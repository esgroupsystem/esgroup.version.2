@extends('layouts.app')
@section('title', 'Project Management Dashboard')
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
                <div class="col-xxl-6 col-lg-12">
                    <div class="card h-100">
                        <div class="bg-holder bg-card"
                            style="
                            background-image: url(../assets/img/icons/spot-illustrations/corner-3.png);
                        ">
                        </div>
                        <!--/.bg-holder-->

                        <div class="card-header z-1">
                            <h5 class="text-primary">Welcome to Falcon!</h5>
                            <h6 class="text-600">
                                Here are some quick links for you to start
                            </h6>
                        </div>
                        <div class="card-body z-1">
                            <div class="row g-2 h-100 align-items-end">
                                <div class="col-sm-6 col-md-5">
                                    <div class="d-flex position-relative">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-chess-rook text-primary"></span>
                                        </div>
                                        <div class="flex-1">
                                            <a class="stretched-link text-800" href="#!">
                                                <h6 class="mb-0">General</h6>
                                            </a>
                                            <p class="mb-0 fs-11 text-500">
                                                Customize with a few clicks
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-5">
                                    <div class="d-flex position-relative">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-crown text-warning"></span>
                                        </div>
                                        <div class="flex-1">
                                            <a class="stretched-link text-800" href="#!">
                                                <h6 class="mb-0">Upgrade to pro</h6>
                                            </a>
                                            <p class="mb-0 fs-11 text-500">
                                                Try Pro for 14 days free!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-5">
                                    <div class="d-flex position-relative">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-video text-success"></span>
                                        </div>
                                        <div class="flex-1">
                                            <a class="stretched-link text-800" href="#!">
                                                <h6 class="mb-0">
                                                    Create a meeting
                                                </h6>
                                            </a>
                                            <p class="mb-0 fs-11 text-500">
                                                Manage and update your meetings
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-5">
                                    <div class="d-flex position-relative">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-user text-info"></span>
                                        </div>
                                        <div class="flex-1">
                                            <a class="stretched-link text-800" href="#!">
                                                <h6 class="mb-0">
                                                    Members activity
                                                </h6>
                                            </a>
                                            <p class="mb-0 fs-11 text-500">
                                                Monitor activity and supervise
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-between-center">
                            <h5 class="mb-0">Team Progress</h5>
                            <a class="btn btn-link btn-sm px-0" href="#!">Report<span
                                    class="fas fa-chevron-right ms-1 fs-11">
                                </span></a>
                        </div>
                        <div class="card-body">
                            <p class="fs-10 text-600">
                                See team members' time worked, <br />
                                activity levels, and progress
                            </p>
                            <div class="progress mb-3 rounded-pill" style="height: 6px" role="progressbar"
                                aria-valuenow="43.72" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-progress-gradient rounded-pill" style="width: 75%"></div>
                            </div>
                            <p class="mb-0 text-primary">75% completed</p>
                            <p class="mb-0 fs-11 text-500">Jan 1st to 30th</p>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col">
                                    <p class="mb-1 fs-11 text-500">
                                        Upcoming schedule
                                    </p>
                                    <h5 class="text-primary fs-9">
                                        Falcon discussion
                                    </h5>
                                </div>
                                <div class="col-auto">
                                    <div class="bg-primary-subtle px-3 py-3 rounded-circle text-center"
                                        style="width: 60px; height: 60px">
                                        <h5 class="text-primary mb-0 d-flex flex-column mt-n1">
                                            <span>09</span><small class="text-primary fs-11 lh-1">MAR</small>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end">
                            <div class="row g-3 justify-content-between">
                                <div class="col-10 mt-0">
                                    <p class="fs-10 text-600 mb-0">
                                        The very first general meeting for planning
                                        Falcon’s design and development roadmap
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-success w-100 fs-10" type="button">
                                        <span class="fas fa-video me-2"></span>Join
                                        meeting
                                    </button>
                                </div>
                                <div class="col-auto ps-0">
                                    <div class="avatar-group avatar-group-dense">
                                        <div class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                            <img class="rounded-circle" src="../assets/img/team/1-thumb.png"
                                                alt="" />
                                        </div>
                                        <div class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                            <img class="rounded-circle" src="../assets/img/team/2-thumb.png"
                                                alt="" />
                                        </div>
                                        <div class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                            <img class="rounded-circle" src="../assets/img/team/3-thumb.png"
                                                alt="" />
                                        </div>
                                        <div class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                            <div class="avatar-name rounded-circle">
                                                <span>+50</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-xxl-6 col-lg-12">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <div class="card overflow-hidden" style="min-width: 12rem">
                                        <div class="bg-holder bg-card"
                                            style="
                                            background-image: url(../assets/img/icons/spot-illustrations/corner-1.png);
                                        ">
                                        </div>
                                        <!--/.bg-holder-->

                                        <div class="card-body position-relative">
                                            <h6>
                                                Customers<span
                                                    class="badge badge-subtle-warning rounded-pill ms-2">-0.23%</span>
                                            </h6>
                                            <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning"
                                                data-countup='{"endValue":58.386,"decimalPlaces":2,"suffix":"k"}'>
                                                0
                                            </div>
                                            <a class="fw-semi-bold fs-10 text-nowrap"
                                                href="../app/e-commerce/customers.html">See all<span
                                                    class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card overflow-hidden" style="min-width: 12rem">
                                        <div class="bg-holder bg-card"
                                            style="
                                            background-image: url(../assets/img/icons/spot-illustrations/corner-2.png);
                                        ">
                                        </div>
                                        <!--/.bg-holder-->

                                        <div class="card-body position-relative">
                                            <h6>
                                                Orders<span class="badge badge-subtle-info rounded-pill ms-2">0.0%</span>
                                            </h6>
                                            <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info"
                                                data-countup='{"endValue":23.434,"decimalPlaces":2,"suffix":"k"}'>
                                                0
                                            </div>
                                            <a class="fw-semi-bold fs-10 text-nowrap"
                                                href="../app/e-commerce/orders/order-list.html">All orders<span
                                                    class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <div class="row flex-between-center">
                                        <div class="col-auto">
                                            <h6 class="mb-0">
                                                Report for this week
                                            </h6>
                                        </div>
                                        <div class="col-auto d-flex">
                                            <div class="btn btn-sm d-flex align-items-center p-0 me-3 shadow-none"
                                                id="echart-bar-report-for-this-week-option-1">
                                                <span class="fas fa-circle text-primary fs-11 me-1"></span><span
                                                    class="text">This Week
                                                </span>
                                            </div>
                                            <div class="btn btn-sm d-flex align-items-center p-0 shadow-none"
                                                id="echart-bar-report-for-this-week-option-2">
                                                <span class="fas fa-circle text-300 fs-11 me-1"></span><span
                                                    class="text">Last Week</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-0">
                                    <!-- Find the JS file for the following chart at: src/js/charts/echarts/report-for-this-week.js-->
                                    <!-- If you are not using gulp based workflow, you can find the transpiled code at: public/assets/js/theme.js-->
                                    <div class="echart-bar-report-for-this-week" data-echart-responsive="true"
                                        data-chart='{"option1":"echart-bar-report-for-this-week-option-1","option2":"echart-bar-report-for-this-week-option-2"}'>
                                    </div>
                                </div>
                                <div class="card-footer bg-body-tertiary p-0">
                                    <a class="btn btn-sm btn-link d-block w-100 py-2" href="#!">See all projects<span
                                            class="fas fa-chevron-right ms-1 fs-11"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-lg-6 order-xxl-1">
                    <div class="card h-100">
                        <div class="card-header bg-body-tertiary d-flex flex-between-center">
                            <h5 class="mb-0">Project Locations</h5>
                            <div class="dropdown font-sans-serif btn-reveal-trigger">
                                <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal"
                                    type="button" data-bs-toggle="dropdown" data-boundary="viewport"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-2">
                                    <a class="dropdown-item" href="#!">Edit</a><a class="dropdown-item"
                                        href="#!">Move</a><a class="dropdown-item" href="#!">Resize</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-warning" href="#!">Archive</a><a
                                        class="dropdown-item text-danger" href="#!">Delete</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body h-100 p-0">
                            <div class="h-100 bg-white" id="map" style="min-height: 300px"></div>
                        </div>
                        <div class="card-footer bg-body-tertiary">
                            <div class="row justify-content-between">
                                <div class="col-auto">
                                    <select class="form-select form-select-sm">
                                        <option value="week" selected="selected">
                                            Last 7 days
                                        </option>
                                        <option value="month">Last month</option>
                                        <option value="year">Last year</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <a class="btn btn-falcon-default btn-sm" href="#!"><span
                                            class="d-none d-sm-inline-block me-1">Location</span>overview<span
                                            class="fa fa-chevron-right ms-1 fs-10"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-between-center">
                            <h6 class="mb-0">Project Statistics</h6>
                            <div class="dropdown font-sans-serif btn-reveal-trigger">
                                <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal"
                                    type="button" id="dropdown-project-statistics" data-bs-toggle="dropdown"
                                    data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <span class="fas fa-ellipsis-h fs-11"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-2"
                                    aria-labelledby="dropdown-project-statistics">
                                    <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                        href="#!">Export</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#!">Remove</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row mb-2">
                                <div class="col-6 border-end border-200">
                                    <h4 class="mb-0">5,432</h4>
                                    <p class="fs-10 text-600 mb-0">
                                        Total Work Hours
                                    </p>
                                </div>
                                <div class="col-3 border-end text-center border-200">
                                    <h4 class="fs-9 mb-0">13</h4>
                                    <p class="fs-10 text-600 mb-0">Projects</p>
                                </div>
                                <div class="col-3 text-center">
                                    <h4 class="fs-9 mb-0">7</h4>
                                    <p class="fs-10 text-600 mb-0">Ongoing</p>
                                </div>
                            </div>
                            <div class="progress-stacked overflow-visible mt-4 rounded-3" style="height: 6px">
                                <div class="progress" role="progressbar" style="width: 25%; height: 6px"
                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    <div
                                        class="overflow-visible progress-bar bg-progress-gradient border-end border-100 border-2 rounded-end rounded-pill">
                                        <span class="mt-n4 text-900 fw-bold">25%</span>
                                    </div>
                                </div>
                                <div class="progress" role="progressbar" style="width: 45%; height: 6px"
                                    aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                                    <div class="overflow-visible progress-bar bg-secondary border-end border-100 border-2">
                                        <span class="mt-n4 text-900 fw-bold">45%</span>
                                    </div>
                                </div>
                                <div class="progress" role="progressbar" style="width: 15%; height: 6px"
                                    aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                                    <div class="overflow-visible progress-bar bg-warning border-end border-100 border-2">
                                        <span class="mt-n4 text-900 fw-bold">15%</span>
                                    </div>
                                </div>
                                <div class="progress" role="progressbar" style="width: 15%; height: 6px"
                                    aria-valuenow="15" aria-valuemin="15" aria-valuemax="100">
                                    <div class="overflow-visible progress-bar bg-info rounded-start rounded-pill">
                                        <span class="mt-n4 text-900 fw-bold">15%</span>
                                    </div>
                                </div>
                            </div>
                            <p class="fs-10 mb-2 mt-3">Assignees in Sprint</p>
                            <div class="avatar-group avatar-group-dense mb-3">
                                <div class="avatar avatar-2xl border border-3 border-200 rounded-circle">
                                    <img class="rounded-circle" src="../assets/img/team/1-thumb.png" alt="" />
                                </div>
                                <div class="avatar avatar-2xl border border-3 border-200 rounded-circle">
                                    <img class="rounded-circle" src="../assets/img/team/2-thumb.png" alt="" />
                                </div>
                                <div class="avatar avatar-2xl border border-3 border-200 rounded-circle">
                                    <img class="rounded-circle" src="../assets/img/team/3-thumb.png" alt="" />
                                </div>
                                <div class="avatar avatar-2xl border border-3 border-200 rounded-circle">
                                    <img class="rounded-circle" src="../assets/img/team/4-thumb.png" alt="" />
                                </div>
                                <div class="avatar avatar-2xl border border-3 border-200 rounded-circle">
                                    <img class="rounded-circle" src="../assets/img/team/5-thumb.png" alt="" />
                                </div>
                                <div class="avatar avatar-2xl border border-3 border-200 rounded-circle">
                                    <div class="avatar-name rounded-circle">
                                        <span>+50</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h6>Project</h6>
                                <h6>Team</h6>
                            </div>
                            <div class="d-flex flex-between-center rounded-3 bg-body-tertiary p-3 mb-2">
                                <a href="#!">
                                    <h6 class="mb-0">
                                        <span class="fas fa-circle fs-10 me-3 text-primary"></span>Organizing UI
                                    </h6>
                                </a><a class="fs-11 text-600 mb-0" href="#!">ThemeWagon</a>
                            </div>
                            <div class="d-flex flex-between-center rounded-3 bg-body-tertiary p-3 mb-2">
                                <a href="#!">
                                    <h6 class="mb-0">
                                        <span class="fas fa-circle fs-10 me-3 text-warning"></span>Create Mobile View
                                    </h6>
                                </a><a class="fs-11 text-600 mb-0" href="#!">Mailbluster</a>
                            </div>
                            <div class="d-flex flex-between-center rounded-3 bg-body-tertiary p-3 mb-2">
                                <a href="#!">
                                    <h6 class="mb-0">
                                        <span class="fas fa-circle fs-10 me-3 text-secondary"></span>Create Landing Pages
                                    </h6>
                                </a><a class="fs-11 text-600 mb-0" href="#!">BlackBOx</a>
                            </div>
                            <div class="d-flex flex-between-center rounded-3 bg-body-tertiary p-3">
                                <a href="#!">
                                    <h6 class="mb-0">
                                        <span class="fas fa-circle fs-10 me-3 text-info"></span>Front end issues
                                    </h6>
                                </a><a class="fs-11 text-600 mb-0" href="#!">Falcon Team</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-lg-6 order-xxl-1">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Recent Activity</h6>
                        </div>
                        <div class="card-body scrollbar recent-activity-body-height ps-2">
                            <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                                <div class="col-auto ps-4 ms-2">
                                    <div class="ps-2">
                                        <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                                            <span class="text-primary fas fa-envelope"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row gx-0 border-bottom pb-x1">
                                        <div class="col">
                                            <h6 class="text-800 mb-1">
                                                Antony Hopkins sent an Email
                                            </h6>
                                            <p class="fs-10 text-600 mb-0">
                                                Got an email for previous year sale
                                                report
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-11 text-500 mb-0">
                                                2m ago
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 timeline timeline-primary timeline-past pb-x1">
                                <div class="col-auto ps-4 ms-2">
                                    <div class="ps-2">
                                        <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                                            <span class="text-primary fas fa-archive"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row gx-0 border-bottom pb-x1">
                                        <div class="col">
                                            <h6 class="text-800 mb-1">
                                                Emma archived a board
                                            </h6>
                                            <p class="fs-10 text-600 mb-0">
                                                A finished project's board is
                                                archived recently
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-11 text-500 mb-0">
                                                26m ago
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 timeline timeline-primary timeline-current pb-x1">
                                <div class="col-auto ps-4 ms-2">
                                    <div class="ps-2">
                                        <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                                            <span class="text-primary fas fa-code"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row gx-0 border-bottom pb-x1">
                                        <div class="col">
                                            <h6 class="text-800 mb-1">
                                                Falcon v3.0.0 released with new
                                                features
                                            </h6>
                                            <p class="fs-10 text-600 mb-0">
                                                Falcon new version is released
                                                successfully with new Dashboards
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-11 text-500 mb-0">
                                                1h ago
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 timeline timeline-primary pb-x1">
                                <div class="col-auto ps-4 ms-2">
                                    <div class="ps-2">
                                        <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                                            <span class="text-primary fas fa-code-branch"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row gx-0 border-bottom pb-x1">
                                        <div class="col">
                                            <h6 class="text-800 mb-1">
                                                Rowan shared a link to the board
                                            </h6>
                                            <p class="fs-10 text-600 mb-0">
                                                A link is shared with attachments
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-11 text-500 mb-0">
                                                3h ago
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 timeline timeline-primary">
                                <div class="col-auto ps-4 ms-2">
                                    <div class="ps-2">
                                        <div class="icon-item icon-item-sm rounded-circle bg-200 shadow-none">
                                            <span class="text-primary far fa-file-code"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row gx-0">
                                        <div class="col">
                                            <h6 class="text-800 mb-1">
                                                Anna updated a file
                                            </h6>
                                            <p class="fs-10 text-600 mb-0">
                                                Fixed some bugs and spelling errors
                                                on this update
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <p class="fs-11 text-500 mb-0">
                                                4h ago
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-6 order-xxl-3">
                    <div class="card h-100">
                        <div class="card-header bg-body-tertiary py-2 d-flex flex-between-center">
                            <h6 class="mb-0">Members Activity</h6>
                            <div class="dropdown font-sans-serif btn-reveal-trigger">
                                <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal"
                                    type="button" id="dropdown-members-activity" data-bs-toggle="dropdown"
                                    data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <span class="fas fa-ellipsis-h fs-11"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-2"
                                    aria-labelledby="dropdown-members-activity">
                                    <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                        href="#!">Export</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#!">Remove</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary p-0">
                            <a class="btn btn-sm btn-link d-block py-2" href="#!">See all projects<span
                                    class="fas fa-chevron-right ms-1 fs-11"></span></a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8 order-xxl-2">
                    <div class="card h-100">
                        <div class="card-body p-0">
                            <div class="scrollbar">
                                <table
                                    class="table table-dashboard mb-0 table-borderless fs-10 border-200 overflow-hidden rounded-3 table-member-info">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th class="text-900">Member info</th>
                                            <th class="text-900 text-center">
                                                Attendance
                                            </th>
                                            <th class="text-900 text-center">
                                                Today
                                            </th>
                                            <th class="text-900 text-end">
                                                This Week
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-2xl status-online">
                                                        <img class="rounded-circle" src="../assets/img/team/25.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">Fionna Mayer</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            SAAS dashboard design
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <small
                                                    class="badge fw-semi-bold rounded-pill badge-subtle-danger">Absent</small>
                                            </td>
                                            <td class="align-middle text-center">
                                                <small
                                                    class="badge fw-semi-bold rounded-pill badge-subtle-primary">12%</small>
                                                <p class="fs-11 mb-0">1h:52m</p>
                                            </td>
                                            <td class="align-middle">
                                                <div class="row g-2 justify-content-end">
                                                    <div class="col-auto">
                                                        <small
                                                            class="badge fw-semi-bold rounded-pill badge-subtle-success">68%</small>
                                                        <p class="fs-11 mb-0">
                                                            1h:52m
                                                        </p>
                                                    </div>
                                                    <div class="col-auto mt-auto">
                                                        <div class="mb-1"
                                                            data-echarts='{"tooltip":{"show":false},"series":[{"data":[30,22,18,30,20,35]}]}'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary py-2">
                            <div class="row flex-between-center">
                                <div class="col-auto">
                                    <select class="form-select form-select-sm">
                                        <option>Last 7 days</option>
                                        <option>Last Month</option>
                                        <option>Last Year</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <a class="btn btn-sm btn-link px-0 fw-medium" href="#!">View All<span
                                            class="fas fa-chevron-right ms-1 fs-11"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 order-xxl-3">
                    <div class="card" id="runningProjectTable"
                        data-list='{"valueNames":["projects","worked","time","date"]}'>
                        <div class="card-header">
                            <h6 class="mb-0">Running Projects</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="scrollbar">
                                <table
                                    class="table mb-0 table-borderless fs-10 border-200 overflow-hidden table-running-project">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th class="text-800 sort" data-sort="projects">
                                                Projects
                                            </th>
                                            <th class="text-800 sort" data-sort="time">
                                                Progress
                                            </th>
                                            <th class="text-800 sort text-center" data-sort="worked">
                                                Worked
                                            </th>
                                            <th class="text-800 sort text-center" data-sort="date">
                                                Due Date
                                            </th>
                                            <th class="text-800 text-end">
                                                Members
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-xl">
                                                        <div
                                                            class="avatar-name rounded-circle text-primary bg-primary-subtle fs-9">
                                                            <span>C</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">CRM dashboard
                                                                design</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            Falcon
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress rounded-3 worked" style="height: 6px"
                                                    role="progressbar" aria-valuenow="43.72" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar bg-progress-gradient rounded-pill"
                                                        style="width: 50%"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center time">
                                                <p class="mb-0 fw-semi-bold">
                                                    12h:50m:00s
                                                </p>
                                            </td>
                                            <td class="align-middle text-center date">
                                                <p class="mb-0 fw-semi-bold">
                                                    01/02/22
                                                </p>
                                            </td>
                                            <td>
                                                <div class="avatar-group justify-content-end">
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/1-thumb.png"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/2-thumb.png"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <div class="avatar-name rounded-circle">
                                                            <span>+2</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-xl">
                                                        <div
                                                            class="avatar-name rounded-circle text-success bg-success-subtle fs-9">
                                                            <span>U</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">UI/UX Redesign</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            Themewagon
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress rounded-3 worked" style="height: 6px"
                                                    role="progressbar" aria-valuenow="43.72" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar bg-progress-gradient rounded-pill"
                                                        style="width: 70%"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center time">
                                                <p class="mb-0 fw-semi-bold">
                                                    11h:50m:00s
                                                </p>
                                            </td>
                                            <td class="align-middle text-center date">
                                                <p class="mb-0 fw-semi-bold">
                                                    04/02/22
                                                </p>
                                            </td>
                                            <td>
                                                <div class="avatar-group justify-content-end">
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/3-thumb.png"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/4-thumb.png"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <div class="avatar-name rounded-circle">
                                                            <span>+5</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-xl">
                                                        <div
                                                            class="avatar-name rounded-circle text-info bg-info-subtle fs-9">
                                                            <span>F</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">F.A.Q Section</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            Mailbluster
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress rounded-3 worked" style="height: 6px"
                                                    role="progressbar" aria-valuenow="43.72" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar bg-progress-gradient rounded-pill"
                                                        style="width: 60%"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center time">
                                                <p class="mb-0 fw-semi-bold">
                                                    3h:30m:50s
                                                </p>
                                            </td>
                                            <td class="align-middle text-center date">
                                                <p class="mb-0 fw-semi-bold">
                                                    01/02/22
                                                </p>
                                            </td>
                                            <td>
                                                <div class="avatar-group justify-content-end">
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/5-thumb.png"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/7.jpg"
                                                            alt="" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-xl">
                                                        <div
                                                            class="avatar-name rounded-circle text-warning bg-warning-subtle fs-9">
                                                            <span>D</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">Drip Campaign
                                                                Feature</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            Themewagon
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress rounded-3 worked" style="height: 6px"
                                                    role="progressbar" aria-valuenow="43.72" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar bg-progress-gradient rounded-pill"
                                                        style="width: 80%"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center time">
                                                <p class="mb-0 fw-semi-bold">
                                                    12h:20m:00s
                                                </p>
                                            </td>
                                            <td class="align-middle text-center date">
                                                <p class="mb-0 fw-semi-bold">
                                                    22/03/22
                                                </p>
                                            </td>
                                            <td>
                                                <div class="avatar-group justify-content-end">
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/7.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/10.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <div class="avatar-name rounded-circle">
                                                            <span>+2</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-xl">
                                                        <div
                                                            class="avatar-name rounded-circle text-danger bg-danger-subtle fs-9">
                                                            <span>S</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">Studio Recording</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            Mailbluster Marketing
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress rounded-3 worked" style="height: 6px"
                                                    role="progressbar" aria-valuenow="43.72" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar bg-progress-gradient rounded-pill"
                                                        style="width: 40%"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center time">
                                                <p class="mb-0 fw-semi-bold">
                                                    11h:10m:00s
                                                </p>
                                            </td>
                                            <td class="align-middle text-center date">
                                                <p class="mb-0 fw-semi-bold">
                                                    28/02/22
                                                </p>
                                            </td>
                                            <td>
                                                <div class="avatar-group justify-content-end">
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/12.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/13.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/5.jpg"
                                                            alt="" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center position-relative">
                                                    <div class="avatar avatar-xl">
                                                        <div
                                                            class="avatar-name rounded-circle text-success bg-success-subtle fs-9">
                                                            <span>P</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h6 class="mb-0 fw-semi-bold">
                                                            <a class="stretched-link text-900"
                                                                href="../pages/user/profile.html">Project
                                                                Managenemt</a>
                                                        </h6>
                                                        <p class="text-500 fs-11 mb-0">
                                                            Themewagon
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress rounded-3 worked" style="height: 6px"
                                                    role="progressbar" aria-valuenow="43.72" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar bg-progress-gradient rounded-pill"
                                                        style="width: 50%"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center time">
                                                <p class="mb-0 fw-semi-bold">
                                                    12h:30m:30s
                                                </p>
                                            </td>
                                            <td class="align-middle text-center date">
                                                <p class="mb-0 fw-semi-bold">
                                                    08/01/22
                                                </p>
                                            </td>
                                            <td>
                                                <div class="avatar-group justify-content-end">
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/16.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/25.jpg"
                                                            alt="" />
                                                    </div>
                                                    <div
                                                        class="avatar avatar-xl border border-3 border-200 rounded-circle">
                                                        <img class="rounded-circle" src="../assets/img/team/18.jpg"
                                                            alt="" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary py-0 text-center">
                            <a class="btn btn-sm btn-link py-2" href="#!">Show All Projects<span
                                    class="fas fa-chevron-right ms-1 fs-11"></span></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-xxl-8">
                    <div class="card overflow-hidden h-100">
                        <div class="card-body p-0 management-calendar">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <div class="p-x1">
                                        <div class="d-flex justify-content-between">
                                            <div class="order-md-1">
                                                <button class="btn btn-sm border me-1 shadow-sm" type="button"
                                                    data-event="prev" data-bs-toggle="tooltip" title="Previous">
                                                    <span class="fas fa-chevron-left"></span>
                                                </button>
                                                <button class="btn btn-sm text-secondary border px-sm-4 shadow-sm"
                                                    type="button" data-event="today">
                                                    Today
                                                </button>
                                                <button class="btn btn-sm border ms-1 shadow-sm" type="button"
                                                    data-event="next" data-bs-toggle="tooltip" title="Next">
                                                    <span class="fas fa-chevron-right"></span>
                                                </button>
                                            </div>
                                            <button class="btn btn-sm text-primary border order-md-0 shadow-none"
                                                type="button" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                                <span class="fas fa-plus me-2"></span>New Schedule
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Find the JS file for the following calendar at: src/js/calendar/management-calendar.js-->
                                    <!-- If you are not using gulp based workflow, you can find the transpiled code at: public/assets/js/theme.js-->
                                    <div class="calendar-outline px-3" id="managementAppCalendar"
                                        data-calendar-option='{"title":"management-calendar-title","day":"management-calendar-day","events":"management-calendar-events"}'>
                                    </div>
                                </div>
                                <div class="col-md-5 bg-body-tertiary pt-3">
                                    <div class="px-3">
                                        <h4 class="mb-0 fs-9 fs-sm-8 fs-lg-7" id="management-calendar-title"></h4>
                                        <p class="text-500 mb-0" id="management-calendar-day"></p>
                                        <ul class="list-unstyled mt-3 scrollbar management-calendar-events"
                                            id="management-calendar-events"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">To Do List</h6>
                        </div>
                        <div class="card-body p-0">
                            <div
                                class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input
                                        class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-primary"
                                        type="checkbox" id="checkbox-todo-0" />
                                    <label class="form-check-label mb-0 p-3" for="checkbox-todo-0">Design a facebook
                                        ad</label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="hover-actions">
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-clock"></span>
                                        </button>
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-user-plus"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button
                                            class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none"
                                            type="button" id="management-to-do-list-0" data-bs-toggle="dropdown"
                                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                            <span class="fas fa-ellipsis-h fs-11"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2"
                                            aria-labelledby="management-to-do-list-0">
                                            <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                                href="#!">Export</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input
                                        class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-secondary"
                                        type="checkbox" id="checkbox-todo-1" />
                                    <label class="form-check-label mb-0 p-3" for="checkbox-todo-1">Analyze Data</label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="hover-actions">
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-clock"></span>
                                        </button>
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-user-plus"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button
                                            class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none"
                                            type="button" id="management-to-do-list-1" data-bs-toggle="dropdown"
                                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                            <span class="fas fa-ellipsis-h fs-11"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2"
                                            aria-labelledby="management-to-do-list-1">
                                            <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                                href="#!">Export</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input
                                        class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-success"
                                        type="checkbox" id="checkbox-todo-2" />
                                    <label class="form-check-label mb-0 p-3" for="checkbox-todo-2">Youtube
                                        campaign</label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="hover-actions">
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-clock"></span>
                                        </button>
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-user-plus"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button
                                            class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none"
                                            type="button" id="management-to-do-list-2" data-bs-toggle="dropdown"
                                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                            <span class="fas fa-ellipsis-h fs-11"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2"
                                            aria-labelledby="management-to-do-list-2">
                                            <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                                href="#!">Export</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input
                                        class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-warning"
                                        type="checkbox" id="checkbox-todo-3" />
                                    <label class="form-check-label mb-0 p-3" for="checkbox-todo-3">Assign 10
                                        employee</label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="hover-actions">
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-clock"></span>
                                        </button>
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-user-plus"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button
                                            class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none"
                                            type="button" id="management-to-do-list-3" data-bs-toggle="dropdown"
                                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                            <span class="fas fa-ellipsis-h fs-11"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2"
                                            aria-labelledby="management-to-do-list-3">
                                            <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                                href="#!">Export</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input
                                        class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-danger"
                                        type="checkbox" id="checkbox-todo-4" />
                                    <label class="form-check-label mb-0 p-3" for="checkbox-todo-4">Meeting at 12</label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="hover-actions">
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-clock"></span>
                                        </button>
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-user-plus"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button
                                            class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none"
                                            type="button" id="management-to-do-list-4" data-bs-toggle="dropdown"
                                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                            <span class="fas fa-ellipsis-h fs-11"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2"
                                            aria-labelledby="management-to-do-list-4">
                                            <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                                href="#!">Export</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input
                                        class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-info"
                                        type="checkbox" id="checkbox-todo-5" />
                                    <label class="form-check-label mb-0 p-3" for="checkbox-todo-5">Meeting at 10</label>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="hover-actions">
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-clock"></span>
                                        </button>
                                        <button class="btn btn-tertiary icon-item rounded-3 me-2 fs-11 icon-item-sm">
                                            <span class="fas fa-user-plus"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button
                                            class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none"
                                            type="button" id="management-to-do-list-5" data-bs-toggle="dropdown"
                                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                            <span class="fas fa-ellipsis-h fs-11"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2"
                                            aria-labelledby="management-to-do-list-5">
                                            <a class="dropdown-item" href="#!">View</a><a class="dropdown-item"
                                                href="#!">Export</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#!">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary p-0">
                            <a class="btn btn-sm btn-link d-block py-2" href="#!"><span
                                    class="fas fa-plus me-1 fs-11"></span>Add New
                                Task</a>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">
                            Thank you for creating with Falcon
                            <span class="d-none d-sm-inline-block">| </span><br class="d-sm-none" />
                            2024 &copy;
                            <a href="https://themewagon.com">Themewagon</a>
                        </p>
                    </div>
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">v3.23.0</p>
                    </div>
                </div>
            </footer>
        </div>
        <div class="modal fade" id="authentication-modal" tabindex="-1" role="dialog"
            aria-labelledby="authentication-modal-label" aria-hidden="true">
            <div class="modal-dialog mt-6" role="document">
                <div class="modal-content border-0">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="authentication-modal-label">
                                Register
                            </h4>
                            <p class="fs-10 mb-0 text-white">
                                Please create your free Falcon account
                            </p>
                        </div>
                        <div data-bs-theme="dark">
                            <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body py-4 px-5">
                        <form>
                            <div class="mb-3">
                                <label class="form-label" for="modal-auth-name">Name</label>
                                <input class="form-control" type="text" autocomplete="on" id="modal-auth-name" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="modal-auth-email">Email address</label>
                                <input class="form-control" type="email" autocomplete="on"
                                    id="modal-auth-email" />
                            </div>
                            <div class="row gx-2">
                                <div class="mb-3 col-sm-6">
                                    <label class="form-label" for="modal-auth-password">Password</label>
                                    <input class="form-control" type="password" autocomplete="on"
                                        id="modal-auth-password" />
                                </div>
                                <div class="mb-3 col-sm-6">
                                    <label class="form-label" for="modal-auth-confirm-password">Confirm Password</label>
                                    <input class="form-control" type="password" autocomplete="on"
                                        id="modal-auth-confirm-password" />
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal-auth-register-checkbox" />
                                <label class="form-label" for="modal-auth-register-checkbox">I accept the <a
                                        href="#!">terms </a>and
                                    <a class="white-space-nowrap" href="#!">privacy policy</a></label>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">
                                    Register
                                </button>
                            </div>
                        </form>
                        <div class="position-relative mt-5">
                            <hr />
                            <div class="divider-content-center">
                                or register with
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-sm-6">
                                <a class="btn btn-outline-google-plus btn-sm d-block w-100" href="#"><span
                                        class="fab fa-google-plus-g me-2" data-fa-transform="grow-8"></span>
                                    google</a>
                            </div>
                            <div class="col-sm-6">
                                <a class="btn btn-outline-facebook btn-sm d-block w-100" href="#"><span
                                        class="fab fa-facebook-square me-2" data-fa-transform="grow-8"></span>
                                    facebook</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="eventDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border"></div>
            </div>
        </div>
        <div class="modal fade" id="addEventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content border">
                    <form id="addEventForm" autocomplete="off">
                        <div class="modal-header px-x1 bg-body-tertiary border-bottom-0">
                            <h5 class="modal-title">Create Schedule</h5>
                            <button class="btn-close me-n1" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-x1">
                            <div class="mb-3">
                                <label class="fs-9" for="eventTitle">Title</label>
                                <input class="form-control" id="eventTitle" type="text" name="title"
                                    required="required" />
                            </div>
                            <div class="mb-3">
                                <label class="fs-9" for="eventStartDate">Start Date</label>
                                <input class="form-control datetimepicker" id="eventStartDate" type="text"
                                    required="required" name="startDate" placeholder="yyyy/mm/dd hh:mm"
                                    data-options='{"static":"true","enableTime":"true","dateFormat":"Y-m-d H:i"}' />
                            </div>
                            <div class="mb-3">
                                <label class="fs-9" for="eventEndDate">End Date</label>
                                <input class="form-control datetimepicker" id="eventEndDate" type="text"
                                    name="endDate" placeholder="yyyy/mm/dd hh:mm"
                                    data-options='{"static":"true","enableTime":"true","dateFormat":"Y-m-d H:i"}' />
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="eventAllDay" name="allDay" />
                                <label class="form-check-label" for="eventAllDay">All Day
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="fs-9">Schedule Meeting</label>
                                <div>
                                    <a class="btn badge-subtle-success btn-sm" href="#!"><span
                                            class="fas fa-video me-2"></span>Add
                                        video conference link</a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="fs-9" for="eventDescription">Description</label>
                                <textarea class="form-control" rows="3" name="description" id="eventDescription"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="fs-9" for="eventLabel">Label</label>
                                <select class="form-select" id="eventLabel" name="label">
                                    <option value="" selected="selected">
                                        None
                                    </option>
                                    <option value="primary">Business</option>
                                    <option value="danger">Important</option>
                                    <option value="success">Personal</option>
                                    <option value="warning">Must Attend</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-end align-items-center bg-body-tertiary border-0">
                            <a class="me-3 text-600" href="../app/events/create-an-event.html">More options</a>
                            <button class="btn btn-primary px-4" type="submit">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
