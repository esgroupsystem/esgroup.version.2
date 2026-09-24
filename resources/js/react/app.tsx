import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { installModalRouting } from '@/components/modal/modal-store';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { installGlobalToasts, TOAST_DURATION } from '@/lib/notify';
import type { SharedData } from '@/types';

createInertiaApp({
    title: (title) => (title ? `${title} | Jell Group` : 'Jell Group'),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx');
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`React page not found: ./pages/${name}.tsx`);
        }

        return page() as never;
    },
    setup({ el, App, props }) {
        installGlobalToasts(props.initialPage.props as unknown as Partial<SharedData>);
        installModalRouting(props.initialPage.version);

        createRoot(el).render(
            <TooltipProvider delayDuration={0}>
                <App {...props} />
                {/* Lives outside the page so toasts survive navigation. */}
                <Toaster position="top-right" richColors closeButton duration={TOAST_DURATION} />
            </TooltipProvider>,
        );
    },
    progress: {
        color: '#171717',
    },
});
