<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Payroll\Concerns\SharesPayrollTestOptions;
use App\Http\Requests\Payroll\PayrollSettingVersionRequest;
use App\Http\Resources\Payroll\PayrollSettingVersionResource;
use App\Models\PayrollSettingVersion;
use App\Services\Payroll\PayrollSettingsService;
use App\Services\Payroll\PayrollSimulationService;
use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payroll → Payroll Settings → Rates & contributions (versions by effective date).
 */
final class PayrollSettingController extends Controller
{
    use SharesPayrollTestOptions;

    public function __construct(
        private readonly PayrollSettingsService $settings,
        private readonly PayrollSimulationService $simulation,
    ) {}

    public function index(Request $request): Response
    {
        $versions = $this->settings->list()->values();
        $usage = $this->settings->usage();
        $current = $this->settings->versionFor(null);
        $today = now('Asia/Manila')->toDateString();
        $canManage = $request->user()->can('payroll-settings.manage');

        $rows = $versions->map(function (PayrollSettingVersion $version, int $index) use ($versions, $usage, $current, $today, $canManage, $request): array {
            $previous = $versions->get($index + 1);
            $row = PayrollSettingVersionResource::make($version)->resolve($request);

            return $row + [
                'is_current' => $current['id'] === $version->id,
                'is_future' => $version->effective_from->toDateString() > $today,
                'changes' => $previous
                    ? PayrollSettingCatalog::changes((array) $previous->values, (array) $version->values)
                    : PayrollSettingCatalog::changes(PayrollSettingCatalog::defaults(), (array) $version->values),
                'first' => $previous === null,
                'usage' => $usage[$version->id] ?? ['total' => 0, 'finalized' => 0],
                'urls' => $canManage ? [
                    'edit' => route('payroll-settings.versions.edit', $version),
                    'destroy' => route('payroll-settings.versions.destroy', $version),
                    'copy' => route('payroll-settings.versions.create', ['from' => $version->id]),
                ] : null,
            ];
        })->all();

        return Inertia::render('payroll/settings/index', [
            'versions' => $rows,
            'current' => ['id' => $current['id'], 'label' => $current['label'], 'effective_from' => $current['effective_from'], 'values' => $current['values']],
            'sections' => PayrollSettingCatalog::publicSections(),
            'can' => ['manage' => $canManage],
            'urls' => [
                'create' => route('payroll-settings.versions.create'),
                ...$this->tabUrls(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $from = $request->integer('from') ?: null;
        $source = $from ? $this->settings->findOrFail($from) : null;
        $values = $source
            ? PayrollSettingCatalog::normalize((array) $source->values)
            : $this->settings->versionFor(null)['values'];

        return $this->form($request, null, [
            'effective_from' => now('Asia/Manila')->addDay()->toDateString(),
            'label' => '',
            'notes' => '',
            'values' => $values,
        ]);
    }

    public function store(PayrollSettingVersionRequest $request): RedirectResponse
    {
        $version = $this->settings->create($request->validated(), $request->user()?->id);

        return redirect()
            ->route('payroll-settings.index')
            ->with('success', sprintf('Settings "%s" saved. Payrolls from %s use these values.', $version->label, $version->effective_from->format('M d, Y')));
    }

    public function edit(Request $request, PayrollSettingVersion $version): Response
    {
        $resource = PayrollSettingVersionResource::make($version)->resolve($request);

        return $this->form($request, $version, [
            'effective_from' => $resource['effective_from'],
            'label' => $resource['label'],
            'notes' => $resource['notes'],
            'values' => $resource['values'],
        ]);
    }

    public function update(PayrollSettingVersionRequest $request, PayrollSettingVersion $version): RedirectResponse
    {
        $this->settings->update($version, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('payroll-settings.index')
            ->with('success', 'Settings updated. Finalized payrolls keep their old values; drafts use the new values when recomputed.');
    }

    public function destroy(PayrollSettingVersion $version): RedirectResponse
    {
        $this->settings->delete($version);

        return redirect()->route('payroll-settings.index')->with('success', 'Settings version deleted.');
    }

    /** @param  array<string, mixed>  $values */
    private function form(Request $request, ?PayrollSettingVersion $version, array $values): Response
    {
        $usage = $version ? ($this->settings->usage()[$version->id] ?? null) : null;

        return Inertia::render('payroll/settings/form', [
            'version' => $version ? ['id' => $version->id, 'label' => $version->label, 'usage' => $usage] : null,
            'values' => $values,
            'sections' => PayrollSettingCatalog::publicSections(),
            'defaults' => PayrollSettingCatalog::defaults(),
            'test' => $this->testOptions($this->simulation),
            'urls' => [
                'index' => route('payroll-settings.index'),
                'submit' => $version ? route('payroll-settings.versions.update', $version) : route('payroll-settings.versions.store'),
            ],
        ]);
    }

    /** @return array<string, string> */
    private function tabUrls(): array
    {
        return [
            'settings' => route('payroll-settings.index'),
            'rules' => route('payroll-settings.rules.index'),
            'test' => route('payroll-settings.test.index'),
        ];
    }
}
