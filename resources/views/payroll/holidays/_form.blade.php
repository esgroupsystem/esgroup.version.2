@php
    $holidayType = old('holiday_type', $holiday->holiday_type ?? \App\Models\Holiday::TYPE_REGULAR);
    $standard = \App\Models\Holiday::standardMultipliers($holidayType);
    $savedNotWorked = isset($holiday) ? (float) $holiday->not_worked_multiplier : (float) $standard['not_worked_multiplier'];
    $savedWorked = isset($holiday) ? (float) $holiday->worked_multiplier : (float) $standard['worked_multiplier'];
    $hasCustomMultiplier = isset($holiday)
        && (abs($savedNotWorked - (float) $standard['not_worked_multiplier']) > 0.001
            || abs($savedWorked - (float) $standard['worked_multiplier']) > 0.001);
    $overrideMultipliers = (bool) old('override_multipliers', $hasCustomMultiplier);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Holiday Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $holiday->name ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Holiday Type</label>
        <select name="holiday_type" id="holiday_type" class="form-select" required>
            <option value="regular" @selected($holidayType === 'regular')>Regular Holiday</option>
            <option value="special" @selected($holidayType === 'special')>Special Non-Working Holiday</option>
        </select>
        <div class="form-text">Regular worked = 2.00x. Special worked = 1.30x.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Source Proclamation</label>
        <input type="text" name="source_proclamation" class="form-control"
            value="{{ old('source_proclamation', $holiday->source_proclamation ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Actual Date</label>
        <input type="date" name="actual_date" class="form-control"
            value="{{ old('actual_date', isset($holiday) ? $holiday->actual_date?->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Observed Date</label>
        <input type="date" name="observed_date" class="form-control"
            value="{{ old('observed_date', isset($holiday) ? $holiday->observed_date?->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-6">
        <div class="card border shadow-none h-100">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="fw-semibold">Payroll Multipliers</div>
                        <div class="fs-10 text-600">Automatic by holiday type. Enable override only for an approved company exception.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="override_multipliers" id="override_multipliers"
                            value="1" @checked($overrideMultipliers)>
                        <label class="form-check-label" for="override_multipliers">Custom</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Not Worked</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="10" name="not_worked_multiplier"
                                id="not_worked_multiplier" class="form-control"
                                value="{{ old('not_worked_multiplier', number_format($savedNotWorked, 2, '.', '')) }}">
                            <span class="input-group-text">x</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Worked</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="10" name="worked_multiplier"
                                id="worked_multiplier" class="form-control"
                                value="{{ old('worked_multiplier', number_format($savedWorked, 2, '.', '')) }}">
                            <span class="input-group-text">x</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Notes</label>
        <textarea name="notes" class="form-control" rows="4">{{ old('notes', $holiday->notes ?? '') }}</textarea>
    </div>

    <div class="col-md-3">
        <div class="form-check mt-4 pt-2">
            <input class="form-check-input" type="checkbox" name="is_moved" value="1"
                {{ old('is_moved', $holiday->is_moved ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">Moved observance</label>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-check mt-4 pt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                {{ old('is_active', $holiday->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const type = document.getElementById('holiday_type');
        const override = document.getElementById('override_multipliers');
        const notWorked = document.getElementById('not_worked_multiplier');
        const worked = document.getElementById('worked_multiplier');

        const defaults = {
            regular: { notWorked: '1.00', worked: '2.00' },
            special: { notWorked: '0.00', worked: '1.30' },
        };

        function refresh(forceValues = false) {
            const useOverride = override.checked;
            const values = defaults[type.value] || defaults.regular;

            if (!useOverride || forceValues) {
                notWorked.value = values.notWorked;
                worked.value = values.worked;
            }

            notWorked.readOnly = !useOverride;
            worked.readOnly = !useOverride;
            notWorked.classList.toggle('bg-light', !useOverride);
            worked.classList.toggle('bg-light', !useOverride);
        }

        type.addEventListener('change', () => refresh(true));
        override.addEventListener('change', () => refresh(!override.checked));
        refresh(false);
    });
</script>
@endpush
