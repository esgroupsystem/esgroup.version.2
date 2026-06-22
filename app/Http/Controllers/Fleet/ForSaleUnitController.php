<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\ForSaleUnitRequest;
use App\Models\BusForSaleRecord;
use App\Services\Fleet\ForSaleUnitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForSaleUnitController extends Controller
{
    public function __construct(
        private readonly ForSaleUnitService $forSaleUnitService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'search',
            'company',
            'garage',
            'status',
        ]);

        return view('fleet.for-sale.index', $this->forSaleUnitService->getIndexData($filters));
    }

    public function create(): View
    {
        return view('fleet.for-sale.create', [
            'forSaleRecord' => new BusForSaleRecord,
            ...$this->forSaleUnitService->getFormData(),
        ]);
    }

    public function store(ForSaleUnitRequest $request): RedirectResponse
    {
        $record = $this->forSaleUnitService->create($request->validated());

        return redirect()
            ->route('fleet.for-sale-units.edit', $record)
            ->with('success', 'For sale unit has been created and synced to bus monitoring.');
    }

    public function edit(BusForSaleRecord $forSaleRecord): View
    {
        return view('fleet.for-sale.edit', [
            'forSaleRecord' => $forSaleRecord,
            ...$this->forSaleUnitService->getFormData(),
        ]);
    }

    public function update(
        ForSaleUnitRequest $request,
        BusForSaleRecord $forSaleRecord
    ): RedirectResponse {
        $this->forSaleUnitService->update($forSaleRecord, $request->validated());

        return redirect()
            ->route('fleet.for-sale-units.edit', $forSaleRecord)
            ->with('success', 'For sale unit has been updated and synced to bus monitoring.');
    }

    public function destroy(BusForSaleRecord $forSaleRecord): RedirectResponse
    {
        $this->forSaleUnitService->delete($forSaleRecord);

        return redirect()
            ->route('fleet.for-sale-units.index')
            ->with('success', 'For sale unit has been deleted.');
    }
}
