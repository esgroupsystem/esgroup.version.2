import { ChevronDown, ChevronLeft, ChevronRight, Download, FileSpreadsheet, FileText, Inbox, Loader2, Printer, Search, X } from 'lucide-react';
import { useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { useModal } from '@/components/modal/modal-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardAction, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';
import { fetchAllPages, runExport, type ExportFormat } from './data-export';

export interface FilterOption {
    value: string;
    label: string;
}

/** A filter shown inside the table, under the column's header. */
export type ColumnFilter =
    | { type: 'text'; param: string; placeholder?: string }
    | { type: 'select'; param: string; options: FilterOption[]; placeholder?: string }
    | { type: 'date'; param: string }
    /** Two date inputs (from / to) under one column. */
    | { type: 'daterange'; from: string; to: string };

/** Query params a filter writes. */
const paramsOf = (filter: ColumnFilter): string[] => (filter.type === 'daterange' ? [filter.from, filter.to] : [filter.param]);

export interface DataTableColumn<T> {
    key: string;
    header: ReactNode;
    cell: (row: T) => ReactNode;
    /** Plain value for search/filtering when the table filters rows itself (client mode). */
    value?: (row: T) => string | number | null | undefined;
    filter?: ColumnFilter;
    align?: 'left' | 'right' | 'center';
    /** Hide the column on small screens to keep rows readable. */
    hideBelow?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    className?: string;
    headClassName?: string;
    /** Text for Excel/CSV/print when the cell is not plain text (default: the rendered cell's text). */
    exportValue?: (row: T) => string | number | null | undefined;
    /** false = leave this column out of export and print (e.g. a column of inputs). */
    exportable?: boolean;
}

type Values = Record<string, string>;

interface CommonProps<T> {
    columns: DataTableColumn<T>[];
    rowKey: (row: T) => string | number;
    title?: ReactNode;
    description?: ReactNode;
    /** Buttons on the right of the card header (New, Export, ...). */
    toolbar?: ReactNode;
    searchPlaceholder?: string;
    /** Actions column (view/edit/delete buttons). */
    rowActions?: (row: T) => ReactNode;
    rowClassName?: (row: T) => string | undefined;
    /** Whole-row click (e.g. open the record in a modal). Buttons inside rows should stopPropagation. */
    onRowClick?: (row: T) => void;
    emptyText?: ReactNode;
    noun?: string;
    /** Extra content under the table (totals, notes). */
    footer?: ReactNode;
    className?: string;
    /** Minimum table width before it scrolls sideways on small screens. */
    minWidth?: number;
    /** Export (Excel / CSV) and Print buttons; on by default. */
    exportable?: boolean;
    /** Title of the exported file / printout (default: the table title). */
    exportTitle?: string;
}

/** Server mode: the controller filters and paginates. Search and filters are query params. */
interface ServerProps<T> extends CommonProps<T> {
    paginator: Paginated<T>;
    url: string;
    /** Current query filters from the controller (any object of strings, e.g. a page's Filters interface). */
    filters: object;
    searchParam?: string | null;
    pageParam?: string;
    /** Other query params to keep while filtering (e.g. an active tab). */
    keepParams?: Values;
}

/** Client mode: all rows are on the page; search, filters and paging happen in the browser. */
interface ClientProps<T> extends CommonProps<T> {
    rows: T[];
    pageSize?: number;
    searchable?: boolean;
}

export type DataTableProps<T> = ServerProps<T> | ClientProps<T>;

const HIDE: Record<NonNullable<DataTableColumn<unknown>['hideBelow']>, string> = {
    sm: 'hidden sm:table-cell',
    md: 'hidden md:table-cell',
    lg: 'hidden lg:table-cell',
    xl: 'hidden xl:table-cell',
    '2xl': 'hidden 2xl:table-cell',
};
const ALIGN = { left: '', right: 'text-right', center: 'text-center' };
const ALL = '__all';
const DEBOUNCE = 350;

const isServer = <T,>(props: DataTableProps<T>): props is ServerProps<T> => 'paginator' in props;

/**
 * Reusable data table: search-as-you-type, filters inside the header, active
 * filter chips, pagination and an empty state. Works on full pages and inside
 * modals (navigation goes through useModal()).
 */
export function DataTable<T>(props: DataTableProps<T>) {
    return isServer(props) ? <ServerTable {...props} /> : <ClientTable {...props} />;
}

/* ---------------------------------------------------------------- server */

function ServerTable<T>(props: ServerProps<T>) {
    const { paginator, url, filters, searchParam = 'search', pageParam = 'page', keepParams } = props;
    const modal = useModal();
    const [values, setValues] = useState<Values>(filters as Values);
    const [pending, setPending] = useState(false);
    const timer = useRef<number | undefined>(undefined);
    const filtersKey = JSON.stringify(filters);

    // New results arrived: adopt the server's filters, stop the spinner.
    useEffect(() => {
        setValues(JSON.parse(filtersKey) as Values);
        setPending(false);
    }, [filtersKey, paginator]);

    useEffect(() => () => window.clearTimeout(timer.current), []);

    const go = (next: Values, page?: number) => {
        setPending(true);
        modal.get(url, { ...keepParams, ...next, [pageParam]: page && page > 1 ? String(page) : undefined });
    };

    const change = (param: string, value: string, debounce = false) => {
        const next = { ...values, [param]: value };
        setValues(next);
        window.clearTimeout(timer.current);
        if (debounce) timer.current = window.setTimeout(() => go(next), DEBOUNCE);
        else go(next);
    };

    const clearAll = () => {
        const next = Object.fromEntries(Object.keys(values).map((key) => [key, ''])) as Values;
        setValues(next);
        go(next);
    };

    // Export/print: every page of this list, with the filters currently applied.
    const allRows = () =>
        fetchAllPages(paginator, (page) => {
            const target = new URL(url, window.location.origin);
            Object.entries({ ...keepParams, ...(filters as Values) }).forEach(([key, value]) => value && target.searchParams.set(key, String(value)));
            target.searchParams.set(pageParam, String(page));
            return target.toString();
        });

    return (
        <TableShell
            {...props}
            allRows={allRows}
            rows={paginator.data}
            values={values}
            searchParam={searchParam}
            onChange={change}
            onClearAll={clearAll}
            pending={pending}
            total={paginator.total}
            pager={
                paginator.last_page > 1 || paginator.total > 0 ? (
                    <Pager
                        from={paginator.from}
                        to={paginator.to}
                        total={paginator.total}
                        page={paginator.current_page}
                        lastPage={paginator.last_page}
                        noun={props.noun}
                        onPage={(page) => go(values, page)}
                    />
                ) : null
            }
        />
    );
}

/* ---------------------------------------------------------------- client */

function ClientTable<T>(props: ClientProps<T>) {
    const { rows, columns, pageSize = 15, searchable = true } = props;
    const [values, setValues] = useState<Values>({});
    const [page, setPage] = useState(1);

    const filtered = useMemo(() => {
        const search = (values.search ?? '').trim().toLowerCase();
        const text = (row: T, column: DataTableColumn<T>) => {
            const raw = column.value ? column.value(row) : column.cell(row);
            return typeof raw === 'string' || typeof raw === 'number' ? String(raw).toLowerCase() : '';
        };

        return rows.filter((row) => {
            if (search && !columns.some((column) => text(row, column).includes(search))) return false;

            return columns.every((column) => {
                const filter = column.filter;
                if (!filter) return true;
                const actual = text(row, column);
                if (filter.type === 'daterange') {
                    const from = values[filter.from] ?? '';
                    const to = values[filter.to] ?? '';
                    return (!from || actual >= from) && (!to || actual <= to);
                }
                const wanted = (values[filter.param] ?? '').trim().toLowerCase();
                if (!wanted) return true;
                return filter.type === 'text' ? actual.includes(wanted) : actual === wanted;
            });
        });
    }, [rows, columns, values]);

    const lastPage = Math.max(1, Math.ceil(filtered.length / pageSize));
    const current = Math.min(page, lastPage);
    const slice = filtered.slice((current - 1) * pageSize, current * pageSize);

    const change = (param: string, value: string) => {
        setValues((previous) => ({ ...previous, [param]: value }));
        setPage(1);
    };

    return (
        <TableShell
            {...props}
            allRows={async () => filtered}
            rows={slice}
            values={values}
            searchParam={searchable ? 'search' : null}
            onChange={change}
            onClearAll={() => {
                setValues({});
                setPage(1);
            }}
            pending={false}
            total={filtered.length}
            pager={
                filtered.length > 0 ? (
                    <Pager
                        from={(current - 1) * pageSize + 1}
                        to={Math.min(current * pageSize, filtered.length)}
                        total={filtered.length}
                        page={current}
                        lastPage={lastPage}
                        noun={props.noun}
                        onPage={setPage}
                    />
                ) : null
            }
        />
    );
}

/* ---------------------------------------------------------------- shared UI */

interface ShellProps<T> extends CommonProps<T> {
    rows: T[];
    values: Values;
    searchParam: string | null;
    onChange: (param: string, value: string, debounce?: boolean) => void;
    onClearAll: () => void;
    pending: boolean;
    total: number;
    pager: ReactNode;
    /** Every row matching the current search and filters (all pages). */
    allRows: () => Promise<T[]>;
}

function TableShell<T>({
    columns,
    rowKey,
    rows,
    title,
    description,
    toolbar,
    searchPlaceholder = 'Search...',
    rowActions,
    rowClassName,
    onRowClick,
    emptyText = 'No records found.',
    footer,
    className,
    minWidth = 720,
    values,
    searchParam,
    onChange,
    onClearAll,
    pending,
    total,
    noun = 'record',
    pager,
    exportable = true,
    exportTitle,
    allRows,
}: ShellProps<T>) {
    const hasFilterRow = columns.some((column) => column.filter);
    const colSpan = columns.length + (rowActions ? 1 : 0);

    // Chips for every active search/filter, with a readable label.
    const chips = [
        ...(searchParam && values[searchParam] ? [{ param: searchParam, label: 'Search', value: values[searchParam] }] : []),
        ...columns
            .filter((column) => column.filter)
            .flatMap((column) => {
                const filter = column.filter as ColumnFilter;
                const name = typeof column.header === 'string' ? column.header : paramsOf(filter)[0];

                if (filter.type === 'daterange') {
                    return [
                        ...(values[filter.from] ? [{ param: filter.from, label: `${name} from`, value: values[filter.from] }] : []),
                        ...(values[filter.to] ? [{ param: filter.to, label: `${name} to`, value: values[filter.to] }] : []),
                    ];
                }

                const raw = values[filter.param];
                if (!raw) return [];
                const value = filter.type === 'select' ? (filter.options.find((option) => option.value === raw)?.label ?? raw) : raw;
                return [{ param: filter.param, label: name, value }];
            }),
    ];

    return (
        <Card className={cn('gap-0 overflow-hidden py-0', className)}>
            <CardHeader className="gap-3 border-b py-4">
                {(title || description) && (
                    <div className="grid gap-1">
                        {title && <CardTitle>{title}</CardTitle>}
                        <CardDescription>
                            {description ?? `${total.toLocaleString()} ${noun}${total === 1 ? '' : 's'}`}
                        </CardDescription>
                    </div>
                )}
                {toolbar && <CardAction className="flex flex-wrap items-center gap-2">{toolbar}</CardAction>}

                {(searchParam || exportable) && (
                    <div className="col-span-full flex flex-wrap items-center gap-2">
                        {searchParam && (
                        <div className="relative w-full sm:w-80">
                            <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                aria-label="Search table"
                                placeholder={searchPlaceholder}
                                className="pr-8 pl-8"
                                value={values[searchParam] ?? ''}
                                onChange={(event) => onChange(searchParam, event.target.value, true)}
                            />
                            {pending ? (
                                <Loader2 className="absolute top-1/2 right-2.5 size-4 -translate-y-1/2 animate-spin text-muted-foreground" />
                            ) : (
                                values[searchParam] && (
                                    <button
                                        type="button"
                                        aria-label="Clear search"
                                        className="absolute top-1/2 right-2 -translate-y-1/2 rounded-sm p-0.5 text-muted-foreground hover:text-foreground"
                                        onClick={() => onChange(searchParam, '')}
                                    >
                                        <X className="size-3.5" />
                                    </button>
                                )
                            )}
                        </div>
                        )}
                        {chips.length > 0 && (
                            <div className="flex flex-wrap items-center gap-1.5">
                                {chips.map((chip) => (
                                    <Badge key={chip.param} variant="secondary" className="gap-1 pr-1 font-normal">
                                        <span className="text-muted-foreground">{chip.label}:</span>
                                        <span className="max-w-40 truncate">{chip.value}</span>
                                        <button type="button" aria-label={`Remove ${chip.label} filter`} className="rounded-sm p-0.5 hover:bg-background" onClick={() => onChange(chip.param, '')}>
                                            <X className="size-3" />
                                        </button>
                                    </Badge>
                                ))}
                                <Button variant="ghost" size="sm" className="h-6 px-2 text-xs" onClick={onClearAll}>
                                    Clear all
                                </Button>
                            </div>
                        )}
                        {exportable && (
                            <ExportButtons
                                onExport={(format) =>
                                    runExport({
                                        format,
                                        title: exportTitle ?? (typeof title === 'string' ? title : document.title.replace(/\s*\|.*$/, '')),
                                        filters: chips.map((chip) => `${chip.label}: ${chip.value}`),
                                        columns,
                                        rows: allRows,
                                    })
                                }
                            />
                        )}
                    </div>
                )}
            </CardHeader>

            <Table style={{ minWidth }} className={cn(pending && 'opacity-60 transition-opacity')}>
                <TableHeader>
                    <TableRow className="bg-muted/40 hover:bg-muted/40">
                        {columns.map((column, index) => (
                            <TableHead
                                key={column.key}
                                className={cn(index === 0 && 'pl-6', column.align && ALIGN[column.align], column.hideBelow && HIDE[column.hideBelow], column.headClassName)}
                            >
                                {column.header}
                            </TableHead>
                        ))}
                        {rowActions && <TableHead className="w-px pr-6 text-right">Actions</TableHead>}
                    </TableRow>
                    {hasFilterRow && (
                        <TableRow className="hover:bg-transparent">
                            {columns.map((column, index) => (
                                <TableHead key={column.key} className={cn('h-auto py-2 font-normal', index === 0 && 'pl-6', column.hideBelow && HIDE[column.hideBelow])}>
                                    {column.filter && <FilterControl column={column} filter={column.filter} values={values} onChange={onChange} />}
                                </TableHead>
                            ))}
                            {rowActions && <TableHead className="h-auto py-2 pr-6" />}
                        </TableRow>
                    )}
                </TableHeader>
                <TableBody>
                    {rows.length === 0 && (
                        <TableRow className="hover:bg-transparent">
                            <TableCell colSpan={colSpan} className="py-14">
                                <div className="flex flex-col items-center gap-2 text-center text-sm text-muted-foreground">
                                    <Inbox className="size-8 opacity-60" />
                                    {emptyText}
                                    {chips.length > 0 && (
                                        <Button variant="link" size="sm" className="h-auto p-0" onClick={onClearAll}>
                                            Clear search and filters
                                        </Button>
                                    )}
                                </div>
                            </TableCell>
                        </TableRow>
                    )}
                    {rows.map((row) => (
                        <TableRow
                            key={rowKey(row)}
                            className={cn(onRowClick && 'cursor-pointer', rowClassName?.(row))}
                            onClick={onRowClick ? () => onRowClick(row) : undefined}
                            tabIndex={onRowClick ? 0 : undefined}
                            onKeyDown={onRowClick ? (event) => (event.key === 'Enter' || event.key === ' ') && event.target === event.currentTarget && (event.preventDefault(), onRowClick(row)) : undefined}
                        >
                            {columns.map((column, index) => (
                                <TableCell
                                    key={column.key}
                                    className={cn(index === 0 && 'pl-6', column.align && ALIGN[column.align], column.hideBelow && HIDE[column.hideBelow], column.className)}
                                >
                                    {column.cell(row)}
                                </TableCell>
                            ))}
                            {rowActions && (
                                <TableCell className="pr-6">
                                    <div className="flex items-center justify-end gap-1">{rowActions(row)}</div>
                                </TableCell>
                            )}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            {footer}
            {pager && <div className="border-t px-6 py-3">{pager}</div>}
        </Card>
    );
}

function FilterControl<T>({ column, filter, values, onChange }: { column: DataTableColumn<T>; filter: ColumnFilter; values: Values; onChange: ShellProps<T>['onChange'] }) {
    const label = `Filter ${typeof column.header === 'string' ? column.header : paramsOf(filter)[0]}`;

    if (filter.type === 'daterange') {
        return (
            <div className="grid min-w-32 gap-1">
                <Input type="date" aria-label={`${label} from`} title="From" className="h-8 text-xs" value={values[filter.from] ?? ''} onChange={(event) => onChange(filter.from, event.target.value)} />
                <Input type="date" aria-label={`${label} to`} title="To" className="h-8 text-xs" value={values[filter.to] ?? ''} onChange={(event) => onChange(filter.to, event.target.value)} />
            </div>
        );
    }

    const value = values[filter.param] ?? '';

    if (filter.type === 'select') {
        return (
            <Select value={value || ALL} onValueChange={(next) => onChange(filter.param, next === ALL ? '' : next)}>
                <SelectTrigger size="sm" className="h-8 w-full min-w-28 text-xs" aria-label={label}>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value={ALL}>{filter.placeholder ?? 'All'}</SelectItem>
                    {filter.options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        );
    }

    return (
        <Input
            type={filter.type === 'date' ? 'date' : 'text'}
            aria-label={label}
            placeholder={filter.type === 'text' ? (filter.placeholder ?? 'Filter...') : undefined}
            className="h-8 min-w-28 text-xs"
            value={value}
            onChange={(event) => onChange(filter.param, event.target.value, filter.type === 'text')}
        />
    );
}

function Pager({ from, to, total, page, lastPage, noun = 'record', onPage }: { from: number | null; to: number | null; total: number; page: number; lastPage: number; noun?: string; onPage: (page: number) => void }) {
    // Compact page list: 1 … 4 5 [6] 7 8 … 20
    const pages = Array.from(new Set([1, page - 2, page - 1, page, page + 1, page + 2, lastPage].filter((value) => value >= 1 && value <= lastPage))).sort((a, b) => a - b);

    return (
        <div className="flex flex-col items-center justify-between gap-2 text-sm sm:flex-row">
            <p className="text-muted-foreground">
                {total > 0 ? `Showing ${from?.toLocaleString()}–${to?.toLocaleString()} of ${total.toLocaleString()} ${noun}${total === 1 ? '' : 's'}` : `No ${noun}s`}
            </p>
            {lastPage > 1 && (
                <div className="flex items-center gap-1">
                    <Button variant="outline" size="icon" className="size-8" aria-label="Previous page" disabled={page <= 1} onClick={() => onPage(page - 1)}>
                        <ChevronLeft />
                    </Button>
                    {pages.map((value, index) => (
                        <span key={value} className="flex items-center gap-1">
                            {index > 0 && value - pages[index - 1] > 1 && <span className="px-1 text-muted-foreground">…</span>}
                            <Button variant={value === page ? 'default' : 'ghost'} size="icon" className="size-8 tabular-nums" aria-current={value === page ? 'page' : undefined} onClick={() => onPage(value)}>
                                {value}
                            </Button>
                        </span>
                    ))}
                    <Button variant="outline" size="icon" className="size-8" aria-label="Next page" disabled={page >= lastPage} onClick={() => onPage(page + 1)}>
                        <ChevronRight />
                    </Button>
                </div>
            )}
        </div>
    );
}

/** Excel / CSV download and Print, on the right of the search row. */
function ExportButtons({ onExport }: { onExport: (format: ExportFormat) => Promise<void> }) {
    const [busy, setBusy] = useState(false);
    const run = (format: ExportFormat) => {
        setBusy(true);
        onExport(format).finally(() => setBusy(false));
    };

    return (
        <div className="ml-auto flex items-center gap-1.5">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" disabled={busy}>
                        {busy ? <Loader2 className="animate-spin" /> : <Download />}
                        Export
                        <ChevronDown className="opacity-60" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem onSelect={() => run('xlsx')}>
                        <FileSpreadsheet className="text-emerald-600" />
                        Excel (.xlsx)
                    </DropdownMenuItem>
                    <DropdownMenuItem onSelect={() => run('csv')}>
                        <FileText />
                        CSV
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
            {/* Print opens its window inside the click (popup blockers), so no state change first. */}
            <Button variant="outline" size="sm" disabled={busy} onClick={() => onExport('print')}>
                <Printer />
                Print
            </Button>
        </div>
    );
}
