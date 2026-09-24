import { router } from '@inertiajs/react';
import { useEffect } from 'react';

/** Minutes without mouse, keyboard, touch or scroll before the screen locks itself. */
export const IDLE_LOCK_MINUTES = 15;

const STORAGE_KEY = 'jg:last-activity';
const EVENTS = ['pointerdown', 'pointermove', 'keydown', 'wheel', 'touchstart', 'scroll'] as const;

const read = (): number => {
    try {
        return Number(window.localStorage.getItem(STORAGE_KEY)) || 0;
    } catch {
        return 0;
    }
};

const write = (time: number) => {
    try {
        window.localStorage.setItem(STORAGE_KEY, String(time));
    } catch {
        // Private mode / blocked storage: this tab still tracks its own activity.
    }
};

/**
 * Locks the session (POST lockUrl -> React lock screen) after IDLE_LOCK_MINUTES
 * of no activity. Activity in any open tab counts, so a busy tab never gets
 * locked by an idle one. The server enforces the lock; this only triggers it.
 */
export function useIdleLock(lockUrl: string | undefined, minutes = IDLE_LOCK_MINUTES) {
    useEffect(() => {
        if (!lockUrl) return;

        const limit = minutes * 60_000;
        let local = Date.now();
        let lastWrite = 0;
        let locking = false;
        write(local);

        const touch = () => {
            local = Date.now();
            // Throttle storage writes; pointermove fires constantly.
            if (local - lastWrite > 5_000) {
                lastWrite = local;
                write(local);
            }
        };

        const check = () => {
            if (locking) return;
            const last = Math.max(local, read());
            if (Date.now() - last >= limit) {
                locking = true;
                router.post(lockUrl, {}, { onFinish: () => (locking = false) });
            }
        };

        EVENTS.forEach((name) => window.addEventListener(name, touch, { passive: true }));
        const timer = window.setInterval(check, 30_000);
        // Coming back to a tab that slept (laptop lid, background throttling) checks right away.
        const onVisible = () => document.visibilityState === 'visible' && check();
        document.addEventListener('visibilitychange', onVisible);

        return () => {
            EVENTS.forEach((name) => window.removeEventListener(name, touch));
            window.clearInterval(timer);
            document.removeEventListener('visibilitychange', onVisible);
        };
    }, [lockUrl, minutes]);
}
