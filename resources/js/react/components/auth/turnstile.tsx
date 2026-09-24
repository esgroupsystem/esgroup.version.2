import { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';

interface TurnstileApi {
    render: (element: HTMLElement, options: Record<string, unknown>) => string;
    reset: (id?: string) => void;
    remove: (id: string) => void;
}

declare global {
    interface Window {
        turnstile?: TurnstileApi;
    }
}

const SCRIPT = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
let loading: Promise<TurnstileApi> | null = null;

/** Loads Cloudflare's script once per page. */
function loadTurnstile(): Promise<TurnstileApi> {
    if (window.turnstile) return Promise.resolve(window.turnstile);
    if (loading) return loading;

    loading = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = SCRIPT;
        script.async = true;
        script.onload = () => (window.turnstile ? resolve(window.turnstile) : reject(new Error('Turnstile unavailable')));
        script.onerror = () => {
            loading = null;
            reject(new Error('Turnstile failed to load'));
        };
        document.head.appendChild(script);
    });

    return loading;
}

export interface TurnstileHandle {
    /** Get a fresh token (a token is single-use: call after every failed sign-in). */
    reset: () => void;
}

interface Props {
    siteKey: string;
    onVerify: (token: string) => void;
    onExpire: () => void;
    onError?: () => void;
}

/** Cloudflare Turnstile check box (explicit rendering, so it works in a React page). */
export const Turnstile = forwardRef<TurnstileHandle, Props>(function Turnstile({ siteKey, onVerify, onExpire, onError }, ref) {
    const box = useRef<HTMLDivElement>(null);
    const widget = useRef<string | null>(null);
    const callbacks = useRef({ onVerify, onExpire, onError });
    callbacks.current = { onVerify, onExpire, onError };

    useImperativeHandle(ref, () => ({
        reset: () => {
            if (widget.current) window.turnstile?.reset(widget.current);
            callbacks.current.onExpire();
        },
    }));

    useEffect(() => {
        let cancelled = false;

        loadTurnstile()
            .then((api) => {
                if (cancelled || !box.current || widget.current) return;
                widget.current = api.render(box.current, {
                    sitekey: siteKey,
                    theme: 'light',
                    callback: (token: string) => callbacks.current.onVerify(token),
                    'expired-callback': () => callbacks.current.onExpire(),
                    'error-callback': () => (callbacks.current.onError ?? callbacks.current.onExpire)(),
                });
            })
            .catch(() => !cancelled && (callbacks.current.onError ?? callbacks.current.onExpire)());

        return () => {
            cancelled = true;
            if (widget.current) window.turnstile?.remove(widget.current);
            widget.current = null;
        };
    }, [siteKey]);

    return <div ref={box} className="flex min-h-[65px] justify-center" />;
});
