import type { ComponentType, ReactNode } from 'react';
import { PageHeader } from '@/components/page-header';
import AppLayout from '@/layouts/app-layout';

/** Width of a page when it opens inside a modal. `auto` picks one from the content. */
export type ModalSize = 'sm' | 'md' | 'lg' | 'xl' | 'full';

export interface PageDescriptor<P> {
    /** Browser tab / modal title. */
    title: (props: P) => string;
    description?: (props: P) => ReactNode;
    /** Buttons shown in the page header (full page) or the modal header. */
    actions?: (props: P) => ReactNode;
    /** Modal width; a function lets big records open wider (e.g. many rows -> 'full'). */
    size?: ModalSize | ((props: P) => ModalSize);
    /** The page body, without layout or header. Rendered the same way in both places. */
    Content: ComponentType<P>;
}

export type DefinedPage<P> = ComponentType<P> & { page: PageDescriptor<P> };

/**
 * One page file, two ways to show it: as a normal page (sidebar + header), or
 * inside a modal opened with <ModalLink> / openModal() from another page.
 *
 *   export default definePage<Props>({ title: (p) => `Payroll ${p.payroll.number}`, size: 'xl', Content });
 *
 * The route and controller stay the same, so deep links and refreshes keep working.
 */
export function definePage<P extends object>(descriptor: PageDescriptor<P>): DefinedPage<P> {
    function Page(props: P) {
        const { Content } = descriptor;

        return (
            <AppLayout title={descriptor.title(props)}>
                <PageHeader title={descriptor.title(props)} description={descriptor.description?.(props)} actions={descriptor.actions?.(props)} />
                <Content {...props} />
            </AppLayout>
        );
    }

    Page.displayName = 'DefinedPage';

    return Object.assign(Page, { page: descriptor });
}

export function resolveSize<P>(descriptor: PageDescriptor<P>, props: P): ModalSize {
    const size = typeof descriptor.size === 'function' ? descriptor.size(props) : descriptor.size;
    return size ?? 'lg';
}
