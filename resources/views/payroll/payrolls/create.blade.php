@extends('layouts.app')

@section('title', 'Generate Payroll')

@push('styles')
    <style>
        .generate-payroll-page {
            --payroll-border: var(--falcon-border-color, #d8e2ef);
            --payroll-muted: var(--falcon-gray-600, #748194);
        }

        .generate-payroll-header {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(
                    135deg,
                    rgba(44, 123, 229, 0.14) 0%,
                    rgba(44, 123, 229, 0.04) 55%,
                    rgba(39, 188, 253, 0.09) 100%
                );
        }

        .generate-payroll-header::before {
            position: absolute;
            top: -115px;
            right: -60px;
            width: 270px;
            height: 270px;
            content: "";
            border-radius: 50%;
            background: rgba(44, 123, 229, 0.08);
            pointer-events: none;
        }

        .generate-payroll-header::after {
            position: absolute;
            right: 155px;
            bottom: -90px;
            width: 180px;
            height: 180px;
            content: "";
            border-radius: 50%;
            background: rgba(39, 188, 253, 0.06);
            pointer-events: none;
        }

        .generate-payroll-header .card-body {
            position: relative;
            z-index: 1;
        }

        .page-heading-icon {
            display: inline-flex;
            width: 56px;
            height: 56px;
            flex: 0 0 56px;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.14);
            font-size: 1.4rem;
        }

        .form-section-card {
            border: 1px solid var(--payroll-border);
            border-radius: 0.625rem;
            background: var(--falcon-card-bg, #fff);
        }

        .form-section-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--payroll-border);
            background: var(--falcon-gray-100, #f9fafd);
        }

        .form-section-body {
            padding: 1.25rem;
        }

        .section-icon {
            display: inline-flex;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .payroll-form-label {
            margin-bottom: 0.45rem;
            color: var(--falcon-gray-800, #4d5969);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .payroll-form-label .required {
            margin-left: 0.15rem;
            color: var(--falcon-danger, #e63757);
        }

        .payroll-form-control {
            min-height: 43px;
        }

        .payroll-helper-text {
            margin-top: 0.4rem;
            color: var(--payroll-muted);
            font-size: 0.72rem;
            line-height: 1.45;
        }

        .payroll-basis-card {
            border: 1px solid rgba(44, 123, 229, 0.2);
            border-radius: 0.625rem;
            background: rgba(44, 123, 229, 0.055);
        }

        .payroll-preview-card {
            position: sticky;
            top: 1rem;
        }

        .preview-icon {
            display: inline-flex;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.12);
        }

        .preview-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 0;
            border-bottom: 1px dashed var(--payroll-border);
        }

        .preview-item:last-child {
            border-bottom: 0;
        }

        .preview-label {
            color: var(--payroll-muted);
            font-size: 0.72rem;
        }

        .preview-value {
            max-width: 62%;
            color: var(--falcon-gray-900, #344050);
            font-size: 0.78rem;
            font-weight: 600;
            text-align: right;
        }

        .rebuild-summary-box {
            padding: 1rem;
            border: 1px solid var(--payroll-border);
            border-radius: 0.625rem;
            background: var(--falcon-gray-100, #f9fafd);
        }

        .generate-button {
            min-width: 175px;
        }

        /*
         * Full-screen payroll processing overlay
         */
        .payroll-loading-overlay {
            position: fixed;
            z-index: 99999;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            overflow-y: auto;
            background: rgba(11, 23, 39, 0.72);
            backdrop-filter: blur(5px);
            cursor: wait;
        }

        .payroll-loading-overlay.is-visible {
            display: flex;
        }

        .payroll-loading-card {
            width: 100%;
            max-width: 510px;
            overflow: hidden;
            border: 0;
            border-radius: 1rem;
            background: var(--falcon-card-bg, #fff);
            box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, 0.28);
            cursor: wait;
        }

        .payroll-loading-header {
            padding: 1.75rem 1.75rem 1.25rem;
            text-align: center;
        }

        .payroll-spinner-container {
            position: relative;
            display: inline-flex;
            width: 92px;
            height: 92px;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.1rem;
        }

        .payroll-spinner-ring {
            position: absolute;
            width: 92px;
            height: 92px;
            border: 6px solid rgba(44, 123, 229, 0.14);
            border-top-color: var(--falcon-primary, #2c7be5);
            border-radius: 50%;
            animation: payroll-spin 0.85s linear infinite;
        }

        .payroll-spinner-icon {
            display: inline-flex;
            width: 62px;
            height: 62px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.1);
            font-size: 1.35rem;
        }

        @keyframes payroll-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .payroll-progress-wrapper {
            padding: 0 1.75rem 1.5rem;
        }

        .payroll-progress {
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--falcon-gray-200, #edf2f9);
        }

        .payroll-progress .progress-bar {
            position: relative;
            min-width: 2%;
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        .payroll-progress .progress-bar::after {
            position: absolute;
            inset: 0;
            content: "";
            background-image:
                linear-gradient(
                    45deg,
                    rgba(255, 255, 255, 0.18) 25%,
                    transparent 25%,
                    transparent 50%,
                    rgba(255, 255, 255, 0.18) 50%,
                    rgba(255, 255, 255, 0.18) 75%,
                    transparent 75%,
                    transparent
                );
            background-size: 1rem 1rem;
            animation: payroll-progress-stripes 0.8s linear infinite;
        }

        @keyframes payroll-progress-stripes {
            from {
                background-position: 1rem 0;
            }

            to {
                background-position: 0 0;
            }
        }

        .payroll-percentage {
            color: var(--falcon-primary, #2c7be5);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .payroll-loading-details {
            padding: 1.15rem 1.75rem;
            border-top: 1px solid var(--payroll-border);
            border-bottom: 1px solid var(--payroll-border);
            background: var(--falcon-gray-100, #f9fafd);
        }

        .loading-detail-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.35rem 0;
        }

        .loading-detail-label {
            color: var(--payroll-muted);
            font-size: 0.72rem;
        }

        .loading-detail-value {
            max-width: 65%;
            color: var(--falcon-gray-900, #344050);
            font-size: 0.75rem;
            font-weight: 600;
            text-align: right;
        }

        .payroll-loading-footer {
            padding: 1rem 1.75rem 1.4rem;
            text-align: center;
        }

        body.payroll-is-processing {
            overflow: hidden !important;
        }

        @media (max-width: 991.98px) {
            .payroll-preview-card {
                position: static;
            }

            .generate-payroll-header::before {
                right: -130px;
            }
        }

        @media (max-width: 575.98px) {
            .page-heading-icon {
                width: 48px;
                height: 48px;
                flex-basis: 48px;
            }

            .generate-button {
                width: 100%;
            }

            .payroll-loading-header,
            .payroll-progress-wrapper,
            .payroll-loading-details,
            .payroll-loading-footer {
                padding-right: 1.25rem;
                padding-left: 1.25rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid generate-payroll-page" data-layout="container">
        <div class="content">

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
                    <div class="d-flex align-items-start">
                        <span class="fas fa-exclamation-circle fs-5 me-3 mt-1"></span>

                        <div class="flex-1">
                            <div class="fw-semibold mb-1">
                                Payroll generation could not be started
                            </div>

                            <div class="fs-10">
                                {{ $errors->first() }}
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close"
                        ></button>
                    </div>
                </div>
            @endif

            {{-- Page header --}}
            <div class="card generate-payroll-header border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                        <div class="d-flex align-items-start">
                            <div class="page-heading-icon me-3">
                                <span class="fas fa-calculator"></span>
                            </div>

                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="mb-0 text-900">
                                        Generate Payroll
                                    </h3>

                                    <span class="badge rounded-pill bg-primary-subtle text-primary">
                                        Payroll Processing
                                    </span>
                                </div>

                                <p class="mb-0 text-600" style="max-width: 790px;">
                                    Generate payroll using attendance summaries, approved adjustments,
                                    employee salary configurations, government contributions, loans,
                                    allowances, and payroll deduction schedules.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('payroll.index') }}"
                            class="btn btn-falcon-default"
                        >
                            <span class="fas fa-arrow-left me-2"></span>
                            Back to Payroll
                        </a>
                    </div>
                </div>
            </div>

            <form
                id="generatePayrollForm"
                method="POST"
                action="{{ route('payroll.store') }}"
                novalidate
            >
                @csrf

                <div class="row g-3">
                    {{-- Main form --}}
                    <div class="col-12 col-xl-8">
                        <div class="d-flex flex-column gap-3">

                            {{-- Payroll group --}}
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <div class="d-flex align-items-center">
                                        <div class="section-icon bg-primary-subtle text-primary me-3">
                                            <span class="fas fa-users"></span>
                                        </div>

                                        <div>
                                            <h6 class="mb-1 text-900">
                                                Payroll Group
                                            </h6>

                                            <p class="mb-0 fs-10 text-600">
                                                Select the employee group included in this payroll run.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label
                                                for="garage_group"
                                                class="payroll-form-label"
                                            >
                                                Payroll Group
                                                <span class="required">*</span>
                                            </label>

                                            <select
                                                id="garage_group"
                                                name="garage_group"
                                                class="form-select payroll-form-control
                                                    @error('garage_group') is-invalid @enderror"
                                                required
                                            >
                                                <option value="">
                                                    Select payroll group
                                                </option>

                                                @foreach ($payrollGroups as $value => $label)
                                                    <option
                                                        value="{{ $value }}"
                                                        @selected(old('garage_group') == $value)
                                                    >
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error('garage_group')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <div class="payroll-helper-text">
                                                Only active and eligible employees assigned to the selected
                                                payroll group will be evaluated.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cutoff details --}}
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <div class="d-flex align-items-center">
                                        <div class="section-icon bg-info-subtle text-info me-3">
                                            <span class="fas fa-calendar-alt"></span>
                                        </div>

                                        <div>
                                            <h6 class="mb-1 text-900">
                                                Payroll Cutoff
                                            </h6>

                                            <p class="mb-0 fs-10 text-600">
                                                Define the payroll contribution month and attendance period.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="cutoff_month"
                                                class="payroll-form-label"
                                            >
                                                Cutoff Month
                                                <span class="required">*</span>
                                            </label>

                                            <select
                                                id="cutoff_month"
                                                name="cutoff_month"
                                                class="form-select payroll-form-control
                                                    @error('cutoff_month') is-invalid @enderror"
                                                required
                                            >
                                                @for ($month = 1; $month <= 12; $month++)
                                                    <option
                                                        value="{{ $month }}"
                                                        @selected(
                                                            (int) old(
                                                                'cutoff_month',
                                                                $defaultCutoffMonth
                                                            ) === $month
                                                        )
                                                    >
                                                        {{ \Carbon\Carbon::create(null, $month, 1)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>

                                            @error('cutoff_month')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label
                                                for="cutoff_year"
                                                class="payroll-form-label"
                                            >
                                                Cutoff Year
                                                <span class="required">*</span>
                                            </label>

                                            <input
                                                id="cutoff_year"
                                                type="number"
                                                name="cutoff_year"
                                                class="form-control payroll-form-control
                                                    @error('cutoff_year') is-invalid @enderror"
                                                value="{{ old('cutoff_year', $defaultCutoffYear) }}"
                                                min="2020"
                                                max="2100"
                                                required
                                            >

                                            @error('cutoff_year')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label
                                                for="cutoff_type"
                                                class="payroll-form-label"
                                            >
                                                Cutoff Type
                                                <span class="required">*</span>
                                            </label>

                                            <select
                                                id="cutoff_type"
                                                name="cutoff_type"
                                                class="form-select payroll-form-control
                                                    @error('cutoff_type') is-invalid @enderror"
                                                required
                                            >
                                                <option
                                                    value="first"
                                                    @selected(
                                                        old(
                                                            'cutoff_type',
                                                            $defaultCutoffType
                                                        ) === 'first'
                                                    )
                                                >
                                                    1st Cutoff (11–25)
                                                </option>

                                                <option
                                                    value="second"
                                                    @selected(
                                                        old(
                                                            'cutoff_type',
                                                            $defaultCutoffType
                                                        ) === 'second'
                                                    )
                                                >
                                                    2nd Cutoff (26–10 next month)
                                                </option>
                                            </select>

                                            @error('cutoff_type')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <div class="payroll-basis-card p-3">
                                                <div class="d-flex align-items-start">
                                                    <span class="fas fa-info-circle text-primary mt-1 me-3"></span>

                                                    <div>
                                                        <div class="fw-semibold text-900 mb-1">
                                                            Payroll contribution basis
                                                        </div>

                                                        <div class="fs-10 text-600">
                                                            A second cutoff ending on February 10 and a first
                                                            cutoff covering February 11–25 are assigned to the
                                                            February contribution month. Government contribution
                                                            schedules remain configurable through
                                                            <code>config/payroll.php</code>.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Processing options --}}
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <div class="d-flex align-items-center">
                                        <div class="section-icon bg-warning-subtle text-warning me-3">
                                            <span class="fas fa-cogs"></span>
                                        </div>

                                        <div>
                                            <h6 class="mb-1 text-900">
                                                Processing Options
                                            </h6>

                                            <p class="mb-0 fs-10 text-600">
                                                Configure attendance rebuilding and payroll notes.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="rebuild-summary-box">
                                                <input
                                                    type="hidden"
                                                    name="rebuild_summary"
                                                    value="0"
                                                >

                                                <div class="form-check form-switch mb-0">
                                                    <input
                                                        id="rebuild_summary"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="rebuild_summary"
                                                        value="1"
                                                        @checked(old('rebuild_summary', '1'))
                                                    >

                                                    <label
                                                        class="form-check-label fw-semibold text-900"
                                                        for="rebuild_summary"
                                                    >
                                                        Rebuild attendance summary
                                                    </label>
                                                </div>

                                                <div class="payroll-helper-text ms-4">
                                                    Recalculate daily attendance summaries before generating
                                                    payroll. Enable this when biometric logs, schedules, leaves,
                                                    offsets, or attendance adjustments were recently changed.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label
                                                for="remarks"
                                                class="payroll-form-label"
                                            >
                                                Payroll Remarks
                                            </label>

                                            <textarea
                                                id="remarks"
                                                name="remarks"
                                                rows="4"
                                                class="form-control @error('remarks') is-invalid @enderror"
                                                placeholder="Enter optional notes about this payroll run"
                                            >{{ old('remarks') }}</textarea>

                                            @error('remarks')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <div class="payroll-helper-text">
                                                Remarks will be stored with the payroll record for audit and
                                                future reference.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                        <div class="fs-10 text-600">
                                            <span class="fas fa-shield-alt text-success me-1"></span>
                                            Payroll generation uses a protected server-side database transaction.
                                        </div>

                                        <div class="d-flex flex-column flex-sm-row gap-2">
                                            <a
                                                id="cancelPayrollButton"
                                                href="{{ route('payroll.index') }}"
                                                class="btn btn-falcon-default"
                                            >
                                                Cancel
                                            </a>

                                            <button
                                                id="generatePayrollButton"
                                                type="submit"
                                                class="btn btn-primary generate-button"
                                            >
                                                <span class="fas fa-play me-2"></span>
                                                Generate Payroll
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payroll summary --}}
                    <div class="col-12 col-xl-4">
                        <div class="card payroll-preview-card border-0 shadow-sm">
                            <div class="card-header bg-body-tertiary border-bottom py-3">
                                <div class="d-flex align-items-center">
                                    <div class="preview-icon me-3">
                                        <span class="fas fa-file-invoice-dollar"></span>
                                    </div>

                                    <div>
                                        <h6 class="mb-1 text-900">
                                            Payroll Run Summary
                                        </h6>

                                        <p class="mb-0 fs-10 text-600">
                                            Review the selected payroll configuration.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="preview-item">
                                    <div>
                                        <div class="preview-label">
                                            Payroll Group
                                        </div>
                                    </div>

                                    <div
                                        id="previewPayrollGroup"
                                        class="preview-value"
                                    >
                                        Not selected
                                    </div>
                                </div>

                                <div class="preview-item">
                                    <div>
                                        <div class="preview-label">
                                            Contribution Month
                                        </div>
                                    </div>

                                    <div
                                        id="previewContributionMonth"
                                        class="preview-value"
                                    >
                                        —
                                    </div>
                                </div>

                                <div class="preview-item">
                                    <div>
                                        <div class="preview-label">
                                            Cutoff Period
                                        </div>
                                    </div>

                                    <div
                                        id="previewCutoffType"
                                        class="preview-value"
                                    >
                                        —
                                    </div>
                                </div>

                                <div class="preview-item">
                                    <div>
                                        <div class="preview-label">
                                            Attendance Summary
                                        </div>
                                    </div>

                                    <div
                                        id="previewRebuildSummary"
                                        class="preview-value"
                                    >
                                        Rebuild enabled
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 mt-3 mb-0">
                                    <div class="d-flex align-items-start">
                                        <span class="fas fa-exclamation-triangle mt-1 me-2"></span>

                                        <div class="fs-10">
                                            Verify the payroll group and cutoff period before generating.
                                            Avoid generating the same payroll period twice.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Full-screen blocking payroll loading overlay --}}
    <div
        id="payrollLoadingOverlay"
        class="payroll-loading-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="payrollLoadingTitle"
        aria-describedby="payrollLoadingDescription"
    >
        <div class="payroll-loading-card">
            <div class="payroll-loading-header">
                <div class="payroll-spinner-container">
                    <div class="payroll-spinner-ring"></div>

                    <div class="payroll-spinner-icon">
                        <span class="fas fa-calculator"></span>
                    </div>
                </div>

                <h4 id="payrollLoadingTitle" class="mb-2 text-900">
                    Generating Payroll
                </h4>

                <p
                    id="payrollLoadingDescription"
                    class="mb-0 text-600"
                >
                    Preparing payroll information. Do not close or refresh this page.
                </p>
            </div>

            <div class="payroll-progress-wrapper">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div
                            id="payrollProcessingStage"
                            class="fw-semibold text-900"
                        >
                            Validating payroll configuration
                        </div>

                        <div class="fs-10 text-600">
                            Estimated processing progress
                        </div>
                    </div>

                    <div
                        id="payrollProgressPercentage"
                        class="payroll-percentage"
                    >
                        0%
                    </div>
                </div>

                <div
                    class="progress payroll-progress"
                    role="progressbar"
                    aria-label="Payroll generation progress"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="0"
                >
                    <div
                        id="payrollProgressBar"
                        class="progress-bar bg-primary"
                        style="width: 0%;"
                    ></div>
                </div>
            </div>

            <div class="payroll-loading-details">
                <div class="loading-detail-row">
                    <div class="loading-detail-label">
                        Payroll Group
                    </div>

                    <div
                        id="loadingPayrollGroup"
                        class="loading-detail-value"
                    >
                        —
                    </div>
                </div>

                <div class="loading-detail-row">
                    <div class="loading-detail-label">
                        Payroll Period
                    </div>

                    <div
                        id="loadingPayrollPeriod"
                        class="loading-detail-value"
                    >
                        —
                    </div>
                </div>

                <div class="loading-detail-row">
                    <div class="loading-detail-label">
                        Attendance Rebuild
                    </div>

                    <div
                        id="loadingRebuildSummary"
                        class="loading-detail-value"
                    >
                        —
                    </div>
                </div>

                <div class="loading-detail-row">
                    <div class="loading-detail-label">
                        Elapsed Time
                    </div>

                    <div
                        id="loadingElapsedTime"
                        class="loading-detail-value"
                    >
                        0 seconds
                    </div>
                </div>
            </div>

            <div class="payroll-loading-footer">
                <div class="fs-10 text-600">
                    <span class="fas fa-lock text-success me-1"></span>
                    The page is temporarily locked to prevent duplicate payroll processing.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            'use strict';

            const form = document.getElementById('generatePayrollForm');
            const payrollGroup = document.getElementById('garage_group');
            const cutoffMonth = document.getElementById('cutoff_month');
            const cutoffYear = document.getElementById('cutoff_year');
            const cutoffType = document.getElementById('cutoff_type');
            const rebuildSummary = document.getElementById('rebuild_summary');

            const previewPayrollGroup = document.getElementById('previewPayrollGroup');
            const previewContributionMonth = document.getElementById('previewContributionMonth');
            const previewCutoffType = document.getElementById('previewCutoffType');
            const previewRebuildSummary = document.getElementById('previewRebuildSummary');

            const loadingOverlay = document.getElementById('payrollLoadingOverlay');
            const loadingPayrollGroup = document.getElementById('loadingPayrollGroup');
            const loadingPayrollPeriod = document.getElementById('loadingPayrollPeriod');
            const loadingRebuildSummary = document.getElementById('loadingRebuildSummary');
            const loadingElapsedTime = document.getElementById('loadingElapsedTime');

            const progressBar = document.getElementById('payrollProgressBar');
            const progressContainer = progressBar.closest('.progress');
            const progressPercentage = document.getElementById('payrollProgressPercentage');
            const processingStage = document.getElementById('payrollProcessingStage');

            const generateButton = document.getElementById('generatePayrollButton');
            const cancelButton = document.getElementById('cancelPayrollButton');

            let isSubmitting = false;
            let currentProgress = 0;
            let progressTimer = null;
            let elapsedTimer = null;
            let startedAt = null;

            const processingStages = [
                {
                    minimum: 0,
                    maximum: 12,
                    message: 'Validating payroll configuration'
                },
                {
                    minimum: 13,
                    maximum: 28,
                    message: 'Preparing attendance summaries'
                },
                {
                    minimum: 29,
                    maximum: 44,
                    message: 'Loading eligible employee records'
                },
                {
                    minimum: 45,
                    maximum: 61,
                    message: 'Calculating salaries and earnings'
                },
                {
                    minimum: 62,
                    maximum: 76,
                    message: 'Calculating attendance deductions'
                },
                {
                    minimum: 77,
                    maximum: 87,
                    message: 'Applying contributions and loans'
                },
                {
                    minimum: 88,
                    maximum: 94,
                    message: 'Saving payroll items and payment logs'
                },
                {
                    minimum: 95,
                    maximum: 100,
                    message: 'Finalizing payroll records'
                }
            ];

            function getSelectedText(selectElement, fallback = 'Not selected') {
                const selectedOption = selectElement.options[selectElement.selectedIndex];

                if (!selectedOption || !selectedOption.value) {
                    return fallback;
                }

                return selectedOption.text.trim();
            }

            function updatePayrollPreview() {
                const groupText = getSelectedText(payrollGroup);
                const monthText = getSelectedText(cutoffMonth, '—');
                const yearText = cutoffYear.value || '—';
                const cutoffText = getSelectedText(cutoffType, '—');

                previewPayrollGroup.textContent = groupText;
                previewContributionMonth.textContent = `${monthText} ${yearText}`;
                previewCutoffType.textContent = cutoffText;
                previewRebuildSummary.textContent = rebuildSummary.checked
                    ? 'Rebuild enabled'
                    : 'Use existing summary';
            }

            function getStageMessage(progress) {
                const stage = processingStages.find(function (item) {
                    return progress >= item.minimum && progress <= item.maximum;
                });

                return stage
                    ? stage.message
                    : 'Finalizing payroll records';
            }

            function updateProgress(progress) {
                currentProgress = Math.min(Math.max(progress, 0), 97);

                const roundedProgress = Math.round(currentProgress);

                progressBar.style.width = `${roundedProgress}%`;
                progressPercentage.textContent = `${roundedProgress}%`;
                progressContainer.setAttribute('aria-valuenow', roundedProgress);
                processingStage.textContent = getStageMessage(roundedProgress);
            }

            function getNextProgressIncrement(progress) {
                if (progress < 15) {
                    return 5;
                }

                if (progress < 35) {
                    return 4;
                }

                if (progress < 60) {
                    return 3;
                }

                if (progress < 80) {
                    return 2;
                }

                if (progress < 92) {
                    return 1;
                }

                return 0.3;
            }

            function startEstimatedProgress() {
                updateProgress(3);

                progressTimer = window.setInterval(function () {
                    if (currentProgress >= 97) {
                        window.clearInterval(progressTimer);
                        return;
                    }

                    const increment = getNextProgressIncrement(currentProgress);
                    const randomFactor = 0.75 + Math.random() * 0.5;

                    updateProgress(currentProgress + increment * randomFactor);
                }, 850);
            }

            function startElapsedTimer() {
                startedAt = Date.now();

                elapsedTimer = window.setInterval(function () {
                    const elapsedSeconds = Math.floor(
                        (Date.now() - startedAt) / 1000
                    );

                    loadingElapsedTime.textContent =
                        `${elapsedSeconds} ${elapsedSeconds === 1 ? 'second' : 'seconds'}`;
                }, 1000);
            }

            function populateLoadingDetails() {
                const groupText = getSelectedText(payrollGroup);
                const monthText = getSelectedText(cutoffMonth, '—');
                const yearText = cutoffYear.value || '—';
                const cutoffText = getSelectedText(cutoffType, '—');

                loadingPayrollGroup.textContent = groupText;
                loadingPayrollPeriod.textContent =
                    `${monthText} ${yearText} — ${cutoffText}`;

                loadingRebuildSummary.textContent = rebuildSummary.checked
                    ? 'Enabled'
                    : 'Disabled';
            }

            function showLoadingOverlay() {
                populateLoadingDetails();

                loadingOverlay.classList.add('is-visible');
                loadingOverlay.setAttribute('aria-hidden', 'false');

                document.body.classList.add('payroll-is-processing');

                generateButton.disabled = true;
                generateButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' +
                    'Generating...';

                cancelButton.classList.add('disabled');
                cancelButton.setAttribute('aria-disabled', 'true');
                cancelButton.setAttribute('tabindex', '-1');

                startEstimatedProgress();
                startElapsedTimer();
            }

            function resetLoadingOverlay() {
                isSubmitting = false;
                currentProgress = 0;

                window.clearInterval(progressTimer);
                window.clearInterval(elapsedTimer);

                updateProgress(0);

                loadingOverlay.classList.remove('is-visible');
                loadingOverlay.setAttribute('aria-hidden', 'true');

                document.body.classList.remove('payroll-is-processing');

                generateButton.disabled = false;
                generateButton.innerHTML =
                    '<span class="fas fa-play me-2"></span>Generate Payroll';

                cancelButton.classList.remove('disabled');
                cancelButton.removeAttribute('aria-disabled');
                cancelButton.removeAttribute('tabindex');

                loadingElapsedTime.textContent = '0 seconds';
            }

            payrollGroup.addEventListener('change', updatePayrollPreview);
            cutoffMonth.addEventListener('change', updatePayrollPreview);
            cutoffYear.addEventListener('input', updatePayrollPreview);
            cutoffType.addEventListener('change', updatePayrollPreview);
            rebuildSummary.addEventListener('change', updatePayrollPreview);

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (isSubmitting) {
                    return;
                }

                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    form.reportValidity();
                    return;
                }

                isSubmitting = true;

                showLoadingOverlay();

                /*
                 * Delay the native submission briefly so the browser can render
                 * the blocking overlay before Laravel starts processing.
                 */
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(function () {
                        window.setTimeout(function () {
                            HTMLFormElement.prototype.submit.call(form);
                        }, 100);
                    });
                });
            });

            /*
             * Reset the overlay when returning through browser back-forward cache.
             */
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    resetLoadingOverlay();
                }
            });

            updatePayrollPreview();
        });
    </script>
@endpush
