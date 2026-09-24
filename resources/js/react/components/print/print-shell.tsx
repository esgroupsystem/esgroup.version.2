import { Head } from '@inertiajs/react';
import { Printer, X } from 'lucide-react';
import { useEffect, type ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Standalone printable page (opened in a new tab from a Print button).
 * White paper look on screen, a Print / Close bar that never prints, and the
 * browser print dialog opens by itself once the page has rendered.
 */
export function PrintShell({
    title,
    subtitle,
    meta,
    landscape,
    pageSize,
    bare,
    autoPrint = true,
    className,
    children,
}: {
    title: string;
    subtitle?: ReactNode;
    /** Right side of the header (generated date, totals, ...). */
    meta?: ReactNode;
    landscape?: boolean;
    /** CSS page size when A4 is not right (e.g. '8.5in 11in'). */
    pageSize?: string;
    /** No shared header: the content draws its own per printed page. */
    bare?: boolean;
    autoPrint?: boolean;
    className?: string;
    children: ReactNode;
}) {
    useEffect(() => {
        if (!autoPrint) return;
        // Let fonts/images settle first.
        const timer = window.setTimeout(() => window.print(), 400);
        return () => window.clearTimeout(timer);
    }, [autoPrint]);

    return (
        <div className="min-h-svh bg-slate-100 text-[12px] text-slate-900 print:bg-white">
            <Head title={title} />
            {/* Page size for the printer. */}
            <style>{`@page { size: ${pageSize ?? (landscape ? 'A4 landscape' : 'A4 portrait')}; margin: 10mm; } @media print { html, body { background: #fff !important; } }`}</style>

            <div className="sticky top-0 z-10 flex items-center justify-between gap-2 border-b bg-white/90 px-4 py-2 backdrop-blur print:hidden">
                <span className="truncate text-sm font-medium">{title}</span>
                <div className="flex gap-2">
                    <Button size="sm" onClick={() => window.print()}>
                        <Printer />
                        Print
                    </Button>
                    <Button size="sm" variant="outline" onClick={() => window.close()}>
                        <X />
                        Close
                    </Button>
                </div>
            </div>

            <main className={cn('mx-auto my-6 max-w-[1200px] bg-white p-8 shadow-sm print:m-0 print:max-w-none print:p-0 print:shadow-none', className)}>
                {!bare && (
                <header className="mb-4 flex items-start gap-3 border-b-2 border-blue-700 pb-3">
                    <img src="/assets/img/favicons/esgroup-logo180x180.png" alt="" className="size-10 object-contain" />
                    <div className="min-w-0">
                        <div className="text-[10px] font-bold tracking-wider text-slate-500 uppercase">Jell Group of Company</div>
                        <h1 className="text-lg leading-tight font-bold">{title}</h1>
                        {subtitle && <div className="text-xs text-slate-600">{subtitle}</div>}
                    </div>
                    {meta && <div className="ml-auto shrink-0 text-right text-[11px] text-slate-500">{meta}</div>}
                </header>
                )}
                {children}
            </main>
        </div>
    );
}

/** Bordered print table cells (use inside PrintShell). */
export const printTable = 'w-full border-collapse [&_td]:border [&_td]:border-slate-300 [&_td]:px-1.5 [&_td]:py-1 [&_td]:align-top [&_th]:border [&_th]:border-slate-300 [&_th]:bg-slate-100 [&_th]:px-1.5 [&_th]:py-1 [&_th]:text-left [&_th]:font-bold [&_thead]:table-header-group [&_tr]:break-inside-avoid';
