@extends('layouts.app')
@section('title', 'Dashboard | Falcon')

@section('content')
    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">
            <div class="row g-3 mb-3">
                <div class="col-xxl-6">
                    <div class="row g-0 h-100">
                        <div class="col-12 mb-3">
                            <div class="card bg-body-tertiary dark__bg-opacity-50 shadow-none">
                                <div class="bg-holder bg-card d-none d-sm-block"
                                    style="background-image:url(../assets/img/illustrations/ticket-bg.png);">
                                </div>

                                <div class="d-flex align-items-center z-1 p-0">
                                    <img src="../assets/img/illustrations/ticket-welcome.png" alt=""
                                        width="96" />
                                    <div class="ms-n3">
                                        <h6 class="mb-1 text-primary">Welcome to</h6>
                                        <h4 class="mb-0 text-primary fw-bold">IT Department
                                            <span class="text-info fw-medium">Dashboard</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 🔹 Job Orders Summary Card --}}
                        <div class="row g-1">
                            <div class="col-12">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-md-flex justify-content-between border-bottom border-200 py-3 py-md-2">
                                        <h6 class="mb-2 mb-md-0 py-md-2">Job Orders Summary</h6>
                                        <div class="row g-md-0">
                                            <div class="col-auto d-md-flex">
                                                <div class="d-flex align-items-center me-md-3 form-check mb-0">
                                                    <input
                                                        class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer"
                                                        type="checkbox" id="pendingTickets" checked />
                                                    <label
                                                        class="form-check-label lh-base mb-0 fs-11 text-500 fw-semi-bold font-base cursor-pointer"
                                                        for="pendingTickets">Pending</label>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center me-md-3 form-check mb-0 mt-n1 mt-md-0">
                                                    <input
                                                        class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle open-tickets cursor-pointer"
                                                        type="checkbox" id="inProcessTickets" checked />
                                                    <label
                                                        class="form-check-label lh-base mb-0 fs-11 text-500 fw-semi-bold font-base cursor-pointer"
                                                        for="inProcessTickets">In Process</label>
                                                </div>
                                            </div>

                                            <div class="col-auto d-md-flex">
                                                <div class="d-flex align-items-center me-md-3 form-check mb-0">
                                                    <input
                                                        class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle due-tickets cursor-pointer"
                                                        type="checkbox" id="dueTickets" checked />
                                                    <label
                                                        class="form-check-label lh-base mb-0 fs-11 text-500 fw-semi-bold font-base cursor-pointer"
                                                        for="dueTickets">Due (2+ days old)</label>
                                                </div>
                                                <div class="d-flex align-items-center form-check mb-0 mt-n1 mt-md-0">
                                                    <input
                                                        class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle unassigned-tickets cursor-pointer"
                                                        type="checkbox" id="unassignedTickets" checked />
                                                    <label
                                                        class="form-check-label lh-base mb-0 fs-11 text-500 fw-semi-bold font-base cursor-pointer"
                                                        for="unassignedTickets">Unassigned</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 🔹 Job Order Count Stats --}}
                                    <div class="card-body scrollbar overflow-y-hidden">
                                        <div class="d-flex">
                                            {{-- ✅ Pending --}}
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="fs-9 d-flex align-items-center text-700 mb-1">
                                                        {{ $pending }}
                                                        <small class="badge text-success bg-transparent px-0">
                                                            <span class="fas fa-caret-up fs-11 ms-2 me-1"></span>
                                                            <span>{{ $percentPending }}</span>
                                                        </small>
                                                    </h6>
                                                    <h6 class="text-600 mb-0 fs-11 text-nowrap">Pending Job Orders</h6>
                                                </div>
                                                <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                                            </div>

                                            {{-- ✅ In Process --}}
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="fs-9 d-flex align-items-center text-700 mb-1">
                                                        {{ $inProcess }}
                                                        <small class="badge px-0 text-primary">
                                                            <span class="fas fa-caret-up fs-11 ms-2 me-1"></span>
                                                            <span>{{ $percentInProcess }}</span>
                                                        </small>
                                                    </h6>
                                                    <h6 class="fs-11 text-600 mb-0 text-nowrap">In Process Job Orders</h6>
                                                </div>
                                                <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                                            </div>

                                            {{-- ✅ Due --}}
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="fs-9 d-flex align-items-center text-700 mb-1">
                                                        {{ $due }}
                                                        <small class="badge px-0 text-warning">
                                                            <span class="fas fa-caret-down fs-11 ms-2 me-1"></span>
                                                            <span>{{ $percentDue }}</span>
                                                        </small>
                                                    </h6>
                                                    <h6 class="fs-11 text-600 mb-0 text-nowrap">Due (2+ Days Old)</h6>
                                                </div>
                                                <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                                            </div>

                                            {{-- ✅ Unassigned --}}
                                            <div>
                                                <h6 class="fs-9 d-flex align-items-center text-700 mb-1">
                                                    {{ $unassigned }}
                                                    <small class="badge px-0 text-danger">
                                                        <span class="fas fa-caret-up fs-11 ms-2 me-1"></span>
                                                        <span>{{ $percentUnassigned }}</span>
                                                    </small>
                                                </h6>
                                                <h6 class="fs-11 text-600 mb-0 text-nowrap">Unassigned Job Orders</h6>
                                            </div>
                                        </div>

                                        {{-- 🔹 Chart Container --}}
                                        <div class="echart-number-of-tickets" style="height: 300px;"
                                            data-echart-responsive="true"></div>
                                    </div>

                                    <div class="card-footer bg-body-tertiary py-2">
                                        <div class="row g-2 flex-between-center">
                                            <div class="col-auto">
                                                <select class="form-select form-select-sm">
                                                    <option>January</option>
                                                    <option>February</option>
                                                    <option selected="selected">March</option>
                                                    <option>April</option>
                                                    <option>May</option>
                                                    <option>June</option>
                                                    <option>July</option>
                                                    <option>August</option>
                                                    <option>September</option>
                                                    <option>October</option>
                                                    <option>November</option>
                                                    <option>December</option>
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <a class="btn btn-link btn-sm px-0" href="#!">
                                                    View all reports
                                                    <span class="fas fa-chevron-right ms-1 fs-11"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <footer class="footer">
                <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">Thank you for creating with Falcon <span
                                class="d-none d-sm-inline-block">|</span><br class="d-sm-none" /> 2024 &copy; <a
                                href="https://themewagon.com">Themewagon</a></p>
                    </div>
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">v3.23.0</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chartEl = document.querySelector('.echart-number-of-tickets');
            if (!chartEl) return;

            const chart = echarts.init(chartEl);

            const option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['Pending', 'In Process', 'Due (2+ days old)', 'Unassigned'],
                    top: 10,
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: @json($days),
                    axisLabel: {
                        color: '#888'
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        color: '#888'
                    }
                },
                series: [{
                        name: 'Pending',
                        type: 'bar',
                        data: @json($chartData['Pending']),
                        color: '#2c7be5'
                    },
                    {
                        name: 'In Process',
                        type: 'bar',
                        data: @json($chartData['In Process']),
                        color: '#5bc0de'
                    },
                    {
                        name: 'Due (2+ days old)',
                        type: 'bar',
                        data: @json($chartData['Due']),
                        color: '#f0ad4e'
                    },
                    {
                        name: 'Unassigned',
                        type: 'bar',
                        data: @json($chartData['Unassigned']),
                        color: '#d9534f'
                    }
                ]
            };

            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        });
    </script>
@endpush
