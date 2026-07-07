@php
    $resolvePerson = function ($person) {
        $canonicalId = $person->employee_biometric_id ?? ($person->id ?? null);
        $legacyId = $person->biometric_employee_id ?? ($person->legacy_biometric_employee_id ?? ($person->source_employee_id ?? ($person->source_crosschex_id ?? ($person->source_employee_no ?? null))));
        $employeeNo = $person->employee_no ?? ($person->effective_employee_no ?? ($person->display_employee_no ?? ($person->source_employee_no ?? ($person->source_employee_id ?? null))));
        $employeeName = $person->employee_name ?? ($person->effective_name ?? ($person->display_name ?? ($person->source_employee_name ?? ($person->source_crosschex_account_name ?? 'Unknown Employee'))));
        $crosschexId = $person->crosschex_id ?? ($person->source_crosschex_id ?? null);

        return [
            'canonical_id' => $canonicalId,
            'legacy_id' => $legacyId,
            'employee_no' => $employeeNo,
            'employee_name' => $employeeName,
            'crosschex_id' => $crosschexId,
        ];
    };
@endphp

<div class="col-12">
    <div class="card border-0 bg-body-tertiary">
        <div class="card-body">
            <label class="form-label fw-semibold">Select Employee from Biometrics</label>
            <div class="position-relative">
                <input type="text" id="employeePicker" class="form-control" placeholder="Search employee name / employee no / CrossChex ID" autocomplete="off">

                <div id="employeeDropdown" class="card shadow-sm border mt-1 d-none position-absolute w-100" style="z-index: 1050; max-height: 280px; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        @foreach ($people as $person)
                            @php($resolvedPerson = $resolvePerson($person))
                            @continue(empty($resolvedPerson['canonical_id']))

                            <button type="button"
                                class="list-group-item list-group-item-action employee-option"
                                data-employee-biometric-id="{{ $resolvedPerson['canonical_id'] }}"
                                data-biometric="{{ $resolvedPerson['legacy_id'] }}"
                                data-empno="{{ $resolvedPerson['employee_no'] }}"
                                data-name="{{ $resolvedPerson['employee_name'] }}"
                                data-crosschex="{{ $resolvedPerson['crosschex_id'] }}"
                                data-search="{{ strtolower(trim(($resolvedPerson['canonical_id'] ?? '') . ' ' . ($resolvedPerson['employee_name'] ?? '') . ' ' . ($resolvedPerson['employee_no'] ?? '') . ' ' . ($resolvedPerson['legacy_id'] ?? '') . ' ' . ($resolvedPerson['crosschex_id'] ?? ''))) }}">
                                <div class="fw-semibold text-dark">{{ $resolvedPerson['employee_name'] }}</div>
                                <div class="small text-muted">
                                    Bio ID: {{ $resolvedPerson['canonical_id'] }} |
                                    {{ $resolvedPerson['employee_no'] ?: 'No Employee No' }}
                                    {{ $resolvedPerson['legacy_id'] ? '| Legacy: ' . $resolvedPerson['legacy_id'] : '' }}
                                    {{ $resolvedPerson['crosschex_id'] ? '| CrossChex: ' . $resolvedPerson['crosschex_id'] : '' }}
                                </div>
                            </button>
                        @endforeach

                        <div id="employeeNoResult" class="list-group-item text-muted d-none">No employee found.</div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="employee_biometric_id" id="employee_biometric_id" value="{{ old('employee_biometric_id') }}">
            <input type="hidden" name="biometric_employee_id" id="biometric_employee_id" value="{{ old('biometric_employee_id') }}">
            <input type="hidden" name="crosschex_id" id="crosschex_id" value="{{ old('crosschex_id') }}">

            <div class="text-danger small mt-2">{{ $errors->first('employee_biometric_id') }}</div>
            <div class="text-danger small mt-1">{{ $errors->first('biometric_employee_id') }}</div>
        </div>
    </div>
</div>
