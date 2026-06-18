<script>
    // show filename next to file input in Edit 201 modal
    document.querySelectorAll('.file-input').forEach(function(el) {
        el.addEventListener('change', function() {
            const target = document.querySelector(this.dataset.target);
            if (!target) return;
            const file = this.files[0];
            target.textContent = file ? file.name : '';
        });
    });

    // confirmation for delete actions
    document.querySelectorAll('.confirm-delete').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure?')) e.preventDefault();
        });
    });
</script>

<script>
    // Dynamic Position Loading for EDIT PROFILE MODAL
    document.getElementById('editDepartmentSelect')?.addEventListener('change', function() {
        const deptId = this.value;
        const posSelect = document.getElementById('editPositionSelect');
        const url = "{{ url('/employees/departments') }}/" + deptId + "/positions";

        posSelect.innerHTML = '<option value="">Loading...</option>';

        if (!deptId) {
            posSelect.innerHTML = '<option value="">-- Select position --</option>';
            return;
        }

        fetch(url)
            .then(res => res.json())
            .then(list => {
                posSelect.innerHTML = '<option value="">-- Select position --</option>';
                list.forEach(pos => {
                    posSelect.innerHTML += `<option value="${pos.id}">${pos.title}</option>`;
                });
            })
            .catch(() => {
                posSelect.innerHTML = '<option value="">-- Select position --</option>';
            });
    });
</script>

<script>
    // Permanent ID live checker
    (function() {
        const input = document.getElementById('employee_id_permanent');
        const hint = document.getElementById('permanentIdHint');
        if (!input || !hint) return;

        const url = @json(route('employees.staff.checkPermanentId'));
        const ignoreId = @json($employee->id ?? null);
        let t = null;

        function setHint(text, type) {
            hint.textContent = text || '';
            hint.className = 'ms-2 small ' + (type === 'danger' ? 'text-danger' :
                type === 'success' ? 'text-success' : 'text-muted');
        }

        async function check() {
            const value = (input.value || '').trim();
            if (!value) {
                setHint('', 'muted');
                return;
            }

            setHint('Checking...', 'muted');

            const params = new URLSearchParams({
                value
            });
            if (ignoreId) params.set('ignore_id', ignoreId);

            try {
                const res = await fetch(url + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                setHint(data.message || (data.exists ? 'ID already exists.' : 'ID is available.'), data.exists ?
                    'danger' : 'success');
            } catch (e) {
                setHint('Unable to check right now.', 'danger');
            }
        }

        input.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(check, 350);
        });

        check();
    })();
</script>

{{-- CropperJS script stays here (ONLY ONCE) --}}
<script>
    (function() {
        const fileInput = document.getElementById('profile_picture');
        const preview = document.getElementById('profilePreview');
        const hidden = document.getElementById('profile_picture_cropped');
        const removeCheck = document.getElementById('remove_profile_picture');

        const cropperModalEl = document.getElementById('cropperModal');
        const cropperImg = document.getElementById('cropperImage');

        const btnApply = document.getElementById('applyCrop');
        const zoomIn = document.getElementById('zoomIn');
        const zoomOut = document.getElementById('zoomOut');
        const rotateLeft = document.getElementById('rotateLeft');
        const resetCrop = document.getElementById('resetCrop');

        if (!fileInput || !preview || !hidden || !cropperModalEl || !cropperImg) return;

        let cropper = null;
        const cropModal = new bootstrap.Modal(cropperModalEl);

        if (removeCheck) {
            removeCheck.addEventListener('change', () => {
                if (removeCheck.checked) hidden.value = '';
            });
        }

        const editModalEl = document.getElementById('editProfileModal');
        const editModal = editModalEl ?
            (bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl)) :
            null;

        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;

            if (removeCheck) removeCheck.checked = false;

            if (file.size > 2 * 1024 * 1024) {
                alert('Image too large. Max 2MB.');
                fileInput.value = '';
                return;
            }

            cropperImg.src = URL.createObjectURL(file);

            if (editModalEl && editModal) {
                editModalEl.addEventListener('hidden.bs.modal', function handler() {
                    editModalEl.removeEventListener('hidden.bs.modal', handler);
                    cropModal.show();
                });
                editModal.hide();
            } else {
                cropModal.show();
            }
        });

        cropperModalEl.addEventListener('shown.bs.modal', () => {
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropperImg, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                responsive: true,
                background: false,
                zoomOnWheel: true
            });
        });

        cropperModalEl.addEventListener('hidden.bs.modal', () => {
            try {
                URL.revokeObjectURL(cropperImg.src);
            } catch (e) {}
        });

        zoomIn?.addEventListener('click', () => cropper?.zoom(0.1));
        zoomOut?.addEventListener('click', () => cropper?.zoom(-0.1));
        rotateLeft?.addEventListener('click', () => cropper?.rotate(-90));
        resetCrop?.addEventListener('click', () => cropper?.reset());

        btnApply?.addEventListener('click', () => {
            if (!cropper) return;

            const canvas = cropper.getCroppedCanvas({
                width: 600,
                height: 600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

            preview.src = dataUrl;
            hidden.value = dataUrl;

            cropModal.hide();
            fileInput.value = '';

            if (editModalEl && editModal) {
                cropperModalEl.addEventListener('hidden.bs.modal', function handler() {
                    cropperModalEl.removeEventListener('hidden.bs.modal', handler);
                    editModal.show();
                });
            }
        });
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        /*
         * Unified Violation Modal Handler
         * Handles:
         * 1. Add modal: #addHistoryModal
         * 2. Edit modals: .violation-edit-modal
         */

        document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target]').forEach(function(button) {
            const targetSelector = button.getAttribute('data-bs-target');

            if (!targetSelector || !document.querySelector(targetSelector)) {
                console.warn('Missing Bootstrap modal target:', targetSelector, button);
            }
        });

        function safeQuery(parent, selectors) {
            if (!parent) {
                return null;
            }

            for (const selector of selectors) {
                if (!selector || typeof selector !== 'string' || selector.trim() === '') {
                    continue;
                }

                const element = parent.querySelector(selector);

                if (element) {
                    return element;
                }
            }

            return null;
        }

        function initializeViolationModal(modalEl, config = {}) {
            if (!modalEl || modalEl.dataset.violationModalInit === '1') {
                return;
            }

            modalEl.dataset.violationModalInit = '1';

            const addButton = safeQuery(modalEl, [
                config.addButtonSelector,
                '.addViolationBtn',
                '#addViolationBtn'
            ]);

            const targetSelector = addButton?.dataset?.target;

            const container = safeQuery(modalEl, [
                targetSelector,
                config.containerSelector,
                '.violation-fields',
                '#violationsContainer'
            ]);

            const sdaCheckbox = safeQuery(modalEl, [
                config.sdaCheckboxSelector,
                '.js-action-sda',
                '#actionSDA'
            ]);

            const suspensionCheckbox = safeQuery(modalEl, [
                config.suspensionCheckboxSelector,
                '.js-action-suspension',
                '#actionSuspension'
            ]);

            const sdaWrapper = safeQuery(modalEl, [
                config.sdaWrapperSelector,
                '.sda-fields-wrapper',
                '#sdaFieldsWrapper'
            ]);

            const suspensionWrapper = safeQuery(modalEl, [
                config.suspensionWrapperSelector,
                '.suspension-dates-wrapper',
                '#suspensionDatesWrapper'
            ]);

            const suspensionStartDate = safeQuery(modalEl, [
                config.suspensionStartSelector,
                '[name="suspension_start_date"]',
                '#suspensionStartDate'
            ]);

            const suspensionEndDate = safeQuery(modalEl, [
                config.suspensionEndSelector,
                '[name="suspension_end_date"]',
                '#suspensionEndDate'
            ]);

            function getViolationTitle(row) {
                return safeQuery(row, [
                    '.violation-title',
                    '.violation-number',
                    'h6'
                ]);
            }

            function updateViolationNumbers() {
                if (!container) {
                    return;
                }

                const rows = container.querySelectorAll('.violation-row');

                rows.forEach(function(row, index) {
                    const title = getViolationTitle(row);
                    const removeButton = row.querySelector('.removeViolation');

                    if (title) {
                        title.textContent = 'Violation #' + (index + 1);
                    }

                    if (removeButton) {
                        removeButton.classList.toggle('d-none', index === 0);
                    }
                });
            }

            function resetViolationRow(row) {
                row.querySelectorAll('select').forEach(function(select) {
                    select.selectedIndex = 0;
                    select.value = '';
                });

                row.querySelectorAll('textarea').forEach(function(textarea) {
                    textarea.value = '';
                });

                row.querySelectorAll('input').forEach(function(input) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                        return;
                    }

                    input.value = '';
                });
            }

            function toggleSdaFields() {
                if (!sdaCheckbox || !sdaWrapper) {
                    return;
                }

                const isChecked = sdaCheckbox.checked;

                sdaWrapper.classList.toggle('d-none', !isChecked);

                sdaWrapper.querySelectorAll('input, select, textarea').forEach(function(input) {
                    input.disabled = !isChecked;

                    if (!isChecked) {
                        input.value = '';
                    }
                });
            }

            function toggleSuspensionFields() {
                if (!suspensionCheckbox || !suspensionWrapper) {
                    return;
                }

                const isChecked = suspensionCheckbox.checked;

                suspensionWrapper.classList.toggle('d-none', !isChecked);

                suspensionWrapper.querySelectorAll('input, select, textarea').forEach(function(input) {
                    input.disabled = !isChecked;

                    if (!isChecked) {
                        input.value = '';
                    }
                });

                if (suspensionStartDate) {
                    suspensionStartDate.required = isChecked;

                    if (!isChecked) {
                        suspensionStartDate.removeAttribute('required');
                    }
                }

                if (suspensionEndDate && !isChecked) {
                    suspensionEndDate.removeAttribute('required');
                }
            }

            addButton?.addEventListener('click', function() {
                if (!container) {
                    console.warn('Violation container not found.');
                    return;
                }

                const firstRow = container.querySelector('.violation-row');

                if (!firstRow) {
                    console.warn('No violation row found.');
                    return;
                }

                const clone = firstRow.cloneNode(true);

                resetViolationRow(clone);

                const removeButton = clone.querySelector('.removeViolation');

                if (removeButton) {
                    removeButton.classList.remove('d-none');
                }

                container.appendChild(clone);
                updateViolationNumbers();
            });

            container?.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.removeViolation');

                if (!removeButton) {
                    return;
                }

                const rows = container.querySelectorAll('.violation-row');

                if (rows.length <= 1) {
                    return;
                }

                removeButton.closest('.violation-row').remove();
                updateViolationNumbers();
            });

            modalEl.addEventListener('change', function(event) {
                if (!event.target.classList.contains('offenseSelect')) {
                    return;
                }

                const option = event.target.options[event.target.selectedIndex];
                const row = event.target.closest('.violation-row');
                const textarea = row?.querySelector('.offenseDescription');

                if (textarea) {
                    textarea.value = option?.dataset?.description || '';
                }
            });

            sdaCheckbox?.addEventListener('change', toggleSdaFields);
            suspensionCheckbox?.addEventListener('change', toggleSuspensionFields);

            updateViolationNumbers();
            toggleSdaFields();
            toggleSuspensionFields();
        }

        initializeViolationModal(document.getElementById('addHistoryModal'), {
            containerSelector: '#violationsContainer',
            addButtonSelector: '#addViolationBtn',
            sdaCheckboxSelector: '#actionSDA',
            suspensionCheckboxSelector: '#actionSuspension',
            sdaWrapperSelector: '#sdaFieldsWrapper',
            suspensionWrapperSelector: '#suspensionDatesWrapper',
            suspensionStartSelector: '#suspensionStartDate',
            suspensionEndSelector: '#suspensionEndDate'
        });

        document.querySelectorAll('.violation-edit-modal').forEach(function(modalEl) {
            initializeViolationModal(modalEl);
        });
    });
</script>
