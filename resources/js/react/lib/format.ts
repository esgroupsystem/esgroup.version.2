export function initials(name: string): string {
    return (
        name
            .split(/[\s,]+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase() ?? '')
            .join('') || 'U'
    );
}

export function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

/** "Sep 23, 2026" + "02:10 PM" in Philippine time. */
export function splitDateTime(iso: string | null): { date: string; time: string } | null {
    if (!iso) return null;

    const value = new Date(iso);

    return {
        date: new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Manila',
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        }).format(value),
        time: new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
        }).format(value),
    };
}

const pesoFormat = new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/** "₱ 1,234.56" (negative amounts as "-₱ 1,234.56"). */
export function peso(value: number | string | null | undefined): string {
    const amount = Number(value ?? 0);
    return `${amount < 0 ? '-' : ''}₱ ${pesoFormat.format(Math.abs(amount))}`;
}
