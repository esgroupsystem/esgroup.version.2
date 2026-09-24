import type { Page, VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import { useSyncExternalStore } from 'react';
import type { DefinedPage, ModalSize } from '@/lib/define-page';
import { notifyHttpError } from '@/lib/notify';

/**
 * Global stack of modal pages. A modal shows another Inertia page (its route
 * and controller unchanged) inside a dialog over the current page:
 *
 *   openModal(url)                 -> view a record (actions inside refresh it)
 *   openModal(url, { mode: 'form' }) -> create/edit (closes after a successful save)
 *
 * The page is fetched as Inertia JSON and rendered from its definePage() descriptor.
 * Saves inside the modal send `X-Modal-Base`; the server (HandleModalRedirects)
 * keeps the browser on the page underneath and tells us where the save wanted to go.
 */

export type ModalMode = 'view' | 'form';

export interface ModalEntry {
    id: number;
    url: string;
    mode: ModalMode;
    size?: ModalSize;
    status: 'loading' | 'ready';
    component?: string;
    props?: Record<string, unknown>;
    Page?: DefinedPage<Record<string, unknown>>;
}

interface State {
    /** URL of the page underneath the modals. */
    base: string | null;
    stack: ModalEntry[];
}

const pages = import.meta.glob<{ default: DefinedPage<Record<string, unknown>> }>('../../pages/**/*.tsx');

let state: State = { base: null, stack: [] };
let version: string | null = null;
let nextId = 1;
const listeners = new Set<() => void>();

function set(next: State): void {
    state = next;
    listeners.forEach((listener) => listener());
}

function update(id: number, patch: Partial<ModalEntry>): void {
    set({ ...state, stack: state.stack.map((entry) => (entry.id === id ? { ...entry, ...patch } : entry)) });
}

export function useModalStack(): State {
    return useSyncExternalStore(
        (listener) => {
            listeners.add(listener);
            return () => listeners.delete(listener);
        },
        () => state,
        () => state,
    );
}

/** Kept in sync by installModalRouting(); needed so the server accepts our JSON requests. */
export function setInertiaVersion(value: string | null | undefined): void {
    version = value ?? null;
}

/**
 * Fetch another page's props as Inertia JSON without navigating (used by table
 * export to collect every page of a list). Throws with the HTTP status on failure.
 */
export async function fetchInertiaProps(url: string): Promise<Record<string, unknown>> {
    const response = await fetch(url, {
        headers: {
            Accept: 'text/html, application/xhtml+xml',
            'X-Inertia': 'true',
            'X-Requested-With': 'XMLHttpRequest',
            ...(version ? { 'X-Inertia-Version': version } : {}),
        },
        credentials: 'same-origin',
    });

    if (!response.ok || !response.headers.get('X-Inertia')) throw new Error(String(response.status));

    return ((await response.json()) as Page).props as Record<string, unknown>;
}

const pathOf = (url: string) => new URL(url, window.location.origin).pathname.replace(/\/+$/, '');
export const samePath = (a: string, b: string) => pathOf(a) === pathOf(b);

async function load(id: number, url: string): Promise<void> {
    let response: Response;

    try {
        response = await fetch(url, {
            headers: {
                Accept: 'text/html, application/xhtml+xml',
                'X-Inertia': 'true',
                'X-Requested-With': 'XMLHttpRequest',
                ...(version ? { 'X-Inertia-Version': version } : {}),
            },
            credentials: 'same-origin',
        });
    } catch {
        remove(id);
        notifyHttpError(0, 'Could not reach the server. Check your connection and try again.');
        return;
    }

    // Assets changed or the page is Blade: open it normally instead.
    if (response.status === 409) {
        window.location.href = response.headers.get('X-Inertia-Location') ?? url;
        return;
    }

    if (!response.ok || !response.headers.get('X-Inertia')) {
        remove(id);
        let message: string | null = null;
        try {
            const data = await response.clone().json();
            message = typeof data?.message === 'string' ? data.message : null;
        } catch {
            /* HTML error page */
        }
        notifyHttpError(response.status, message);
        return;
    }

    const page = (await response.json()) as Page;
    const loader = pages[`../../pages/${page.component}.tsx`];
    const module = loader ? await loader() : null;

    // Pages not built with definePage() can't render in a modal: go there instead.
    if (!module?.default?.page) {
        window.location.href = url;
        return;
    }

    update(id, { status: 'ready', url: page.url, component: page.component, props: page.props as Record<string, unknown>, Page: module.default });
}

export function openModal(url: string, options: { mode?: ModalMode; size?: ModalSize } = {}): void {
    const id = nextId++;
    const entry: ModalEntry = { id, url, mode: options.mode ?? 'view', size: options.size, status: 'loading' };

    set({ base: state.base ?? window.location.href, stack: [...state.stack, entry] });
    void load(id, url);
}

export function remove(id: number): void {
    const stack = state.stack.filter((entry) => entry.id !== id);
    set({ base: stack.length ? state.base : null, stack });
}

export function closeAllModals(): void {
    if (state.stack.length) set({ base: null, stack: [] });
}

/** Re-fetch a modal's page (after an action inside it), keeping it open. */
export function reloadModal(id: number, url?: string): void {
    const entry = state.stack.find((item) => item.id === id);
    if (!entry) return;
    const target = url ?? entry.url;
    // Keep showing the old content while the new props load.
    update(id, { url: target });
    void load(id, target);
}

/** What happens after a write made inside modal `id` succeeded (no validation errors). */
function afterSave(id: number, page: Page): void {
    const entry = state.stack.find((item) => item.id === id);
    if (!entry || !state.base) return;

    const target = (page.props as { modal?: { redirect?: string | null } }).modal?.redirect ?? null;
    const elsewhere = target && !samePath(target, state.base) && !samePath(target, entry.url);

    if (entry.mode === 'form') {
        remove(id);
        // e.g. "Generate payroll" redirects to the new payroll: show it.
        if (elsewhere && target) openModal(target, { mode: 'view' });
        return;
    }

    reloadModal(id, elsewhere && target ? target : entry.url);
}

/** Visit options for a write (post/put/delete) made from inside modal `id`. */
export function modalVisitOptions<T extends VisitOptions>(id: number, options: T = {} as T): T {
    return {
        ...options,
        preserveScroll: true,
        preserveState: true,
        headers: { ...(options.headers ?? {}), 'X-Modal-Base': state.base ?? window.location.href },
        onSuccess: (page: Page) => {
            (options.onSuccess as ((page: Page) => void) | undefined)?.(page);
            afterSave(id, page);
        },
    } as T;
}

let installed = false;

/** Called once from app.tsx: tracks the asset version and closes modals when the user leaves the page. */
export function installModalRouting(initialVersion: string | null | undefined): void {
    if (installed) return;
    installed = true;
    setInertiaVersion(initialVersion);

    router.on('navigate', (event) => {
        setInertiaVersion(event.detail.page.version);
        if (state.base && !samePath(event.detail.page.url, state.base)) closeAllModals();
    });
}
