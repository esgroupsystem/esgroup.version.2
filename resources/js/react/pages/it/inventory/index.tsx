import { router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { definePage } from '@/lib/define-page';
import type { Paginated } from '@/types';

interface Item {
    id: number;
    item_name: string;
    description: string | null;
    category: string;
    brand: string | null;
    model: string | null;
    part_number: string | null;
    stock_qty: number;
    minimum_stock: number;
    unit: string;
    location: string | null;
    is_active: boolean;
    edit_url: string;
    destroy_url: string;
}

interface Props {
    items: Paginated<Item>;
    filters: { search: string; category: string };
    categories: string[];
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { index: string; create: string };
}

export default definePage<Props>({
    title: () => 'IT Department Inventory',
    description: () => 'IT parts, accessories, devices and supplies. Stock at or below the minimum is flagged Low.',
    actions: ({ can, urls }) =>
        can.create && (
            <Button asChild>
                <ModalLink href={urls.create} mode="form">
                    <Plus />
                    Add item
                </ModalLink>
            </Button>
        ),
    size: 'xl',
    Content: ItInventoryIndex,
});

function ItInventoryIndex({ items, filters, categories, can, urls }: Props) {
    const modal = useModal();

    const columns: DataTableColumn<Item>[] = [
        {
            key: 'item',
            header: 'Item',
            cell: (item) => (
                <div className="max-w-72">
                    <div className="truncate font-medium" title={item.item_name}>
                        {item.item_name}
                    </div>
                    <div className="truncate text-xs text-muted-foreground" title={item.description ?? undefined}>
                        {[item.brand, item.model, item.part_number && `P/N ${item.part_number}`].filter(Boolean).join(' · ') || item.description || '—'}
                    </div>
                </div>
            ),
        },
        {
            key: 'category',
            header: 'Category',
            cell: (item) => <Badge variant="secondary">{item.category}</Badge>,
            filter: { type: 'select', param: 'category', options: categories.map((name) => ({ value: name, label: name })), placeholder: 'All categories' },
        },
        {
            key: 'stock',
            header: 'Stock',
            align: 'right',
            cell: (item) => {
                const out = item.stock_qty <= 0;
                const low = !out && item.minimum_stock > 0 && item.stock_qty <= item.minimum_stock;

                return (
                    <div className="flex flex-col items-end gap-0.5">
                        {out ? (
                            <Badge variant="destructive">Out of stock</Badge>
                        ) : (
                            <span className={low ? 'font-semibold text-amber-700 tabular-nums dark:text-amber-400' : 'font-semibold tabular-nums'}>
                                {item.stock_qty.toLocaleString()} {item.unit}
                                {low && ' · Low'}
                            </span>
                        )}
                        <span className="text-xs text-muted-foreground tabular-nums">min {item.minimum_stock.toLocaleString()}</span>
                    </div>
                );
            },
        },
        { key: 'location', header: 'Location', hideBelow: 'md', className: 'text-muted-foreground', cell: (item) => item.location || '—' },
        {
            key: 'status',
            header: 'Status',
            hideBelow: 'sm',
            cell: (item) => (
                <Badge variant="outline" className={item.is_active ? 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400' : 'text-muted-foreground'}>
                    {item.is_active ? 'Active' : 'Inactive'}
                </Badge>
            ),
        },
    ];

    return (
        <DataTable
            title="Inventory items"
            noun="item"
            paginator={items}
            url={urls.index}
            filters={filters}
            columns={columns}
            rowKey={(item) => item.id}
            rowClassName={(item) => (item.is_active ? undefined : 'opacity-70')}
            searchPlaceholder="Search item, brand, model, part number, location..."
            emptyText="No inventory item found."
            minWidth={620}
            onRowClick={can.update ? (item) => openModal(item.edit_url, { mode: 'form' }) : undefined}
            rowActions={
                can.update || can.delete
                    ? (item) => (
                          <span className="flex gap-1" onClick={(event) => event.stopPropagation()}>
                              {can.update && (
                                  <Button variant="ghost" size="icon" className="size-8" asChild>
                                      <ModalLink href={item.edit_url} mode="form" aria-label={`Edit ${item.item_name}`}>
                                          <Pencil />
                                      </ModalLink>
                                  </Button>
                              )}
                              {can.delete && (
                                  <ConfirmAction
                                      title="Delete this item?"
                                      description={`${item.item_name} will be removed from IT inventory.`}
                                      confirmLabel="Delete"
                                      destructive
                                      onConfirm={() => router.delete(item.destroy_url, modal.visit({ preserveScroll: true, preserveState: true }))}
                                      trigger={
                                          <IconButton label={`Delete ${item.item_name}`}>
                                              <Trash2 className="text-destructive" />
                                          </IconButton>
                                      }
                                  />
                              )}
                          </span>
                      )
                    : undefined
            }
        />
    );
}
