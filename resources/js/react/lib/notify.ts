import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import { httpStatusMessage } from '@/lib/http-status';
import type { SharedData } from '@/types';

/** Every message toast stays 5 seconds, top-right (see the Toaster in app.tsx). */
export const TOAST_DURATION = 5000;

type Level = 'success' | 'error' | 'warning' | 'info';

export function notify(level: Level, message: string, description?: string): void {
    toast[level](message, { description, duration: TOAST_DURATION });
}

/** Show a failed HTTP status (400, 403, 404, 419, 500, ...) as a toast. */
export function notifyHttpError(status: number, serverMessage?: string | null): void {
    const { title, message } = httpStatusMessage(status);
    const extra = serverMessage && serverMessage !== title ? serverMessage : message;

    toast.error(`${status} ${title}`, { description: extra, duration: TOAST_DURATION });
}

const LEVEL_FROM_FLASH: Record<string, Level> = { danger: 'error', error: 'error', warning: 'warning', info: 'info', success: 'success' };

/** Flash messages and validation errors that arrived with a page. */
function notifyFromProps(props: Partial<SharedData>): void {
    const flash = props.flash;

    if (flash?.success) notify('success', flash.success);
    if (flash?.error) notify('error', flash.error);
    if (flash?.warning) notify('warning', flash.warning);
    if (flash?.info) notify('info', flash.info);
    flash?.messages?.forEach((item) => notify(LEVEL_FROM_FLASH[item.level] ?? 'info', item.message));

    const errors = Object.values(props.errors ?? {}).filter(Boolean);
    if (errors.length === 1) {
        notify('error', errors[0]);
    } else if (errors.length > 1) {
        notify('error', 'Please check the highlighted fields.', `${errors[0]}${errors.length > 1 ? ` (+${errors.length - 1} more)` : ''}`);
    }
}

let installed = false;

/**
 * One place for every message in the React app: flash messages, validation
 * errors, HTTP error statuses and network failures all become top-right toasts
 * that hide after 5 seconds. Pages no longer render their own message alerts.
 */
export function installGlobalToasts(initialProps: Partial<SharedData>): void {
    if (installed) return;
    installed = true;

    // The first page is rendered from the server, not by a router visit.
    notifyFromProps(initialProps);

    router.on('success', (event) => notifyFromProps(event.detail.page.props as Partial<SharedData>));

    // A visit that came back with validation errors fires 'error' instead of 'success'.
    router.on('error', (event) => {
        const page = event.detail.page;
        notifyFromProps(page ? (page.props as Partial<SharedData>) : { errors: event.detail.errors as Record<string, string> });
    });

    router.on('httpException', (event) => {
        const { status, data } = event.detail.response;
        const serverMessage = typeof data === 'object' && data !== null && typeof data.message === 'string' ? data.message : null;

        notifyHttpError(status, serverMessage);
        // Don't open Inertia's error modal; the toast is the message.
        event.preventDefault();

        if (status === 419) {
            // Expired CSRF token: a fresh page load gets a new one.
            setTimeout(() => window.location.reload(), 1500);
        }
    });

    router.on('networkError', () => {
        notify('error', 'Network error', 'Could not reach the server. Check your connection and try again.');
    });
}
