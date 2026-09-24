import { Check, ChevronsUpDown, Loader2, Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

/** Shape returned by the parts-out / receiving / stock-transfer search-products endpoints. */
export interface ProductHit {
    id: number;
    name: string;
    supplier_name: string | null;
    category: string | null;
    unit: string | null;
    part_number: string | null;
    details: string | null;
    stock: number;
}

export interface LineItem {
    key: number;
    product: ProductHit | null;
    qty: string;
    remarks: string;
}

let nextKey = 1;
export const newLine = (): LineItem => ({ key: nextKey++, product: null, qty: '1', remarks: '' });

/**
 * Editable product rows with remote search. `searchUrl(query, excludeIds)`
 * returns the endpoint to call, or null when searching isn't possible yet
 * (e.g. no source location chosen). `stockEffect` shows the resulting stock:
 * -1 for issuing/transferring out, +1 for receiving.
 */
export function ProductLineItems({
    lines,
    onChange,
    searchUrl,
    stockLabel = 'Stock',
    qtyLabel = 'Qty',
    stockEffect,
    withRemarks = false,
    disabledReason,
    errors = {},
    qtyKey,
}: {
    lines: LineItem[];
    onChange: (lines: LineItem[]) => void;
    searchUrl: (query: string, excludeIds: number[]) => string | null;
    stockLabel?: string;
    qtyLabel?: string;
    stockEffect: -1 | 1;
    withRemarks?: boolean;
    disabledReason?: string;
    errors?: Record<string, string>;
    qtyKey: string;
}) {
    const update = (key: number, patch: Partial<LineItem>) => onChange(lines.map((line) => (line.key === key ? { ...line, ...patch } : line)));
    const chosenIds = lines.map((line) => line.product?.id).filter((id): id is number => typeof id === 'number');

    return (
        <div className="grid gap-3">
            <div className="overflow-x-auto rounded-lg border">
                <Table className="min-w-[900px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-10 text-center">#</TableHead>
                            <TableHead className="min-w-80">Product</TableHead>
                            <TableHead className="w-24 text-right">{stockLabel}</TableHead>
                            <TableHead className="w-20">Unit</TableHead>
                            <TableHead className="w-32">Part no.</TableHead>
                            <TableHead className="w-28">{qtyLabel}</TableHead>
                            <TableHead className="w-28 text-right">Stock after</TableHead>
                            {withRemarks && <TableHead className="min-w-44">Remarks</TableHead>}
                            <TableHead className="w-14" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {lines.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={withRemarks ? 9 : 8} className="py-8 text-center text-sm text-muted-foreground">
                                    {disabledReason ?? 'No items yet. Click “Add item” to start.'}
                                </TableCell>
                            </TableRow>
                        )}
                        {lines.map((line, index) => {
                            const qty = Number(line.qty || 0);
                            const after = line.product ? line.product.stock + stockEffect * qty : null;
                            const invalid = after !== null && after < 0;
                            const productError = errors[`product_id.${index}`];
                            const qtyError = errors[`${qtyKey}.${index}`];

                            return (
                                <TableRow key={line.key} className="align-top">
                                    <TableCell className="pt-4 text-center text-muted-foreground">{index + 1}</TableCell>
                                    <TableCell>
                                        <ProductSearch
                                            value={line.product}
                                            onChange={(product) => update(line.key, { product })}
                                            searchUrl={(query) => searchUrl(query, chosenIds.filter((id) => id !== line.product?.id))}
                                            disabledReason={disabledReason}
                                            label={`Product ${index + 1}`}
                                            invalid={!!productError}
                                        />
                                        {productError && <p className="mt-1 text-xs text-destructive">{productError}</p>}
                                    </TableCell>
                                    <TableCell className="pt-4 text-right tabular-nums">{line.product ? line.product.stock.toLocaleString() : '—'}</TableCell>
                                    <TableCell className="pt-4">{line.product?.unit || '—'}</TableCell>
                                    <TableCell className="pt-4 text-muted-foreground">{line.product?.part_number || '—'}</TableCell>
                                    <TableCell>
                                        <Input
                                            type="number"
                                            min={1}
                                            aria-label={`${qtyLabel} ${index + 1}`}
                                            value={line.qty}
                                            aria-invalid={invalid || !!qtyError}
                                            onChange={(event) => update(line.key, { qty: event.target.value })}
                                        />
                                        {qtyError && <p className="mt-1 text-xs text-destructive">{qtyError}</p>}
                                    </TableCell>
                                    <TableCell className={cn('pt-4 text-right font-semibold tabular-nums', invalid && 'text-destructive')}>{after === null ? '—' : after.toLocaleString()}</TableCell>
                                    {withRemarks && (
                                        <TableCell>
                                            <Input aria-label={`Remarks ${index + 1}`} placeholder="Optional remarks" value={line.remarks} onChange={(event) => update(line.key, { remarks: event.target.value })} />
                                        </TableCell>
                                    )}
                                    <TableCell>
                                        <Button type="button" variant="ghost" size="icon" aria-label={`Remove item ${index + 1}`} onClick={() => onChange(lines.filter((current) => current.key !== line.key))}>
                                            <Trash2 className="text-destructive" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            );
                        })}
                    </TableBody>
                </Table>
            </div>
            <div>
                <Button type="button" variant="outline" size="sm" onClick={() => onChange([...lines, newLine()])}>
                    <Plus />
                    Add item
                </Button>
            </div>
        </div>
    );
}

/** True when every row has a product and a positive qty that does not overdraw stock. */
export function linesAreValid(lines: LineItem[], stockEffect: -1 | 1): boolean {
    return (
        lines.length > 0 &&
        lines.every((line) => {
            const qty = Number(line.qty);

            return line.product !== null && Number.isInteger(qty) && qty >= 1 && line.product.stock + stockEffect * qty >= 0;
        })
    );
}

function ProductSearch({
    value,
    onChange,
    searchUrl,
    disabledReason,
    label,
    invalid,
}: {
    value: ProductHit | null;
    onChange: (product: ProductHit) => void;
    searchUrl: (query: string) => string | null;
    disabledReason?: string;
    label: string;
    invalid: boolean;
}) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<ProductHit[]>([]);
    const [loading, setLoading] = useState(false);
    const url = query.trim().length >= 2 ? searchUrl(query.trim()) : null;

    useEffect(() => {
        if (!open || !url) {
            setResults([]);
            return;
        }
        const controller = new AbortController();
        const timer = window.setTimeout(() => {
            setLoading(true);
            fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
                .then((response) => (response.ok ? response.json() : []))
                .then((data) => setResults(Array.isArray(data) ? data : []))
                .catch(() => undefined)
                .finally(() => setLoading(false));
        }, 300);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [open, url]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-label={label}
                    aria-expanded={open}
                    aria-invalid={invalid}
                    disabled={!!disabledReason}
                    className="w-full justify-between font-normal"
                >
                    <span className={cn('truncate', !value && 'text-muted-foreground')}>{value ? value.name : (disabledReason ?? 'Search product...')}</span>
                    <ChevronsUpDown className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-[26rem] p-0" align="start">
                <Command shouldFilter={false}>
                    <CommandInput placeholder="Type at least 2 characters..." value={query} onValueChange={setQuery} />
                    <CommandList>
                        {loading && (
                            <div className="flex items-center gap-2 px-3 py-6 text-sm text-muted-foreground">
                                <Loader2 className="size-4 animate-spin" />
                                Searching...
                            </div>
                        )}
                        {!loading && <CommandEmpty>{query.trim().length < 2 ? 'Type at least 2 characters.' : 'No available product found.'}</CommandEmpty>}
                        {!loading && (
                            <CommandGroup>
                                {results.map((product) => (
                                    <CommandItem
                                        key={product.id}
                                        value={String(product.id)}
                                        onSelect={() => {
                                            onChange(product);
                                            setOpen(false);
                                        }}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate font-medium">{product.name}</div>
                                            <div className="truncate text-xs text-muted-foreground">
                                                {[product.category, product.part_number && `#${product.part_number}`, product.supplier_name].filter(Boolean).join(' · ') || 'No details'}
                                            </div>
                                        </div>
                                        <span className="text-xs text-muted-foreground tabular-nums">Stock {product.stock}</span>
                                        <Check className={cn('ml-1', value?.id === product.id ? 'opacity-100' : 'opacity-0')} />
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        )}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
