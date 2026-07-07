<div class="col-md-4">
    <label class="form-label">Canonical Bio ID</label>
    <input type="text" class="form-control" value="{{ $salary->employee_biometric_id }}" readonly>
    <input type="hidden" name="employee_biometric_id" id="employee_biometric_id" value="{{ $salary->employee_biometric_id }}">
    <div class="form-text">employee_biometric_id → employee_biometrics.id</div>
</div>

<div class="col-md-4">
    <label class="form-label">Legacy Biometric ID</label>
    <input type="text" class="form-control" value="{{ $salary->biometric_employee_id }}" readonly>
    <input type="hidden" name="biometric_employee_id" id="biometric_employee_id" value="{{ $salary->biometric_employee_id }}">
</div>

<div class="col-md-4">
    <label class="form-label">CrossChex ID</label>
    <input type="text" name="crosschex_id" class="form-control {{ $errors->has('crosschex_id') ? 'is-invalid' : '' }}" value="{{ old('crosschex_id', $salary->crosschex_id) }}" readonly>
    <div class="invalid-feedback">{{ $errors->first('crosschex_id') }}</div>
</div>
