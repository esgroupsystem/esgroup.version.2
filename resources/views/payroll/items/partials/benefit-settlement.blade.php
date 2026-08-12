@php
    $settlement = $item->benefitSettlement;
    $settlementMeta = data_get($item->meta, 'government_settlement', []);
    $isClosingCutoff = (string) $payroll->cutoff_type === 'first';
    $canEditSettlement = $isClosingCutoff && $payroll->status === 'draft' && auth()->user()?->can('payroll-benefit-settlements.manage');
    $reimbursementCaps = (array) data_get($settlementMeta, 'manual_reimbursement_caps', []);
@endphp

@if ($isClosingCutoff)
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-body-tertiary border-bottom py-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
                <div>
                    <h6 class="mb-1">
                        <i class="fas fa-balance-scale text-warning me-2"></i>Government Benefit Settlement
                    </h6>
                    <div class="small text-muted">
                        Controls payroll cash collection only. The exact monthly SSS / PhilHealth / Pag-IBIG liability remains in Benefits Records.
                    </div>
                </div>
                <span class="badge bg-{{ $settlement ? 'warning' : 'secondary' }}-subtle text-{{ $settlement ? 'warning' : 'secondary' }} px-3 py-2">
                    {{ $settlement ? ucwords(str_replace('_', ' ', $settlement->mode)) : 'Auto Cap (Default)' }}
                </span>
            </div>
        </div>

        <div class="card-body">
            @if (data_get($settlementMeta, 'employee_share_unrecovered', 0) > 0)
                <div class="alert alert-warning border-0 mb-3">
                    <div class="fw-semibold mb-1">Employee share not collected from payroll</div>
                    <div>
                        PHP {{ number_format((float) data_get($settlementMeta, 'employee_share_unrecovered', 0), 2) }}
                        is currently tracked as employer-advanced / unrecovered employee share. Net pay is protected from becoming negative.
                    </div>
                </div>
            @endif

            <div class="row g-3 mb-3">
                @foreach ([
                    'SSS' => ['field' => 'sss_employee', 'due' => 'sss_employee_statutory_due'],
                    'PhilHealth' => ['field' => 'philhealth_employee', 'due' => 'philhealth_employee_statutory_due'],
                    'Pag-IBIG' => ['field' => 'pagibig_employee', 'due' => 'pagibig_employee_statutory_due'],
                ] as $label => $config)
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-muted">{{ $label }}</div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Monthly EE liability</span>
                                <strong>PHP {{ number_format((float) data_get($settlementMeta, $config['due'], 0), 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>This cutoff cash</span>
                                <strong class="{{ (float) $item->{$config['field']} < 0 ? 'text-success' : '' }}">
                                    PHP {{ number_format((float) $item->{$config['field']}, 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($canEditSettlement)
                <form method="POST" action="{{ route('payroll.items.benefit-settlement.store', [$payroll, $item]) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Settlement Action</label>
                            <select name="mode" class="form-select @error('mode') is-invalid @enderror" required>
                                <option value="auto_cap" @selected(old('mode', $settlement?->mode ?? 'auto_cap') === 'auto_cap')>
                                    Auto Cap — collect only what net pay can cover
                                </option>
                                <option value="employer_advance" @selected(old('mode', $settlement?->mode) === 'employer_advance')>
                                    Employer Advance — do not collect positive EE share this cutoff
                                </option>
                                <option value="collect_full" @selected(old('mode', $settlement?->mode) === 'collect_full')>
                                    Collect Full — reject if it makes net pay negative
                                </option>
                            </select>
                            <div class="form-text">
                                Recommended for resigned/no-pay cases: Employer Advance. The government liability is still retained for remittance/accounting.
                            </div>
                            @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label fw-semibold">Employee Reimbursement / Credit</label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">SSS</span>
                                        <input type="number" step="0.01" min="0" max="{{ number_format((float) ($reimbursementCaps['sss'] ?? 0), 2, '.', '') }}" name="sss_employee_reimbursement"
                                            value="{{ old('sss_employee_reimbursement', $settlement?->sss_employee_reimbursement ?? 0) }}"
                                            class="form-control @error('sss_employee_reimbursement') is-invalid @enderror">
                                    </div>
                                    <div class="form-text">Max manual credit: PHP {{ number_format((float) ($reimbursementCaps['sss'] ?? 0), 2) }}</div>
                                    @error('sss_employee_reimbursement')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">PH</span>
                                        <input type="number" step="0.01" min="0" max="{{ number_format((float) ($reimbursementCaps['philhealth'] ?? 0), 2, '.', '') }}" name="philhealth_employee_reimbursement"
                                            value="{{ old('philhealth_employee_reimbursement', $settlement?->philhealth_employee_reimbursement ?? 0) }}"
                                            class="form-control @error('philhealth_employee_reimbursement') is-invalid @enderror">
                                    </div>
                                    <div class="form-text">Max manual credit: PHP {{ number_format((float) ($reimbursementCaps['philhealth'] ?? 0), 2) }}</div>
                                    @error('philhealth_employee_reimbursement')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">PI</span>
                                        <input type="number" step="0.01" min="0" max="{{ number_format((float) ($reimbursementCaps['pagibig'] ?? 0), 2, '.', '') }}" name="pagibig_employee_reimbursement"
                                            value="{{ old('pagibig_employee_reimbursement', $settlement?->pagibig_employee_reimbursement ?? 0) }}"
                                            class="form-control @error('pagibig_employee_reimbursement') is-invalid @enderror">
                                    </div>
                                    <div class="form-text">Max manual credit: PHP {{ number_format((float) ($reimbursementCaps['pagibig'] ?? 0), 2) }}</div>
                                    @error('pagibig_employee_reimbursement')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="form-text">
                                Manual reimbursement is capped after any automatic monthly true-up credit, so the employee cannot be refunded more than was actually withheld. It is a payroll credit, not a cancellation of statutory contribution.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">HR / Payroll Reason</label>
                            <textarea name="reason" rows="2" required class="form-control @error('reason') is-invalid @enderror"
                                placeholder="Example: Employee separated before 2nd cutoff; company will advance remaining employee share and reimburse prior cutoff EE deductions as approved by HR/accounting.">{{ old('reason', $settlement?->reason) }}</textarea>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-calculator me-1"></i> Save & Recalculate
                            </button>
                        </div>
                    </div>
                </form>
            @elseif ($settlement)
                <div class="border rounded-3 p-3 bg-body-tertiary">
                    <div class="small text-muted mb-1">Reason</div>
                    <div>{{ $settlement->reason }}</div>
                </div>
            @endif
        </div>
    </div>
@endif
