import { createElement, Fragment, type ReactNode } from 'react';
import { toast } from 'sonner';
import { fetchInertiaProps } from '@/components/modal/modal-store';
import type { Paginated } from '@/types';

/** What export/print needs from a DataTable column. */
export interface ExportColumn<T> {
    key: string;
    header: ReactNode;
    cell: (row: T) => ReactNode;
    value?: (row: T) => string | number | null | undefined;
    exportValue?: (row: T) => string | number | null | undefined;
    exportable?: boolean;
}

export type ExportFormat = 'xlsx' | 'csv' | 'print';

export interface ExportRequest<T> {
    format: ExportFormat;
    title: string;
    /** Active search / filters, e.g. ["Status: Active", "Search: juan"]. */
    filters: string[];
    columns: ExportColumn<T>[];
    /** Every row to export (all pages, current filters). */
    rows: () => Promise<T[]>;
}

const MAX_PAGES = 200;
const PARALLEL = 4;

/* ------------------------------------------------------------------ rows */

/**
 * Server-mode tables only have one page on screen. Fetch every page of the
 * same list (same filters) as Inertia JSON and collect the rows.
 */
export async function fetchAllPages<T>(paginator: Paginated<T>, pageUrl: (page: number) => string): Promise<T[]> {
    if (paginator.last_page <= 1) return paginator.data;

    const first = await fetchInertiaProps(pageUrl(1));
    // The list is whichever prop looks like this paginator.
    const key = Object.keys(first).find((name) => {
        const candidate = first[name] as Partial<Paginated<T>> | null;
        return !!candidate && Array.isArray(candidate.data) && candidate.total === paginator.total && candidate.last_page === paginator.last_page;
    });
    if (!key) return paginator.data;

    const rows = [...((first[key] as Paginated<T>).data ?? [])];
    const last = Math.min(paginator.last_page, MAX_PAGES);

    for (let start = 2; start <= last; start += PARALLEL) {
        const batch = Array.from({ length: Math.min(PARALLEL, last - start + 1) }, (_, index) => start + index);
        const results = await Promise.all(batch.map((page) => fetchInertiaProps(pageUrl(page))));
        results.forEach((props) => rows.push(...((props[key] as Paginated<T>)?.data ?? [])));
    }

    if (paginator.last_page > MAX_PAGES) {
        toast.warning(`Only the first ${MAX_PAGES} pages were exported. Narrow the filters to export the rest.`);
    }

    return rows;
}

/* ------------------------------------------------------------------ cell text */

type StaticRender = (node: ReactNode) => string;

/** Rendered cell -> plain text. Lines inside a cell ("Name" / "No. 123") are joined with " · ". */
function htmlToText(html: string): string {
    const marked = html.replace(/<\/(div|p|li|dd|dt|h\d)>|<br\s*\/?>/gi, '$& ');
    const box = document.createElement('div');
    box.innerHTML = marked;
    // Icons, controls and avatar initials are not data.
    box.querySelectorAll('svg, button, input, select, textarea, [data-slot^="avatar"], [aria-hidden="true"], .sr-only').forEach((node) => node.remove());
    // Items laid out side by side (flex / grid children: email + phone, badges) are separate values.
    box.querySelectorAll('*').forEach((node) => {
        if (/(^|\s)(flex|grid)(\s|$)/.test(node.parentElement?.className ?? '')) node.after(' ');
    });

    return (box.textContent ?? '')
        .split(' ')
        .map((part) => part.replace(/\s+/g, ' ').trim())
        .filter(Boolean)
        .join(' · ');
}

function cellValue<T>(column: ExportColumn<T>, row: T, render: StaticRender): string | number {
    const explicit = column.exportValue?.(row) ?? column.value?.(row);
    if (explicit !== undefined && explicit !== null) return explicit;

    try {
        return htmlToText(render(createElement(Fragment, null, column.cell(row))));
    } catch {
        return '';
    }
}

const headerText = (column: ExportColumn<unknown>) => (typeof column.header === 'string' ? column.header : column.key.replace(/[_-]+/g, ' ').replace(/^\w/, (c) => c.toUpperCase()));

/* ------------------------------------------------------------------ outputs */

const fileName = (title: string, extension: string) => {
    const stamp = new Date().toISOString().slice(0, 10);
    return `${title.replace(/[^\w\s-]+/g, '').trim().replace(/\s+/g, '-').toLowerCase() || 'export'}-${stamp}.${extension}`;
};

function download(blob: Blob, name: string) {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = name;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
}

const escapeHtml = (value: string) => value.replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char] as string);

function printHtml(title: string, filters: string[], headers: string[], body: (string | number)[][]): string {
    const logo = `${window.location.origin}/assets/img/favicons/esgroup-logo180x180.png`;
    const generated = new Date().toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });

    return `<!doctype html><html><head><meta charset="utf-8"><title>${escapeHtml(title)}</title>
<style>
  @page { size: ${headers.length > 6 ? 'landscape' : 'portrait'}; margin: 12mm; }
  * { box-sizing: border-box; }
  body { font-family: Inter, 'Segoe UI', system-ui, sans-serif; color: #0f172a; margin: 0; font-size: 11px; }
  header { display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 10px; }
  header img { width: 34px; height: 34px; object-fit: contain; }
  header .brand { font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #475569; }
  header h1 { margin: 0; font-size: 16px; }
  header .meta { margin-left: auto; text-align: right; color: #64748b; font-size: 10px; }
  .filters { margin: 0 0 8px; color: #334155; }
  .filters span { display: inline-block; background: #f1f5f9; border-radius: 4px; padding: 1px 6px; margin: 0 4px 3px 0; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
  th { background: #eff6ff; font-weight: 700; }
  tr:nth-child(even) td { background: #f8fafc; }
  thead { display: table-header-group; }
  tr { page-break-inside: avoid; }
  footer { margin-top: 8px; color: #64748b; font-size: 10px; }
</style></head><body>
<header><img src="${logo}" alt=""><div><div class="brand">Jell Group of Company</div><h1>${escapeHtml(title)}</h1></div>
<div class="meta">Printed ${escapeHtml(generated)}<br>${body.length.toLocaleString()} record${body.length === 1 ? '' : 's'}</div></header>
${filters.length ? `<p class="filters">${filters.map((filter) => `<span>${escapeHtml(filter)}</span>`).join('')}</p>` : ''}
<table><thead><tr>${headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}</tr></thead>
<tbody>${body.length ? body.map((row) => `<tr>${row.map((cell) => `<td>${escapeHtml(String(cell))}</td>`).join('')}</tr>`).join('') : `<tr><td colspan="${headers.length}">No records.</td></tr>`}</tbody></table>
<footer>Jell Group of Company · ${escapeHtml(title)}</footer>
</body></html>`;
}

/**
 * Export or print a table: every row (all pages, current filters), every
 * column except actions. Excel (.xlsx), CSV (opens in Excel) or a print view.
 */
export async function runExport<T>({ format, title, filters, columns, rows }: ExportRequest<T>): Promise<void> {
    // The print window must open inside the click, before any await (popup blockers).
    const printWindow = format === 'print' ? window.open('', '_blank') : null;
    if (format === 'print') {
        if (!printWindow) {
            toast.error('Allow pop-ups for this site to print.');
            return;
        }
        printWindow.document.write('<p style="font-family:sans-serif;padding:24px">Preparing print view…</p>');
    }

    const busy = toast.loading(format === 'print' ? 'Preparing print view…' : 'Preparing export…');

    try {
        const [{ renderToStaticMarkup }, data] = await Promise.all([import('react-dom/server'), rows()]);
        const usable = columns.filter((column) => column.exportable !== false);
        const headers = usable.map((column) => headerText(column as ExportColumn<unknown>));
        const body = data.map((row) => usable.map((column) => cellValue(column, row, renderToStaticMarkup)));

        if (format === 'print' && printWindow) {
            printWindow.document.open();
            printWindow.document.write(printHtml(title, filters, headers, body));
            printWindow.document.close();
            printWindow.focus();
            // Give the logo a moment to load before the print dialog opens.
            setTimeout(() => printWindow.print(), 300);
        } else if (format === 'csv') {
            const quote = (value: string | number) => `"${String(value).replace(/"/g, '""')}"`;
            const csv = [headers, ...body].map((line) => line.map(quote).join(',')).join('\r\n');
            // BOM so Excel reads UTF-8 (₱, ñ) correctly.
            download(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }), fileName(title, 'csv'));
        } else {
            const { default: writeXlsxFile } = await import('write-excel-file');
            const sheet = [
                headers.map((value) => ({ value, fontWeight: 'bold' as const, backgroundColor: '#EFF6FF' })),
                ...body.map((line) => line.map((value) => ({ value, type: typeof value === 'number' ? Number : String }))),
            ];
            const widths = headers.map((header, index) => ({
                width: Math.min(60, Math.max(header.length, ...body.slice(0, 200).map((line) => String(line[index]).length)) + 2),
            }));
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            await writeXlsxFile(sheet as any, { fileName: fileName(title, 'xlsx'), columns: widths, stickyRowsCount: 1 });
        }

        toast.success(format === 'print' ? 'Print view ready.' : `Exported ${body.length.toLocaleString()} record${body.length === 1 ? '' : 's'}.`, { id: busy });
    } catch {
        printWindow?.close();
        toast.error('Export failed. Please try again.', { id: busy });
    }
}
