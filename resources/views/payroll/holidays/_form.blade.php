<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Holiday Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $holiday->name ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Type</label>
        <select name="holiday_type" class="form-select" required>
            <option value="regular" @selected(old('holiday_type', $holiday->holiday_type ?? '') === 'regular')>Regular</option>
            <option value="special" @selected(old('holiday_type', $holiday->holiday_type ?? '') === 'special')>Special</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Source Proclamation</label>
        <input type="text" name="source_proclamation" class="form-control"
            value="{{ old('source_proclamation', $holiday->source_proclamation ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Actual Date</label>
        <input type="date" name="actual_date" class="form-control"
            value="{{ old('actual_date', isset($holiday) ? $holiday->actual_date?->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Observed Date</label>
        <input type="date" name="observed_date" class="form-control"
            value="{{ old('observed_date', isset($holiday) ? $holiday->observed_date?->format('Y-m-d') : '') }}"
            required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Not Worked Multiplier</label>
        <input type="number" step="0.01" name="not_worked_multiplier" class="form-control"
            value="{{ old('not_worked_multiplier', $holiday->not_worked_multiplier ?? '0.00') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Worked Multiplier</label>
        <input type="number" step="0.01" name="worked_multiplier" class="form-control"
            value="{{ old('worked_multiplier', $holiday->worked_multiplier ?? '1.00') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $holiday->notes ?? '') }}</textarea>
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
