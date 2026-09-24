import { useLayoutEffect, useState, type RefObject } from 'react';
import type { ModalSize } from '@/lib/define-page';

/** Starting widths of each modal size (same as the Tailwind classes in modal-stack). */
export const BASE_WIDTH: Record<ModalSize, string> = {
    sm: '28rem',
    md: '42rem',
    lg: '56rem',
    xl: '72rem',
    full: '96vw',
};

/** Phones keep the default full-width dialog; tables scroll inside it there. */
const MIN_VIEWPORT = 640;

/** Ignore tiny differences so the modal never "wobbles" between two widths. */
const THRESHOLD = 8;

/**
 * Widens a modal so its tables show their full natural width without a
 * sideways scrollbar. The modal starts at its size (lg, xl, ...) and grows only
 * as much as the widest table needs, never past 96% of the screen.
 *
 * It only ever grows while open (and only by THRESHOLD px or more): widening
 * changes wrapping and scrollbars, and allowing it to shrink again made the
 * modal oscillate by a few pixels every frame.
 *
 * Returns a CSS max-width for the dialog, or undefined when no change is needed.
 */
export function useFitWidth(dialog: RefObject<HTMLElement | null>, body: RefObject<HTMLElement | null>, size: ModalSize, ready: boolean): string | undefined {
    const [needed, setNeeded] = useState(0);

    useLayoutEffect(() => {
        const box = dialog.current;
        const content = body.current;
        if (!ready || !box || !content || size === 'full') return;

        const measure = () => {
            if (window.innerWidth < MIN_VIEWPORT) return;

            const dialogWidth = box.getBoundingClientRect().width;
            let widest = 0;

            content.querySelectorAll<HTMLElement>('[data-slot="table-container"]').forEach((container) => {
                const table = container.querySelector('table');
                if (!table || container.clientWidth === 0) return;

                // Natural width of the table if nothing had to wrap or scroll.
                const previous = table.style.width;
                table.style.width = 'max-content';
                const natural = table.getBoundingClientRect().width;
                table.style.width = previous;

                // Everything around the table (modal padding, card borders) stays the same.
                widest = Math.max(widest, Math.ceil(dialogWidth - container.clientWidth + natural));
            });

            // Even whole pixels keep the centred dialog off half pixels (blurry, jittery text).
            const next = Math.ceil(widest / 2) * 2;
            setNeeded((current) => (next > current + THRESHOLD ? next : current));
        };

        measure();
        // Re-measure when the content changes (new rows, filters, expanded panels) or the window resizes.
        // Growing is capped by CSS (96vw), so a smaller window needs no reset.
        const observer = new ResizeObserver(() => measure());
        observer.observe(content);
        const inner = content.firstElementChild;
        if (inner) observer.observe(inner);
        window.addEventListener('resize', measure);

        return () => {
            observer.disconnect();
            window.removeEventListener('resize', measure);
        };
    }, [dialog, body, size, ready]);

    if (size === 'full' || needed === 0) return undefined;

    return `min(96vw, max(${BASE_WIDTH[size]}, ${needed}px))`;
}
