<script>
    document.addEventListener('DOMContentLoaded', function() {
        const workingDaysPerMonth = 22;
        const hoursPerDay = 8;
        const minutesPerHour = 60;
        const loanPrefixes = ['sss_loan', 'pagibig_loan', 'philhealth_loan', 'cash_advance', 'other_loan'];

        function input(id) { return document.getElementById(id); }

        function numberValue(id) {
            const el = input(id);
            return el ? (parseFloat(el.value) || 0) : 0;
        }

        function stringValue(id, fallback = 'none') {
            const el = input(id);
            return el ? (el.value || fallback) : fallback;
        }

        function money(value) {
            return Number(value || 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function setText(id, value) {
            const el = input(id);
            if (el) { el.textContent = value; }
        }

        function setInputValue(id, value, decimals = 2) {
            const el = input(id);
            if (el) { el.value = Number(value || 0).toFixed(decimals); }
        }

        function monthlyBasicSalary() {
            const rateType = stringValue('rate_type', 'daily');
            const basicSalary = numberValue('basic_salary');
            if (basicSalary <= 0) { return 0; }
            return rateType === 'monthly' ? basicSalary : basicSalary * workingDaysPerMonth;
        }

        function computeSalaryRates() {
            const rateType = stringValue('rate_type', 'daily');
            const basicSalary = numberValue('basic_salary');
            const dailyRate = basicSalary > 0 ? (rateType === 'monthly' ? basicSalary / workingDaysPerMonth : basicSalary) : 0;
            const hourlyRate = dailyRate / hoursPerDay;
            const perMinuteRate = hourlyRate / minutesPerHour;

            setInputValue('ot_rate_per_hour', hourlyRate, 2);
            setInputValue('late_deduction_per_minute', perMinuteRate, 4);
            setInputValue('undertime_deduction_per_minute', perMinuteRate, 4);
            setInputValue('absent_deduction_per_day', dailyRate, 2);
        }

        function sssMonthlySalaryCredit(monthlySalary) {
            if (monthlySalary <= 0) { return 0; }
            if (monthlySalary < 5250) { return 5000; }
            if (monthlySalary >= 34750) { return 35000; }
            return Math.round(monthlySalary / 500) * 500;
        }

        function sssEmployeeShare(monthlySalary) { return sssMonthlySalaryCredit(monthlySalary) * 0.05; }

        function pagibigEmployeeShare(monthlySalary) {
            if (monthlySalary <= 0) { return 0; }
            const baseSalary = Math.min(monthlySalary, 10000);
            return baseSalary * (baseSalary <= 1500 ? 0.01 : 0.02);
        }

        function philhealthEmployeeShare(monthlySalary) {
            if (monthlySalary <= 0) { return 0; }
            const baseSalary = Math.min(Math.max(monthlySalary, 10000), 100000);
            return (baseSalary * 0.05) / 2;
        }

        function monthlyToCutoff(monthlyAmount, schedule, cutoff) {
            if (monthlyAmount <= 0 || schedule === 'none') { return 0; }
            if (schedule === 'every_cutoff') { return monthlyAmount / 2; }
            if (schedule === 'first_cutoff' && cutoff === 'first') { return monthlyAmount; }
            if (schedule === 'second_cutoff' && cutoff === 'second') { return monthlyAmount; }
            return 0;
        }

        function fixedDeductionToCutoff(paymentAmount, schedule, cutoff) {
            if (paymentAmount <= 0 || schedule === 'none') { return 0; }
            if (schedule === 'every_cutoff') { return paymentAmount; }
            if (schedule === 'first_cutoff' && cutoff === 'first') { return paymentAmount; }
            if (schedule === 'second_cutoff' && cutoff === 'second') { return paymentAmount; }
            return 0;
        }

        function nextCutoffDate(afterDate, schedule) {
            const allowedDays = schedule === 'first_cutoff' ? [25] : (schedule === 'second_cutoff' ? [11] : [11, 25]);
            const candidates = [];

            for (let monthOffset = 0; monthOffset <= 24; monthOffset++) {
                allowedDays.forEach(function(day) {
                    const candidate = new Date(afterDate.getFullYear(), afterDate.getMonth() + monthOffset, day);
                    if (candidate > afterDate) { candidates.push(candidate); }
                });
            }

            candidates.sort(function(a, b) { return a - b; });
            return candidates[0] || afterDate;
        }

        function estimatedLastPaymentDateByValues(totalAmount, paymentAmount, schedule, startDateValue) {
            if (totalAmount <= 0 || paymentAmount <= 0 || schedule === 'none') { return '—'; }

            const paymentCount = Math.ceil(totalAmount / paymentAmount);
            let cursor = startDateValue ? new Date(startDateValue + 'T00:00:00') : new Date();
            cursor.setDate(cursor.getDate() - 1);

            for (let i = 0; i < paymentCount; i++) { cursor = nextCutoffDate(cursor, schedule); }

            return cursor.toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        }

        function estimatedLastPaymentDate(prefix) {
            return estimatedLastPaymentDateByValues(
                numberValue(`${prefix}_total_amount`),
                numberValue(`${prefix}_payment_amount`),
                stringValue(`${prefix}_deduction_schedule`, 'none'),
                stringValue(`${prefix}_start_date`, '')
            );
        }

        function rowNumberValue(row, selector) {
            const el = row.querySelector(selector);
            return el ? (parseFloat(el.value) || 0) : 0;
        }

        function rowStringValue(row, selector, fallback = 'none') {
            const el = row.querySelector(selector);
            return el ? (el.value || fallback) : fallback;
        }

        function otherDeductionsTotal(cutoff) {
            let total = 0;

            document.querySelectorAll('.other-deduction-row').forEach(function(row) {
                const totalAmount = rowNumberValue(row, '.other-deduction-total');
                const paymentAmount = rowNumberValue(row, '.other-deduction-payment');
                const schedule = rowStringValue(row, '.other-deduction-schedule', 'none');
                const startDate = rowStringValue(row, '.other-deduction-start', '');

                total += fixedDeductionToCutoff(paymentAmount, schedule, cutoff);

                const lastPayment = row.querySelector('.other-deduction-last');
                if (lastPayment) {
                    lastPayment.textContent = estimatedLastPaymentDateByValues(totalAmount, paymentAmount, schedule, startDate);
                }
            });

            return total;
        }

        function loanDeductionTotal(cutoff) {
            let total = 0;

            loanPrefixes.forEach(function(prefix) {
                total += fixedDeductionToCutoff(
                    numberValue(`${prefix}_payment_amount`),
                    stringValue(`${prefix}_deduction_schedule`, 'none'),
                    cutoff
                );

                setText(`${prefix}_last_payment`, estimatedLastPaymentDate(prefix));
            });

            return total + otherDeductionsTotal(cutoff);
        }

        function refreshOtherDeductionsEmptyState() {
            const emptyState = input('otherDeductionsEmptyState');
            const rows = document.querySelectorAll('.other-deduction-row');
            if (emptyState) { emptyState.classList.toggle('d-none', rows.length > 0); }
        }

        function bindOtherDeductionRow(row) {
            row.querySelectorAll('.other-deduction-input').forEach(function(element) {
                element.addEventListener('input', computePreview);
                element.addEventListener('change', computePreview);
            });

            const removeButton = row.querySelector('.remove-other-deduction');
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                    refreshOtherDeductionsEmptyState();
                    computePreview();
                });
            }
        }

        function addOtherDeductionRow() {
            const body = input('otherDeductionsBody');
            const template = input('otherDeductionTemplate');
            if (!body || !template) { return; }

            const index = Date.now();
            body.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index));

            const row = body.querySelector('.other-deduction-row:last-child');
            if (row) { bindOtherDeductionRow(row); }

            refreshOtherDeductionsEmptyState();
            computePreview();
        }

        function setupOtherDeductions() {
            const addButton = input('addOtherDeductionBtn');
            if (addButton) { addButton.addEventListener('click', addOtherDeductionRow); }

            document.querySelectorAll('.other-deduction-row').forEach(function(row) { bindOtherDeductionRow(row); });
            refreshOtherDeductionsEmptyState();
        }

        function computePreview() {
            computeSalaryRates();

            const monthlyBasic = monthlyBasicSalary();
            const monthlySss = sssEmployeeShare(monthlyBasic);
            const monthlyPagibig = pagibigEmployeeShare(monthlyBasic);
            const monthlyPhilhealth = philhealthEmployeeShare(monthlyBasic);

            const firstGovernment =
                monthlyToCutoff(monthlySss, stringValue('sss_contribution_cutoff'), 'first') +
                monthlyToCutoff(monthlyPagibig, stringValue('pagibig_contribution_cutoff'), 'first') +
                monthlyToCutoff(monthlyPhilhealth, stringValue('philhealth_contribution_cutoff'), 'first');

            const secondGovernment =
                monthlyToCutoff(monthlySss, stringValue('sss_contribution_cutoff'), 'second') +
                monthlyToCutoff(monthlyPagibig, stringValue('pagibig_contribution_cutoff'), 'second') +
                monthlyToCutoff(monthlyPhilhealth, stringValue('philhealth_contribution_cutoff'), 'second');

            const firstAllowance =
                monthlyToCutoff(numberValue('allowance'), stringValue('allowance_release_schedule'), 'first') +
                monthlyToCutoff(numberValue('sim_load_allowance'), stringValue('sim_load_release_schedule'), 'first');

            const secondAllowance =
                monthlyToCutoff(numberValue('allowance'), stringValue('allowance_release_schedule'), 'second') +
                monthlyToCutoff(numberValue('sim_load_allowance'), stringValue('sim_load_release_schedule'), 'second');

            const firstLoans = loanDeductionTotal('first');
            const secondLoans = loanDeductionTotal('second');

            const firstGross = (monthlyBasic / 2) + firstAllowance;
            const secondGross = (monthlyBasic / 2) + secondAllowance;
            const firstDeductions = firstGovernment + firstLoans;
            const secondDeductions = secondGovernment + secondLoans;

            setText('preview_monthly_basic', money(monthlyBasic));
            setText('preview_monthly_sss', money(monthlySss));
            setText('preview_monthly_pagibig', money(monthlyPagibig));
            setText('preview_monthly_philhealth', money(monthlyPhilhealth));
            setText('first_gross', money(firstGross));
            setText('first_deductions', money(firstDeductions));
            setText('first_net', money(firstGross - firstDeductions));
            setText('second_gross', money(secondGross));
            setText('second_deductions', money(secondDeductions));
            setText('second_net', money(secondGross - secondDeductions));
        }

        function setupEmployeePicker() {
            const picker = input('employeePicker');
            const dropdown = input('employeeDropdown');
            if (!picker || !dropdown) { return; }

            const wrapper = picker.closest('.position-relative');
            const options = Array.from(document.querySelectorAll('.employee-option'));
            const noResult = input('employeeNoResult');
            const employeeBiometricInput = input('employee_biometric_id');
            const biometricInput = input('biometric_employee_id');
            const employeeNoInput = input('employee_no');
            const employeeNameInput = input('employee_name');
            const crosschexInput = input('crosschex_id');

            function showDropdown() { dropdown.classList.remove('d-none'); }
            function hideDropdown() { dropdown.classList.add('d-none'); }

            function clearSelectedFields() {
                if (employeeBiometricInput) { employeeBiometricInput.value = ''; }
                if (biometricInput) { biometricInput.value = ''; }
                if (employeeNoInput) { employeeNoInput.value = ''; }
                if (employeeNameInput) { employeeNameInput.value = ''; }
                if (crosschexInput) { crosschexInput.value = ''; }
            }

            function filterOptions() {
                const keyword = picker.value.trim().toLowerCase();
                let visibleCount = 0;

                options.forEach(function(option) {
                    const haystack = option.dataset.search || '';
                    const matched = keyword === '' || haystack.includes(keyword);
                    option.classList.toggle('d-none', !matched);
                    if (matched) { visibleCount++; }
                });

                if (noResult) { noResult.classList.toggle('d-none', visibleCount !== 0); }
            }

            options.forEach(function(option) {
                option.addEventListener('click', function() {
                    const name = option.dataset.name || '';
                    const empno = option.dataset.empno || '';

                    picker.value = name + (empno ? ' | ' + empno : '');
                    if (employeeBiometricInput) { employeeBiometricInput.value = option.dataset.employeeBiometricId || ''; }
                    if (biometricInput) { biometricInput.value = option.dataset.biometric || ''; }
                    if (employeeNoInput) { employeeNoInput.value = empno; }
                    if (employeeNameInput) { employeeNameInput.value = name; }
                    if (crosschexInput) { crosschexInput.value = option.dataset.crosschex || ''; }

                    hideDropdown();
                });
            });

            picker.addEventListener('focus', function() { filterOptions(); showDropdown(); });
            picker.addEventListener('input', function() { clearSelectedFields(); filterOptions(); showDropdown(); });

            document.addEventListener('click', function(event) {
                if (!wrapper.contains(event.target)) { hideDropdown(); }
            });
        }

        document.querySelectorAll('.payroll-preview-input, #rate_type, #basic_salary').forEach(function(element) {
            element.addEventListener('input', computePreview);
            element.addEventListener('change', computePreview);
        });

        setupEmployeePicker();
        setupOtherDeductions();
        computePreview();
    });
</script>
