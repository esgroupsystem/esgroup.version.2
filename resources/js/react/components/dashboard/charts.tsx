import type { ReactNode } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, Label, Line, LineChart, Pie, PieChart, XAxis, YAxis } from 'recharts';
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';
import { cn } from '@/lib/utils';

/** Colour for the n-th series/slice; cycles through the five theme chart colours, then a few extras. */
const PALETTE = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', '#6366f1', '#ec4899', '#14b8a6', '#84cc16', '#64748b'];

export const chartColor = (index: number): string => PALETTE[index % PALETTE.length];

export interface Datum {
    label: string;
    value: number;
    color?: string;
}

const number = new Intl.NumberFormat('en-US');
/** Short axis ticks: 1.2K, 90M. */
const compact = new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 });
const tick = (value: number) => compact.format(value);

/** Card wrapper used by every dashboard chart and table section. */
export function DashboardCard({
    title,
    description,
    action,
    className,
    contentClassName,
    children,
}: {
    title: ReactNode;
    description?: ReactNode;
    action?: ReactNode;
    className?: string;
    contentClassName?: string;
    children: ReactNode;
}) {
    return (
        <Card className={cn('gap-4', className)}>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                {description && <CardDescription>{description}</CardDescription>}
                {action && <CardAction>{action}</CardAction>}
            </CardHeader>
            <CardContent className={contentClassName}>{children}</CardContent>
        </Card>
    );
}

/** KPI tile: label, big number, optional hint and icon. */
export function KpiCard({ label, value, hint, icon, tone }: { label: string; value: ReactNode; hint?: ReactNode; icon?: ReactNode; tone?: string }) {
    return (
        <Card className="gap-2 bg-gradient-to-t from-primary/5 to-card py-4 shadow-xs dark:bg-card">
            <CardHeader className="px-4">
                <CardDescription>{label}</CardDescription>
                <CardTitle className={cn('text-2xl font-semibold tabular-nums', tone)}>{value}</CardTitle>
                {icon && <CardAction className="text-muted-foreground [&_svg]:size-5">{icon}</CardAction>}
            </CardHeader>
            {hint && <CardContent className="px-4 text-xs text-muted-foreground">{hint}</CardContent>}
        </Card>
    );
}

export function EmptyChart({ message = 'No data yet.' }: { message?: string }) {
    return <div className="flex h-[240px] items-center justify-center text-sm text-muted-foreground">{message}</div>;
}

function configFor(data: Datum[]): ChartConfig {
    return Object.fromEntries(data.map((item, index) => [`k${index}`, { label: item.label, color: item.color ?? chartColor(index) }]));
}

/** Single-series bar chart (vertical bars, or horizontal with `horizontal`). */
export function SimpleBarChart({
    data,
    horizontal = false,
    height = 260,
    valueLabel = 'Total',
    multicolor = false,
    formatValue = (value: number) => number.format(value),
}: {
    data: Datum[];
    horizontal?: boolean;
    height?: number;
    valueLabel?: string;
    multicolor?: boolean;
    formatValue?: (value: number) => string;
}) {
    if (data.length === 0 || data.every((item) => !item.value)) return <EmptyChart />;

    const config: ChartConfig = { value: { label: valueLabel, color: 'var(--chart-2)' } };
    const rows = data.map((item, index) => ({ ...item, fill: item.color ?? (multicolor ? chartColor(index) : 'var(--color-value)') }));
    const labelWidth = Math.min(160, Math.max(60, ...data.map((item) => item.label.length * 6.5)));

    return (
        <ChartContainer config={config} className="w-full" style={{ height }}>
            <BarChart data={rows} layout={horizontal ? 'vertical' : 'horizontal'} margin={{ left: 4, right: 12, top: 8 }} accessibilityLayer>
                <CartesianGrid vertical={horizontal} horizontal={!horizontal} />
                {horizontal ? (
                    <>
                        <XAxis type="number" tickLine={false} axisLine={false} allowDecimals={false} tickFormatter={tick} />
                        <YAxis type="category" dataKey="label" tickLine={false} axisLine={false} width={labelWidth} tick={{ fontSize: 11 }} interval={0} />
                    </>
                ) : (
                    <>
                        <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={8} tick={{ fontSize: 11 }} interval="preserveStartEnd" />
                        <YAxis tickLine={false} axisLine={false} allowDecimals={false} width={48} tickFormatter={tick} />
                    </>
                )}
                <ChartTooltip cursor={false} content={<ChartTooltipContent formatter={(value) => <span className="font-mono tabular-nums">{valueLabel}: {formatValue(Number(value))}</span>} />} />
                <Bar dataKey="value" radius={4}>
                    {rows.map((row) => (
                        <Cell key={row.label} fill={row.fill} />
                    ))}
                </Bar>
            </BarChart>
        </ChartContainer>
    );
}

/** Multi-series bar or line chart over a shared category axis. */
export function SeriesChart({
    categories,
    series,
    type = 'bar',
    height = 280,
    stacked = false,
    formatValue = (value: number) => number.format(value),
}: {
    categories: string[];
    series: { key: string; label: string; values: number[]; color?: string }[];
    type?: 'bar' | 'line';
    height?: number;
    stacked?: boolean;
    formatValue?: (value: number) => string;
}) {
    if (categories.length === 0 || series.every((item) => item.values.every((value) => !value))) return <EmptyChart />;

    const config: ChartConfig = Object.fromEntries(series.map((item, index) => [item.key, { label: item.label, color: item.color ?? chartColor(index) }]));
    const rows = categories.map((category, index) => ({ label: category, ...Object.fromEntries(series.map((item) => [item.key, item.values[index] ?? 0])) }));
    const Chart = type === 'line' ? LineChart : BarChart;

    return (
        <ChartContainer config={config} className="w-full" style={{ height }}>
            <Chart data={rows} margin={{ left: 4, right: 12, top: 8 }} accessibilityLayer>
                <CartesianGrid vertical={false} />
                <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={8} tick={{ fontSize: 11 }} />
                <YAxis tickLine={false} axisLine={false} width={56} allowDecimals={false} tickFormatter={tick} />
                <ChartTooltip content={<ChartTooltipContent />} />
                {series.length > 1 && <ChartLegend content={<ChartLegendContent />} />}
                {series.map((item) =>
                    type === 'line' ? (
                        <Line key={item.key} dataKey={item.key} type="monotone" stroke={`var(--color-${item.key})`} strokeWidth={2} dot={false} />
                    ) : (
                        <Bar key={item.key} dataKey={item.key} fill={`var(--color-${item.key})`} radius={stacked ? 0 : 4} stackId={stacked ? 'a' : undefined} />
                    ),
                )}
            </Chart>
        </ChartContainer>
    );
}

/** Doughnut with the total in the middle and a legend underneath. */
export function DonutChart({ data, height = 240, centerLabel = 'Total' }: { data: Datum[]; height?: number; centerLabel?: string }) {
    const total = data.reduce((sum, item) => sum + item.value, 0);
    if (total === 0) return <EmptyChart />;

    const config = configFor(data);
    const rows = data.map((item, index) => ({ key: `k${index}`, label: item.label, value: item.value, fill: `var(--color-k${index})` }));

    return (
        <div className="grid gap-3">
            <ChartContainer config={config} className="mx-auto aspect-square w-full" style={{ maxHeight: height }}>
                <PieChart>
                    <ChartTooltip cursor={false} content={<ChartTooltipContent nameKey="key" hideLabel />} />
                    <Pie data={rows} dataKey="value" nameKey="key" innerRadius="58%" strokeWidth={4}>
                        <Label
                            content={({ viewBox }) =>
                                viewBox && 'cx' in viewBox && 'cy' in viewBox ? (
                                    <text x={viewBox.cx} y={viewBox.cy} textAnchor="middle" dominantBaseline="middle">
                                        <tspan x={viewBox.cx} y={viewBox.cy} className="fill-foreground text-2xl font-semibold">
                                            {number.format(total)}
                                        </tspan>
                                        <tspan x={viewBox.cx} y={(viewBox.cy ?? 0) + 20} className="fill-muted-foreground text-xs">
                                            {centerLabel}
                                        </tspan>
                                    </text>
                                ) : null
                            }
                        />
                    </Pie>
                </PieChart>
            </ChartContainer>
            <ul className="grid gap-1.5 text-sm sm:grid-cols-2">
                {rows.map((row, index) => (
                    <li key={row.key} className="flex items-center gap-2">
                        <span className="size-2.5 shrink-0 rounded-[2px]" style={{ background: data[index].color ?? chartColor(index) }} />
                        <span className="min-w-0 flex-1 truncate">{row.label}</span>
                        <span className="font-medium tabular-nums">{number.format(row.value)}</span>
                        <span className="w-10 text-right text-xs text-muted-foreground tabular-nums">{Math.round((row.value / total) * 100)}%</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

/** Label + value + percentage bar, for "breakdown" lists. */
export function BreakdownList({ items, emptyText = 'No data.', formatValue = (value: number) => number.format(value) }: { items: Datum[]; emptyText?: string; formatValue?: (value: number) => string }) {
    if (items.length === 0) return <p className="text-sm text-muted-foreground">{emptyText}</p>;

    const max = Math.max(...items.map((item) => item.value), 1);

    return (
        <ul className="grid gap-2.5">
            {items.map((item, index) => (
                <li key={`${item.label}-${index}`} className="grid gap-1">
                    <div className="flex items-center justify-between gap-2 text-sm">
                        <span className="truncate">{item.label}</span>
                        <span className="font-medium tabular-nums">{formatValue(item.value)}</span>
                    </div>
                    <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                        <div className="h-full rounded-full" style={{ width: `${(item.value / max) * 100}%`, background: item.color ?? chartColor(index) }} />
                    </div>
                </li>
            ))}
        </ul>
    );
}
