@php
    $encodedMechanics = old(
        'mechanic_names',
        isset($jobOrder) ? $jobOrder->mechanic_names_list : [],
    );

    $mechanicRows = collect(is_array($encodedMechanics) ? $encodedMechanics : [$encodedMechanics])
        ->map(fn ($name) => trim((string) $name))
        ->filter()
        ->values();

    if ($mechanicRows->isEmpty()) {
        $mechanicRows = collect(['']);
    }

    $selectedRepairTypes = collect(old(
        'repair_types',
        isset($jobOrder) ? ($jobOrder->repair_types ?? []) : [],
    ))->map(fn ($type) => (string) $type);
@endphp

<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div>
            <label class="form-label fw-semibold mb-1">
                Mechanic Name(s)
            </label>
            <div class="fs-11 text-600">
                Add every mechanic who performed or supervised the repair.
            </div>
        </div>

        <button type="button" class="btn btn-falcon-primary btn-sm" id="add-mechanic-row">
            <span class="fas fa-plus me-1"></span>
            Add Mechanic
        </button>
    </div>

    <div id="mechanic-rows" class="vstack gap-2">
        @foreach ($mechanicRows as $mechanicName)
            <div class="input-group mechanic-row">
                <span class="input-group-text bg-white">
                    <span class="fas fa-user-gear text-primary"></span>
                </span>

                <input type="text" name="mechanic_names[]" value="{{ $mechanicName }}"
                    class="form-control @error('mechanic_names.*') is-invalid @enderror"
                    maxlength="255" placeholder="Enter mechanic full name">

                <button type="button" class="btn btn-falcon-danger remove-mechanic-row"
                    title="Remove mechanic">
                    <span class="fas fa-trash"></span>
                </button>
            </div>
        @endforeach
    </div>

    @error('mechanic_names')
        <div class="text-danger fs-11 mt-2">{{ $message }}</div>
    @enderror

    @error('mechanic_names.*')
        <div class="text-danger fs-11 mt-2">{{ $message }}</div>
    @enderror
</div>

<div>
    <label class="form-label fw-semibold mb-1">
        Type of Repair Done
    </label>

    <div class="fs-11 text-600 mb-3">
        Multiple repair types may be selected for one job order.
    </div>

    <div class="row g-2">
        @foreach ($repairTypes as $repairType)
            <div class="col-md-6 col-xl-4">
                <label class="border rounded-3 p-3 d-flex align-items-center gap-2 h-100 bg-white">
                    <input type="checkbox" name="repair_types[]" value="{{ $repairType->value }}"
                        class="form-check-input mt-0" @checked($selectedRepairTypes->contains($repairType->value))>

                    <span class="badge rounded-pill {{ $repairType->badgeClass() }}">
                        <span class="{{ $repairType->icon() }} me-1"></span>
                        {{ $repairType->label() }}
                    </span>
                </label>
            </div>
        @endforeach
    </div>

    @error('repair_types')
        <div class="text-danger fs-11 mt-2">{{ $message }}</div>
    @enderror

    @error('repair_types.*')
        <div class="text-danger fs-11 mt-2">{{ $message }}</div>
    @enderror
</div>

<template id="mechanic-row-template">
    <div class="input-group mechanic-row">
        <span class="input-group-text bg-white">
            <span class="fas fa-user-gear text-primary"></span>
        </span>

        <input type="text" name="mechanic_names[]" class="form-control"
            maxlength="255" placeholder="Enter mechanic full name">

        <button type="button" class="btn btn-falcon-danger remove-mechanic-row"
            title="Remove mechanic">
            <span class="fas fa-trash"></span>
        </button>
    </div>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rowsContainer = document.getElementById('mechanic-rows');
            const addButton = document.getElementById('add-mechanic-row');
            const rowTemplate = document.getElementById('mechanic-row-template');

            if (!rowsContainer || !addButton || !rowTemplate) {
                return;
            }

            function refreshRemoveButtons() {
                const rows = rowsContainer.querySelectorAll('.mechanic-row');

                rows.forEach(function(row) {
                    const removeButton = row.querySelector('.remove-mechanic-row');

                    if (removeButton) {
                        removeButton.disabled = rows.length === 1;
                    }
                });
            }

            addButton.addEventListener('click', function() {
                rowsContainer.appendChild(rowTemplate.content.cloneNode(true));
                refreshRemoveButtons();

                const inputs = rowsContainer.querySelectorAll('input[name="mechanic_names[]"]');
                inputs[inputs.length - 1]?.focus();
            });

            rowsContainer.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-mechanic-row');

                if (!removeButton) {
                    return;
                }

                const rows = rowsContainer.querySelectorAll('.mechanic-row');

                if (rows.length === 1) {
                    rows[0].querySelector('input')?.focus();
                    return;
                }

                removeButton.closest('.mechanic-row')?.remove();
                refreshRemoveButtons();
            });

            refreshRemoveButtons();
        });
    </script>
@endpush
