<?php

declare(strict_types=1);

namespace App\Http\Controllers\IT;

use App\Enums\CctvConcernStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IT\StoreCctvConcernRequest;
use App\Http\Requests\IT\UpdateCctvConcernRequest;
use App\Http\Resources\IT\CctvConcernExportResource;
use App\Http\Resources\IT\CctvConcernRowResource;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\ItInventoryItem;
use App\Models\User;
use App\Services\IT\CctvConcernService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * IT Support → CCTV Concern.
 */
final class CctvConcernController extends Controller
{
    public function __construct(
        private readonly CctvConcernService $concerns,
    ) {}

    public function index(Request $request): Response
    {
        [$search, $status] = $this->filters($request);
        $user = $request->user();

        return Inertia::render('it/cctv/index', [
            'concerns' => $this->concerns->paginate($search, $status)
                ->through(fn (CctvConcern $concern): array => CctvConcernRowResource::make($concern)->resolve($request)),
            'stats' => $this->concerns->stats($search, $status),
            'buses' => $this->concerns->busOptions()
                ->map(fn (BusDetail $bus): array => ['value' => (string) $bus->id, 'label' => $bus->displayName()])
                ->values(),
            'agents' => $this->concerns->agents()
                ->map(fn (User $agent): array => ['id' => (string) $agent->id, 'name' => $agent->full_name])
                ->values(),
            'inventoryItems' => $this->concerns->inventoryOptions()->map(fn (ItInventoryItem $item): array => [
                'value' => (string) $item->id,
                'label' => $item->item_name,
                'hint' => 'Stock: '.$item->stock_qty.' '.$item->unit.($item->brand ? ' | '.$item->brand : ''),
            ])->values(),
            'statuses' => CctvConcernStatus::values(),
            'issueTypes' => CctvConcern::ISSUE_TYPES,
            'filters' => ['q' => $search, 'status' => $status],
            'openId' => $request->integer('open') ?: null,
            'reporter' => (string) ($user?->full_name ?? ''),
            'can' => [
                'create' => (bool) $user?->can('cctv.create'),
                'update' => (bool) $user?->can('cctv.update'),
                'delete' => (bool) $user?->can('cctv.delete'),
                'export' => (bool) $user?->can('cctv.export'),
            ],
            'urls' => [
                'index' => route('concern.cctv.index'),
                'store' => route('concern.cctv.store'),
                'update' => route('concern.cctv.update', '__ID__'),
                'destroy' => route('concern.cctv.destroy', '__ID__'),
                'print' => route('concern.export', ['type' => 'print', 'q' => $search ?: null, 'status' => $status ?: null]),
                'csv' => route('concern.export', ['type' => 'csv', 'q' => $search ?: null, 'status' => $status ?: null]),
            ],
        ]);
    }

    public function store(StoreCctvConcernRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        try {
            $this->concerns->create($request->validated(), $user);
        } catch (Throwable $exception) {
            return redirect()
                ->route('concern.cctv.index')
                ->withInput()
                ->withErrors(['error' => $exception->getMessage()]);
        }

        return redirect()->route('concern.cctv.index')->with('success', 'CCTV Job Order created successfully.');
    }

    /** The concern opens in the list's View / Edit dialog. */
    public function view(int $id): RedirectResponse
    {
        $concern = $this->concerns->find($id);

        return redirect()->route('concern.cctv.index', ['q' => $concern->jo_no, 'open' => $concern->id]);
    }

    public function update(UpdateCctvConcernRequest $request, int $id): RedirectResponse
    {
        try {
            $this->concerns->update($this->concerns->find($id), $request->validated());
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['error' => $exception->getMessage()]);
        }

        return back()->with('success', 'Job Order updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->concerns->delete($this->concerns->find($id));
        } catch (Throwable $exception) {
            return redirect()->route('concern.cctv.index')->withErrors(['error' => $exception->getMessage()]);
        }

        return redirect()->route('concern.cctv.index')->with('success', 'Job Order deleted.');
    }

    /** `print` = React print page, `csv` = download; both use the list filters. */
    public function export(Request $request, string $type): Response|StreamedResponse
    {
        abort_unless(in_array($type, ['print', 'csv'], true), 404);

        [$search, $status] = $this->filters($request);
        $rows = $this->concerns->exportRows($search, $status)->map(fn (CctvConcern $concern) => CctvConcernExportResource::make($concern));

        if ($type === 'print') {
            return Inertia::render('it/cctv/print', [
                'rows' => $rows->map(fn (CctvConcernExportResource $row): array => $row->resolve($request))->values(),
                'status' => $status ?: 'All',
                'search' => $search,
                'generated' => now()->format('F d, Y h:i A'),
            ]);
        }

        $fileName = 'cctv-job-orders-'.strtolower(str_replace(' ', '-', $status ?: 'all')).'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, CctvConcernExportResource::CSV_HEADINGS, ',', '"', '\\');
            foreach ($rows as $row) {
                fputcsv($handle, $row->toCsvRow(), ',', '"', '\\');
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    /** @return array{0: string, 1: string} search and a known status (or '') */
    private function filters(Request $request): array
    {
        return [
            trim((string) $request->input('q', '')),
            $this->concerns->normalizeStatus(trim((string) $request->input('status', ''))),
        ];
    }
}
