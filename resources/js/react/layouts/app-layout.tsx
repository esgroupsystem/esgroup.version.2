import { Head } from '@inertiajs/react';
import type { CSSProperties, ReactNode } from 'react';
import { AppSidebar } from '@/components/app-sidebar';
import { ModalStack } from '@/components/modal/modal-stack';
import { SiteHeader } from '@/components/site-header';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';

function sidebarDefaultOpen(): boolean {
    // Written by the shadcn sidebar on toggle; read client-side because
    // Laravel encrypts cookies it did not set itself.
    return !document.cookie.split('; ').includes('sidebar_state=false');
}

export default function AppLayout({ title, children }: { title: string; children: ReactNode }) {

    return (
        <SidebarProvider
            defaultOpen={sidebarDefaultOpen()}
            style={
                {
                    '--sidebar-width': '16rem',
                    '--header-height': '3.5rem',
                } as CSSProperties
            }
        >
            <Head title={title} />
            <AppSidebar variant="inset" />
            <SidebarInset className="min-w-0">
                <SiteHeader title={title} />
                <div className="flex flex-1 flex-col gap-4 p-4 md:gap-6 md:p-6">
                    {/* Flash messages and errors are toasts (lib/notify.ts), not inline alerts. */}
                    {children}
                </div>
            </SidebarInset>
            {/* Pages opened with <ModalLink> / openModal() (the stack itself is global). */}
            <ModalStack />
        </SidebarProvider>
    );
}
