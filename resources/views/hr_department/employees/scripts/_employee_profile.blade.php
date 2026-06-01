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
        function initModal(modalEl) {
            const container = modalEl.querySelector('.violation-fields');
            const addBtn = modalEl.querySelector('.addViolationBtn');
            const sdaCheckbox = modalEl.querySelector('.js-action-sda');
            const sdaWrapper = modalEl.querySelector('.sda-fields-wrapper');
            const suspCheckbox = modalEl.querySelector('.js-action-suspension');
            const suspWrapper = modalEl.querySelector('.suspension-dates-wrapper');

            // Add new violation row
            addBtn?.addEventListener('click', function() {
                const firstRow = container.querySelector('.violation-row');
                const clone = firstRow.cloneNode(true);
                clone.querySelectorAll('select, textarea, input').forEach(i => i.value = '');
                clone.querySelector('.removeViolation').classList.remove('d-none');
                container.appendChild(clone);
                updateNumbers();
            });

            // Remove violation row
            modalEl.addEventListener('click', function(e) {
                if (!e.target.classList.contains('removeViolation')) return;
                if (container.querySelectorAll('.violation-row').length <= 1) return;
                e.target.closest('.violation-row').remove();
                updateNumbers();
            });

            function updateNumbers() {
                container.querySelectorAll('.violation-row').forEach((row, i) => {
                    row.querySelector('.violation-number').textContent = 'Violation #' + (i + 1);
                    row.querySelector('.removeViolation').classList.toggle('d-none', i === 0);
                });
            }

            // SDA toggle
            sdaCheckbox?.addEventListener('change', () => {
                if (!sdaWrapper) return;
                sdaWrapper.classList.toggle('d-none', !sdaCheckbox.checked);
            });

            // Suspension toggle
            suspCheckbox?.addEventListener('change', () => {
                if (!suspWrapper) return;
                suspWrapper.classList.toggle('d-none', !suspCheckbox.checked);
            });

            // Auto-fill description from offense select
            modalEl.addEventListener('change', e => {
                if (!e.target.classList.contains('offenseSelect')) return;
                const desc = e.target.options[e.target.selectedIndex].dataset.description || '';
                const textarea = e.target.closest('.violation-row').querySelector(
                    '.offenseDescription');
                if (textarea) textarea.value = desc;
            });
        }

        document.querySelectorAll('.violation-edit-modal').forEach(initModal);
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target]').forEach(function(button) {
            const targetSelector = button.getAttribute('data-bs-target');

            if (!targetSelector || !document.querySelector(targetSelector)) {
                console.warn('Missing Bootstrap modal target:', targetSelector, button);
            }
        });

        document.querySelectorAll('.violation-edit-modal').forEach(function(modalEl) {
            initializeViolationModal(modalEl);
        });

        document.addEventListener('change', function(event) {
            if (!event.target.classList.contains('offenseSelect')) {
                return;
            }

            const option = event.target.options[event.target.selectedIndex];
            const row = event.target.closest('.violation-row');

            if (!row) {
                return;
            }

            const textarea = row.querySelector('.offenseDescription');

            if (textarea) {
                textarea.value = option.dataset.description || '';
            }
        });

        function initializeViolationModal(modalEl) {
            const sdaCheckbox = modalEl.querySelector('.js-action-sda');
            const suspensionCheckbox = modalEl.querySelector('.js-action-suspension');

            const sdaWrapper = modalEl.querySelector('.sda-fields-wrapper');
            const suspensionWrapper = modalEl.querySelector('.suspension-dates-wrapper');

            const addButton = modalEl.querySelector('.addViolationBtn');

            function toggleSdaFields() {
                if (!sdaCheckbox || !sdaWrapper) {
                    return;
                }

                if (sdaCheckbox.checked) {
                    sdaWrapper.classList.remove('d-none');
                    sdaWrapper.querySelectorAll('input').forEach(function(input) {
                        input.disabled = false;
                    });
                    return;
                }

                sdaWrapper.classList.add('d-none');

                sdaWrapper.querySelectorAll('input').forEach(function(input) {
                    input.value = '';
                    input.disabled = true;
                });
            }

            function toggleSuspensionFields() {
                if (!suspensionCheckbox || !suspensionWrapper) {
                    return;
                }

                if (suspensionCheckbox.checked) {
                    suspensionWrapper.classList.remove('d-none');
                    suspensionWrapper.querySelectorAll('input').forEach(function(input) {
                        input.disabled = false;
                    });
                    return;
                }

                suspensionWrapper.classList.add('d-none');

                suspensionWrapper.querySelectorAll('input').forEach(function(input) {
                    input.value = '';
                    input.disabled = true;
                });
            }

            function updateViolationNumbers(container) {
                const rows = container.querySelectorAll('.violation-row');

                rows.forEach(function(row, index) {
                    const title = row.querySelector('.violation-number');
                    const removeButton = row.querySelector('.removeViolation');

                    if (title) {
                        title.textContent = 'Violation #' + (index + 1);
                    }

                    if (removeButton) {
                        removeButton.classList.toggle('d-none', index === 0);
                    }
                });
            }

            addButton?.addEventListener('click', function() {
                const targetSelector = addButton.dataset.target;
                const container = modalEl.querySelector(targetSelector);

                if (!container) {
                    console.warn('Violation container not found:', targetSelector);
                    return;
                }

                const firstRow = container.querySelector('.violation-row');

                if (!firstRow) {
                    return;
                }

                const clone = firstRow.cloneNode(true);

                clone.querySelectorAll('select').forEach(function(select) {
                    select.value = '';
                });

                clone.querySelectorAll('textarea').forEach(function(textarea) {
                    textarea.value = '';
                });

                clone.querySelectorAll('input').forEach(function(input) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });

                container.appendChild(clone);
                updateViolationNumbers(container);
            });

            modalEl.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.removeViolation');

                if (!removeButton) {
                    return;
                }

                const container = removeButton.closest('.violation-fields');

                if (!container) {
                    return;
                }

                const rows = container.querySelectorAll('.violation-row');

                if (rows.length <= 1) {
                    return;
                }

                removeButton.closest('.violation-row').remove();
                updateViolationNumbers(container);
            });

            sdaCheckbox?.addEventListener('change', toggleSdaFields);
            suspensionCheckbox?.addEventListener('change', toggleSuspensionFields);

            toggleSdaFields();
            toggleSuspensionFields();

            const container = modalEl.querySelector('.violation-fields');

            if (container) {
                updateViolationNumbers(container);
            }
        }
    });
</script>
