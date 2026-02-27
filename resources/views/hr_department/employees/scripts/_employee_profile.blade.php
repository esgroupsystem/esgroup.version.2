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
