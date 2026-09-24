import { forwardRef, type AnchorHTMLAttributes, type MouseEvent } from 'react';
import type { ModalSize } from '@/lib/define-page';
import { openModal, type ModalMode } from './modal-store';

type Props = AnchorHTMLAttributes<HTMLAnchorElement> & {
    href: string;
    /** 'view' (default) keeps the modal open after actions; 'form' closes it after a save. */
    mode?: ModalMode;
    /** Override the page's own modal size. */
    size?: ModalSize;
};

/**
 * A link that opens its page in a modal. Ctrl/Cmd/middle-click still opens the
 * real page in a new tab, and the href works without JavaScript.
 * Use it with <Button asChild> for buttons: <Button asChild><ModalLink href=...>New</ModalLink></Button>
 */
export const ModalLink = forwardRef<HTMLAnchorElement, Props>(function ModalLink({ href, mode, size, onClick, ...props }, ref) {
    const open = (event: MouseEvent<HTMLAnchorElement>) => {
        onClick?.(event);
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        openModal(href, { mode, size });
    };

    return <a ref={ref} href={href} onClick={open} {...props} />;
});
