import type { VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import { createContext, useContext, useMemo, type ReactNode } from 'react';
import { modalVisitOptions, reloadModal, remove } from './modal-store';

export interface ModalApi {
    /** True when this content is rendered inside a modal. */
    inModal: boolean;
    close: () => void;
    refresh: () => void;
    /**
     * GET with query params (filters, search, pages). In a modal it reloads the
     * modal; on a full page it is a normal Inertia visit that keeps state.
     */
    get: (url: string, params?: Record<string, string | number | undefined | null>) => void;
    /**
     * Wrap options of every write (form.post/put/delete, router.post...):
     *   form.post(url, modal.visit({ onSuccess: ... }))
     * In a modal the save stays on the page underneath and the modal closes or
     * refreshes; on a full page the options are returned unchanged.
     */
    visit: <T extends VisitOptions>(options?: T) => T;
}

const clean = (params: Record<string, string | number | undefined | null> = {}) =>
    Object.fromEntries(Object.entries(params).filter(([, value]) => value !== undefined && value !== null && value !== '')) as Record<string, string>;

const pageApi: ModalApi = {
    inModal: false,
    close: () => undefined,
    refresh: () => router.reload(),
    get: (url, params) => router.get(url, clean(params), { preserveState: true, preserveScroll: true, replace: true }),
    visit: (options) => (options ?? {}) as never,
};

const ModalContext = createContext<ModalApi>(pageApi);

export function ModalScope({ id, children }: { id: number; children: ReactNode }) {
    const api = useMemo<ModalApi>(
        () => ({
            inModal: true,
            close: () => remove(id),
            refresh: () => reloadModal(id),
            get: (url, params) => {
                const target = new URL(url, window.location.origin);
                Object.entries(clean(params)).forEach(([key, value]) => target.searchParams.set(key, value));
                reloadModal(id, target.pathname + target.search);
            },
            visit: (options) => modalVisitOptions(id, options ?? {}) as never,
        }),
        [id],
    );

    return <ModalContext.Provider value={api}>{children}</ModalContext.Provider>;
}

/** Modal-aware navigation helpers; safe to use on full pages too. */
export function useModal(): ModalApi {
    return useContext(ModalContext);
}
