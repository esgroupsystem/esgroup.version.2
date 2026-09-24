import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

/** Pager for a Laravel LengthAwarePaginator passed to the page as-is. */
export function DataPagination<T>({ paginator, noun = 'record' }: { paginator: Paginated<T>; noun?: string }) {
    const { from, to, total, links, prev_page_url, next_page_url } = paginator;
    // Laravel sends [« Previous, 1, 2, …, Next »]; the arrows are rendered separately.
    const pages = links.slice(1, -1);

    return (
        <div className="flex flex-col items-center justify-between gap-3 text-sm sm:flex-row">
            <p className="text-muted-foreground">
                {total > 0 ? `Showing ${from} to ${to} of ${total.toLocaleString()} ${noun}(s)` : `No ${noun}s`}
            </p>
            {paginator.last_page > 1 && (
                <nav className="flex items-center gap-1" aria-label="Pagination">
                    <PageButton url={prev_page_url} label="Previous page">
                        <ChevronLeft />
                    </PageButton>
                    {pages.map((link, index) =>
                        link.url === null ? (
                            <span key={`gap-${index}`} className="px-2 text-muted-foreground">
                                …
                            </span>
                        ) : (
                            <Button
                                key={link.url}
                                asChild
                                size="sm"
                                variant={link.active ? 'outline' : 'ghost'}
                                className="min-w-8"
                            >
                                <Link href={link.url} preserveScroll>
                                    {link.label}
                                </Link>
                            </Button>
                        ),
                    )}
                    <PageButton url={next_page_url} label="Next page">
                        <ChevronRight />
                    </PageButton>
                </nav>
            )}
        </div>
    );
}

function PageButton({ url, label, children }: { url: string | null; label: string; children: React.ReactNode }) {
    if (!url) {
        return (
            <Button size="icon" variant="ghost" className="size-8" disabled aria-label={label}>
                {children}
            </Button>
        );
    }

    return (
        <Button asChild size="icon" variant="ghost" className="size-8">
            <Link href={url} preserveScroll aria-label={label}>
                {children}
            </Link>
        </Button>
    );
}
