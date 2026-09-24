import { RotateCcw, X } from 'lucide-react';
import { useLayoutEffect, useRef, useState, type CSSProperties } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

/*
 * Top-down bus seat plan (front = left, like the old seat_arrangement.png).
 *
 *   rows 1-3  upper side (3 seats)        rows 4-5  lower side (2 seats)
 *   seat 1 is the driver. 25-27 sit sideways in the middle; 48-52 are the back row.
 *
 * Each column lists its seats top -> bottom; null = no seat (door / stairs).
 */
type Column = { upper: [number | null, number | null, number | null]; lower: [number, number] };

const FRONT: Column[] = [
    { upper: [5, 4, null], lower: [3, 2] },
    { upper: [9, 8, null], lower: [7, 6] },
    { upper: [14, 13, 12], lower: [11, 10] },
    { upper: [19, 18, 17], lower: [16, 15] },
    { upper: [24, 23, 22], lower: [21, 20] },
];
const MIDDLE = [25, 26, 27];
const REAR: Column[] = [
    { upper: [32, 31, 30], lower: [29, 28] },
    { upper: [37, 36, 35], lower: [34, 33] },
    { upper: [42, 41, 40], lower: [39, 38] },
    { upper: [47, 46, 45], lower: [44, 43] },
    { upper: [52, 51, 50], lower: [49, 48] },
];

export const DRIVER_SEAT = 1;
export const SEAT_COUNT = 52;

/** "12, 13" <-> [12, 13] */
export const parseSeats = (value: string): number[] =>
    [...new Set((value.match(/\d+/g) ?? []).map(Number).filter((seat) => seat >= 1 && seat <= SEAT_COUNT))].sort((a, b) => a - b);
export const formatSeats = (seats: number[]): string => [...seats].sort((a, b) => a - b).join(', ');

// Grid geometry (px). Columns: cab, 5 front, 3 middle, 5 rear.
const SEAT = 34;
const GAP = 6;
const AISLE = 26;

interface Props {
    /** Selected seats as the form stores them ("12, 13"). */
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
    /** Show the selected seats only (ticket details); no clicking, no dimming. */
    readOnly?: boolean;
    className?: string;
}

/**
 * Animated bus seat picker: the bus drives in, the roof lifts off and the seats
 * appear. Click seats to select one or many; the value is a sorted list.
 */
export function BusSeatMap({ value, onChange, disabled: disabledProp, readOnly, className }: Props) {
    const disabled = disabledProp || readOnly;
    const selected = parseSeats(value);
    const busRef = useRef<HTMLDivElement>(null);
    const roofRef = useRef<HTMLDivElement>(null);
    const roadRef = useRef<HTMLDivElement>(null);
    const seatsRef = useRef<HTMLDivElement>(null);
    const [run, setRun] = useState(0);
    const [ready, setReady] = useState(false);

    // Layout effect: park the bus off-screen before the first paint (no flash).
    useLayoutEffect(() => {
        const bus = busRef.current;
        const roof = roofRef.current;
        const road = roadRef.current;
        const seats = seatsRef.current;
        if (!bus || !roof || !road || !seats) return;

        const animations: Animation[] = [];
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        setReady(false);

        if (reduce || typeof bus.animate !== 'function') {
            roof.style.opacity = '0';
            setReady(true);
            return;
        }

        roof.style.opacity = '1';
        const seatEls = [...seats.querySelectorAll<HTMLElement>('[data-seat]')];
        seatEls.forEach((el) => (el.style.opacity = '0'));
        // Parked off to the right until the map is actually on screen.
        bus.style.transform = 'translateX(115%)';

        let cancelled = false;
        let timer: number | undefined;

        const play = () => {
            if (cancelled) return;

            // 1. Drive in from the right (front first), with a small settle.
            const drive = bus.animate(
                [
                    { transform: 'translateX(115%)' },
                    { transform: 'translateX(-1.5%)', offset: 0.82 },
                    { transform: 'translateX(0.4%)', offset: 0.92 },
                    { transform: 'translateX(0)' },
                ],
                { duration: 1700, easing: 'cubic-bezier(.22,.8,.25,1)' },
            );
            bus.style.transform = '';
            // Lane markings rush past while the bus moves, then stop.
            const lanes = road.animate([{ backgroundPositionX: '0px' }, { backgroundPositionX: '-360px' }], { duration: 1700, easing: 'cubic-bezier(.22,.8,.25,1)' });
            animations.push(drive, lanes);

            drive.finished
                .then(() => {
                    if (cancelled) return;
                    // 2. Lift the roof off.
                    const lift = roof.animate(
                        [
                            { opacity: 1, transform: 'translateY(0) scale(1)' },
                            { opacity: 0, transform: 'translateY(-18px) scale(1.03)' },
                        ],
                        { duration: 550, easing: 'ease-in', fill: 'forwards' },
                    );
                    animations.push(lift);

                    // 3. Seats pop in from the front to the back.
                    seatEls.forEach((el) => {
                        const order = Number(el.dataset.order ?? 0);
                        const pop = el.animate(
                            [
                                { opacity: 0, transform: 'scale(.3) translateY(6px)' },
                                { opacity: 1, transform: 'scale(1.08)', offset: 0.7 },
                                { opacity: 1, transform: 'scale(1)' },
                            ],
                            { duration: 320, delay: 200 + order * 14, easing: 'ease-out', fill: 'backwards' },
                        );
                        el.style.opacity = '';
                        animations.push(pop);
                    });

                    return lift.finished;
                })
                .then(() => !cancelled && setReady(true))
                .catch(() => undefined);
        };

        // Start once a third of the map is visible (after the page has painted), so the drive is seen.
        const observer = new IntersectionObserver(
            (entries) => {
                if (!entries.some((entry) => entry.isIntersecting)) return;
                observer.disconnect();
                timer = window.setTimeout(play, 150);
            },
            { threshold: 0.35 },
        );
        observer.observe(road.parentElement ?? road);

        return () => {
            cancelled = true;
            observer.disconnect();
            window.clearTimeout(timer);
            animations.forEach((animation) => animation.cancel());
            bus.style.transform = '';
            seatEls.forEach((el) => (el.style.opacity = ''));
        };
    }, [run]);

    const toggle = (seat: number) => {
        if (disabled) return;
        onChange(formatSeats(selected.includes(seat) ? selected.filter((item) => item !== seat) : [...selected, seat]));
    };

    // Columns: 1 = cab, 2-6 front, 7-9 middle, 10-14 rear. Rows: 1-3 upper, 4 aisle, 5-6 lower.
    const gridStyle: CSSProperties = {
        gridTemplateColumns: `78px repeat(5, ${SEAT}px) repeat(3, ${SEAT - 4}px) repeat(5, ${SEAT}px)`,
        gridTemplateRows: `repeat(3, ${SEAT}px) ${AISLE}px repeat(2, ${SEAT}px)`,
        gap: GAP,
    };

    let order = 0;
    const seatButton = (seat: number, style: CSSProperties, extra?: string) => {
        const isSelected = selected.includes(seat);
        const current = order++;

        return (
            <button
                key={seat}
                type="button"
                data-seat={seat}
                data-order={current}
                style={style}
                disabled={disabled}
                aria-pressed={isSelected}
                aria-label={seat === DRIVER_SEAT ? 'Seat 1 (driver)' : `Seat ${seat}`}
                title={seat === DRIVER_SEAT ? 'Seat 1 · Driver' : `Seat ${seat}`}
                onClick={() => toggle(seat)}
                className={cn(
                    'group relative flex items-center justify-center rounded-[7px] border text-xs font-bold tabular-nums shadow-sm transition-[background-color,color,box-shadow,transform] duration-150 outline-none',
                    'focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-1 focus-visible:ring-offset-slate-700',
                    isSelected
                        ? 'border-sky-300 bg-sky-500 text-white shadow-sky-500/40 ring-2 ring-sky-300/60'
                        : 'border-slate-300 bg-white text-slate-800 hover:-translate-y-px hover:bg-sky-50',
                    disabled && (readOnly ? 'cursor-default hover:translate-y-0' : 'cursor-not-allowed opacity-70'),
                    extra,
                )}
            >
                {seat}
                {/* Backrest: seats face the front (left). */}
                <span className={cn('absolute top-1 right-[-5px] bottom-1 w-[5px] rounded-r-sm', isSelected ? 'bg-sky-700' : 'bg-slate-400')} aria-hidden />
            </button>
        );
    };

    const column = (col: Column, gridColumn: number) => (
        <>
            {col.upper.map((seat, row) => (seat === null ? null : seatButton(seat, { gridColumn, gridRow: row + 1 })))}
            {col.lower.map((seat, row) => seatButton(seat, { gridColumn, gridRow: row + 5 }))}
        </>
    );

    return (
        <div className={cn('grid gap-3', className)}>
            {/* Road */}
            <div className="relative overflow-hidden rounded-xl bg-slate-800 py-6 dark:bg-slate-900">
                <div
                    ref={roadRef}
                    aria-hidden
                    className="pointer-events-none absolute inset-x-0 top-1/2 h-1 -translate-y-1/2 opacity-40"
                    style={{ backgroundImage: 'repeating-linear-gradient(90deg, #facc15 0 36px, transparent 36px 80px)' }}
                />
                <div aria-hidden className="pointer-events-none absolute inset-x-0 top-2 h-0.5 bg-white/30" />
                <div aria-hidden className="pointer-events-none absolute inset-x-0 bottom-2 h-0.5 bg-white/30" />

                <div className="overflow-x-auto px-4">
                    <div ref={busRef} className="relative mx-auto w-max will-change-transform">
                        {/* Wheels */}
                        {[96, 486].map((left) => (
                            <span key={left} aria-hidden className="absolute h-2.5 w-12 rounded-sm bg-black" style={{ left, top: -6 }} />
                        ))}
                        {[96, 486].map((left) => (
                            <span key={`b${left}`} aria-hidden className="absolute h-2.5 w-12 rounded-sm bg-black" style={{ left, bottom: -6 }} />
                        ))}

                        {/* Body */}
                        <div className="relative rounded-l-[34px] rounded-r-xl border-[3px] border-slate-200 bg-slate-100 p-2 shadow-2xl shadow-black/40">
                            {/* Headlights and windshield (front = left). */}
                            <span aria-hidden className="absolute top-3 -left-[3px] h-4 w-1.5 rounded-full bg-amber-200 shadow-[0_0_12px_4px_rgba(253,230,138,.7)]" />
                            <span aria-hidden className="absolute bottom-3 -left-[3px] h-4 w-1.5 rounded-full bg-amber-200 shadow-[0_0_12px_4px_rgba(253,230,138,.7)]" />
                            <span aria-hidden className="absolute inset-y-3 left-1.5 w-2 rounded-l-full bg-sky-300/70" />
                            {/* Tail lights */}
                            <span aria-hidden className="absolute top-3 -right-[3px] h-5 w-1 rounded-full bg-red-500" />
                            <span aria-hidden className="absolute bottom-3 -right-[3px] h-5 w-1 rounded-full bg-red-500" />

                            {/* Cabin floor + seats */}
                            <div ref={seatsRef} className="relative grid rounded-l-[26px] rounded-r-lg bg-slate-600 py-3 pr-4 pl-3" style={gridStyle}>
                                {/* Aisle strip */}
                                <div aria-hidden className="rounded-sm bg-slate-500/60" style={{ gridColumn: '2 / -1', gridRow: 4 }} />
                                <div aria-hidden className="flex items-center text-[9px] font-semibold tracking-[0.3em] text-slate-300 uppercase" style={{ gridColumn: '7 / 10', gridRow: 4, justifyContent: 'center' }}>
                                    aisle
                                </div>
                                {/* Front door (upper side, no seat) */}
                                <div aria-hidden className="rounded-md border border-dashed border-slate-400/70" style={{ gridColumn: '2 / 4', gridRow: 3 }} />
                                {/* Middle door (upper side above 25-27) */}
                                <div aria-hidden className="rounded-md border border-dashed border-slate-400/70" style={{ gridColumn: '7 / 10', gridRow: '1 / 4' }} />

                                {/* Driver */}
                                <div aria-hidden className="flex items-center justify-center" style={{ gridColumn: 1, gridRow: '1 / 4' }}>
                                    <span className="rounded-md bg-slate-800 px-1.5 py-1 text-[9px] font-semibold tracking-wider text-slate-300 uppercase">Front</span>
                                </div>
                                <div className="flex items-center gap-1.5" style={{ gridColumn: 1, gridRow: '5 / 7' }}>
                                    <SteeringWheel />
                                    {seatButton(DRIVER_SEAT, { width: SEAT, height: SEAT * 1.4 })}
                                </div>

                                {FRONT.map((col, index) => column(col, index + 2))}
                                {MIDDLE.map((seat, index) => seatButton(seat, { gridColumn: index + 7, gridRow: '5 / 7', alignSelf: 'center', height: SEAT }))}
                                {REAR.map((col, index) => column(col, index + 10))}
                            </div>

                            {/* Roof: covers the cabin until the bus has parked. */}
                            <div
                                ref={roofRef}
                                aria-hidden
                                className="pointer-events-none absolute inset-2 overflow-hidden rounded-l-[26px] rounded-r-lg bg-white"
                                style={{ opacity: ready ? 0 : 1 }}
                            >
                                <div className="absolute inset-x-0 top-0 h-3 bg-red-600" />
                                <div className="absolute inset-x-0 top-3 h-1.5 bg-sky-600" />
                                <div className="absolute inset-x-0 bottom-3 h-1.5 bg-sky-600" />
                                <div className="absolute inset-x-0 bottom-0 h-3 bg-red-600" />
                                {/* Aircon units */}
                                <div className="absolute top-1/2 left-[22%] h-12 w-20 -translate-y-1/2 rounded-md border border-slate-300 bg-slate-100" />
                                <div className="absolute top-1/2 left-[62%] h-12 w-20 -translate-y-1/2 rounded-md border border-slate-300 bg-slate-100" />
                                <div className="absolute inset-0 flex items-center justify-center">
                                    <span className="rounded-md bg-white/90 px-3 py-1 text-sm font-black tracking-[0.25em] text-slate-800 uppercase">Jell Group</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Selection summary */}
            <div className="flex flex-wrap items-center gap-2 text-sm">
                <span className="text-muted-foreground">{selected.length ? `Selected seat${selected.length === 1 ? '' : 's'}:` : readOnly ? 'No seat recorded.' : 'Click seats to select them (you can pick more than one).'}</span>
                {selected.map((seat) => (
                    <button
                        key={seat}
                        type="button"
                        disabled={disabled}
                        onClick={() => toggle(seat)}
                        className="inline-flex items-center gap-1 rounded-md bg-sky-500 px-2 py-0.5 text-xs font-semibold text-white hover:bg-sky-600"
                        aria-label={`Remove seat ${seat}`}
                    >
                        {seat === DRIVER_SEAT ? '1 (driver)' : seat}
                        <X className="size-3" />
                    </button>
                ))}
                <span className="ml-auto flex gap-1">
                    {selected.length > 0 && !disabled && (
                        <Button type="button" variant="ghost" size="sm" onClick={() => onChange('')}>
                            Clear
                        </Button>
                    )}
                    <Button type="button" variant="ghost" size="sm" onClick={() => setRun((value) => value + 1)} title="Play the animation again">
                        <RotateCcw />
                        Replay
                    </Button>
                </span>
            </div>
        </div>
    );
}

function SteeringWheel() {
    return (
        <svg viewBox="0 0 24 24" className="size-7 shrink-0 text-slate-900" aria-hidden>
            <circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" strokeWidth="2.5" />
            <circle cx="12" cy="12" r="2.5" fill="currentColor" />
            <path d="M12 14.5V21.5M9.8 11 3.2 9.5M14.2 11l6.6-1.5" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
        </svg>
    );
}
