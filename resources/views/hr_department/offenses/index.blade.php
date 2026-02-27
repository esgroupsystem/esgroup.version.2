@extends('layouts.app')
@section('title', 'HR Offenses')

@section('content')
    <div class="container" data-layout="container">
        <script>
            (function() {
                const isFluid = JSON.parse(localStorage.getItem('isFluid') || 'false');
                if (!isFluid) return;
                const container = document.querySelector('[data-layout]');
                if (!container) return;
                container.classList.remove('container');
                container.classList.add('container-fluid');
            })();
        </script>

        <div class="content">

            {{-- HEADER CARD --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="mb-1">HR Offense Management</h4>
                    <p class="text-muted mb-0">Create and manage company offense policies.</p>
                </div>
            </div>

            {{-- CREATE FORM (same style as old cards) --}}
            <div class="card jo-card shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div>
                        <h6 class="mb-0">Create Offense</h6>
                        <small class="text-muted">Fill out the details below then click save.</small>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('violation.offenses.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label mb-1">Section</label>
                                <input type="text" name="section" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Offense Type</label>
                                <select name="offense_type" class="form-select form-select-sm" required>
                                    <option value="">Select Type</option>
                                    <option>A</option>
                                    <option>B</option>
                                    <option>C</option>
                                    <option>D</option>
                                    <option>E</option>
                                    <option>F</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Offense Gravity</label>
                                <select name="offense_gravity" class="form-select form-select-sm" required>
                                    <option value="">Select Gravity</option>
                                    <option>CAPITAL</option>
                                    <option>GRAVE</option>
                                    <option>SEVERE</option>
                                    <option>MINOR</option>
                                    <option>LIGHT</option>
                                    <option>SERIOUS</option>
                                    <option>FALSE</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label mb-1">Offense Description</label>
                                <textarea name="offense_description" class="form-control form-control-sm" rows="3" required></textarea>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-primary btn-sm">Save Offense</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE CARD (same as old table card) --}}
            <div class="card jo-card shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div>
                        <h5 class="mb-0">Offense List</h5>
                        <small class="text-muted">Shows all encoded offense policies.</small>
                    </div>
                </div>

                <div class="table-responsive scrollbar jo-table-wrap">
                    <table class="table table-sm table-hover mb-0 fs-10 align-middle jo-table">
                        <thead class="bg-body-tertiary border-bottom border-200">
                            <tr>
                                <th class="ps-3" style="width:120px;">Section</th>
                                <th>Description</th>
                                <th style="width:90px;">Type</th>
                                <th style="width:140px;">Gravity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($offenses as $offense)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $offense->section }}</td>
                                    <td style="white-space: normal;">{{ $offense->offense_description }}</td>
                                    <td>{{ $offense->offense_type }}</td>
                                    <td>{{ $offense->offense_gravity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <div class="empty-state py-4">
                                            <div class="fw-bold">No Records Found</div>
                                            <div class="text-muted fs-11">No offenses yet.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION FOOTER (same as old) --}}
                @if ($offenses->hasPages())
                    <div class="card-footer bg-body-tertiary border-top border-200">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">

                            {{-- ✅ IMPORTANT --}}
                            {{-- If your pagination.custom ALREADY prints "Showing x to y of z results",
                                 delete this <small> to avoid duplicate text --}}
                            <small class="text-muted">
                                Showing {{ $offenses->firstItem() ?? 0 }} to {{ $offenses->lastItem() ?? 0 }} of
                                {{ $offenses->total() }}
                            </small>

                            <div class="ms-md-auto">
                                {{ $offenses->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
