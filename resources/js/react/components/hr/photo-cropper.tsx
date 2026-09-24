import { useCallback, useEffect, useRef, useState, type PointerEvent } from 'react';
import { Label } from '@/components/ui/label';

const VIEW = 240;
const OUTPUT = 400;

/**
 * Square crop with drag-to-pan and zoom (replaces the Blade Cropper.js modal).
 * Calls onChange with a JPEG data URL, which the profile endpoint accepts as
 * profile_picture_cropped.
 */
export function PhotoCropper({ file, onChange }: { file: File; onChange: (dataUrl: string) => void }) {
    const canvas = useRef<HTMLCanvasElement>(null);
    const [image, setImage] = useState<HTMLImageElement | null>(null);
    const [zoom, setZoom] = useState(1);
    const [offset, setOffset] = useState({ x: 0, y: 0 });
    const drag = useRef<{ x: number; y: number } | null>(null);
    // Keep the latest callback without re-running the draw effect: the parent
    // passes a new function each render, and calling it re-renders the parent.
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    useEffect(() => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            setImage(img);
            setZoom(1);
            setOffset({ x: 0, y: 0 });
        };
        img.src = url;

        return () => URL.revokeObjectURL(url);
    }, [file]);

    const draw = useCallback(
        (target: HTMLCanvasElement, size: number) => {
            if (!image) return;
            const context = target.getContext('2d');
            if (!context) return;

            // Cover the square, then apply zoom and pan (offset is in view pixels).
            const base = Math.max(size / image.width, size / image.height) * zoom;
            const width = image.width * base;
            const height = image.height * base;
            const ratio = size / VIEW;
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, size, size);
            context.drawImage(image, (size - width) / 2 + offset.x * ratio, (size - height) / 2 + offset.y * ratio, width, height);
        },
        [image, zoom, offset],
    );

    useEffect(() => {
        if (!canvas.current || !image) return;
        draw(canvas.current, VIEW);

        const output = document.createElement('canvas');
        output.width = OUTPUT;
        output.height = OUTPUT;
        draw(output, OUTPUT);
        onChangeRef.current(output.toDataURL('image/jpeg', 0.9));
    }, [draw, image]);

    const move = (event: PointerEvent<HTMLCanvasElement>) => {
        if (!drag.current) return;
        setOffset({ x: offset.x + event.clientX - drag.current.x, y: offset.y + event.clientY - drag.current.y });
        drag.current = { x: event.clientX, y: event.clientY };
    };

    return (
        <div className="grid justify-items-center gap-2">
            <canvas
                ref={canvas}
                width={VIEW}
                height={VIEW}
                className="size-60 cursor-grab touch-none rounded-full border shadow-sm active:cursor-grabbing"
                aria-label="Drag to position the photo"
                onPointerDown={(event) => {
                    event.currentTarget.setPointerCapture(event.pointerId);
                    drag.current = { x: event.clientX, y: event.clientY };
                }}
                onPointerMove={move}
                onPointerUp={() => (drag.current = null)}
            />
            <div className="grid w-60 gap-1">
                <Label htmlFor="photo-zoom" className="text-xs text-muted-foreground">
                    Zoom · drag the photo to position it
                </Label>
                <input id="photo-zoom" type="range" min={1} max={3} step={0.05} value={zoom} onChange={(event) => setZoom(Number(event.target.value))} />
            </div>
        </div>
    );
}
