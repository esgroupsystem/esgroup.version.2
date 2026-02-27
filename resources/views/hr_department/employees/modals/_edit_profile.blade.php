<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('employees.update', $employee->id) }}"
              method="POST"
              class="modal-content"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Employee Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    {{-- Permanent ID --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Permanent Employee ID
                            <small id="permanentIdHint" class="ms-2"></small>
                        </label>

                        <input type="text"
                               name="employee_id_permanent"
                               id="employee_id_permanent"
                               class="form-control"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               value="{{ old('employee_id_permanent', $employee->employee_id_permanent ?? '') }}">
                    </div>

                    {{-- Full Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text"
                               name="full_name"
                               class="form-control"
                               value="{{ old('full_name', $employee->full_name) }}">
                    </div>

                    {{-- Date of Birth --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date of Birth</label>
                        <input type="date"
                               name="date_of_birth"
                               class="form-control"
                               value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d') ?? '') }}">
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-control">
                            @php
                                $currentStatus = old('status', $employee->status ?? 'Active');
                                $statuses = [
                                    'Active',
                                    'Active(Re-Entry)',
                                    'Suspended',
                                    'Terminated',
                                    'Terminated(due to AWOL)',
                                    'Retrench',
                                    'End of Contract',
                                    'Retired',
                                    'Resigned',
                                ];
                            @endphp

                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" @selected($currentStatus === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Hired --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date Hired</label>
                        <input type="date"
                               name="date_hired"
                               class="form-control"
                               value="{{ old('date_hired', optional($employee->date_hired)->format('Y-m-d') ?? '') }}">
                    </div>

                    {{-- Company --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Company</label>
                        @php $companyVal = old('company', $employee->company); @endphp
                        <select name="company" class="form-control" required>
                            <option value="">-- Select Company --</option>
                            <option value="Jell Transport" @selected($companyVal === 'Jell Transport')>Jell Transport</option>
                            <option value="ES Transport" @selected($companyVal === 'ES Transport')>ES Transport</option>
                            <option value="Kellen Transport" @selected($companyVal === 'Kellen Transport')>Kellen Transport</option>
                            <option value="Earthstar Transport" @selected($companyVal === 'Earthstar Transport')>Earthstar Transport</option>
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Department</label>
                        <select name="department_id" id="editDepartmentSelect" class="form-control">
                            @if ($employee->department)
                                <option value="{{ $employee->department->id }}" selected>
                                    {{ $employee->department->name }} (Current)
                                </option>
                            @else
                                <option value="">-- Select department --</option>
                            @endif

                            @foreach ($departments as $dept)
                                @if ($dept->id != $employee->department_id)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Position --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Position</label>
                        <select name="position_id" id="editPositionSelect" class="form-control">
                            @if ($employee->position)
                                <option value="{{ $employee->position->id }}" selected>
                                    {{ $employee->position->title }} (Current)
                                </option>
                            @else
                                <option value="">-- Select position --</option>
                            @endif

                            @foreach ($employee->department?->positions ?? [] as $pos)
                                @if ($employee->position_id != $pos->id)
                                    <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Garage --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Garage</label>
                        @php $garageVal = old('garage', $employee->garage); @endphp
                        <select name="garage" class="form-control" required>
                            <option value="Mirasol" @selected($garageVal === 'Mirasol')>Mirasol</option>
                            <option value="Balintawak" @selected($garageVal === 'Balintawak')>Balintawak</option>
                            <option value="Gonzales" @selected($garageVal === 'Gonzales')>Gonzales</option>
                        </select>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $employee->email) }}">
                    </div>

                    {{-- Phone Number --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text"
                               name="phone_number"
                               class="form-control"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                               value="{{ old('phone_number', $employee->phone_number) }}">
                    </div>

                    {{-- Address 1 --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Address 1</label>
                        <input type="text"
                               name="address_1"
                               class="form-control"
                               value="{{ old('address_1', $employee->address_1) }}">
                    </div>

                    {{-- Address 2 --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Address 2</label>
                        <input type="text"
                               name="address_2"
                               class="form-control"
                               value="{{ old('address_2', $employee->address_2) }}">
                    </div>

                    {{-- Emergency Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Emergency Contact Name</label>
                        <input type="text"
                               name="emergency_name"
                               class="form-control"
                               value="{{ old('emergency_name', $employee->emergency_name) }}">
                    </div>

                    {{-- Emergency Contact --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Emergency Contact Number</label>
                        <input type="text"
                               name="emergency_contact"
                               class="form-control"
                               inputmode="numeric"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"
                               value="{{ old('emergency_contact', $employee->emergency_contact) }}">
                    </div>

                    {{-- PROFILE PICTURE WITH CROPPER --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Profile Picture</label>

                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <img id="profilePreview"
                                 src="{{ $profilePath }}"
                                 alt="Preview"
                                 class="profile-preview-circle">

                            <div class="d-flex flex-column gap-2">
                                {{-- IMPORTANT: no "name" here (cropper uses hidden base64) --}}
                                <input type="file"
                                       id="profile_picture"
                                       class="form-control"
                                       accept="image/*">

                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           value="1"
                                           id="remove_profile_picture"
                                           name="remove_profile_picture">
                                    <label class="form-check-label" for="remove_profile_picture">
                                        Remove profile picture
                                    </label>
                                </div>

                                <small class="text-muted">Upload then crop (drag + zoom). JPG/PNG max 2MB.</small>
                            </div>
                        </div>

                        <input type="hidden"
                               name="profile_picture_cropped"
                               id="profile_picture_cropped">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save Changes</button>
            </div>

        </form>
    </div>
</div>