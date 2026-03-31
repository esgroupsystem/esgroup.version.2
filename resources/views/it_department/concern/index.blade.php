@extends('layouts.app')
@section('title', 'CCTV Job Orders - IT Department')

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

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $statsSource = $allJobOrders;

                $statusCounts = $statsSource->groupBy('status')->map->count();

                $issueCounts = $statsSource->groupBy('issue_type')->map->count()->sortDesc();
                $topIssue = $issueCounts->keys()->first();
                $topIssueCount = $issueCounts->first() ?? 0;

                $partCounts = $statsSource
                    ->flatMap(function ($job) {
                        return $job->usedItems->map(function ($used) {
                            return $used->inventoryItem->item_name ?? null;
                        });
                    })
                    ->filter()
                    ->groupBy(fn($name) => $name)
                    ->map->count()
                    ->sortDesc();

                $topPart = $partCounts->keys()->first();
                $topPartCount = $partCounts->first() ?? 0;

                $assigneeCounts = $statsSource
                    ->map(fn($x) => $x->assignee->full_name ?? null)
                    ->filter()
                    ->groupBy(fn($n) => $n)
                    ->map->count()
                    ->sortDesc();

                $topAssignee = $assigneeCounts->keys()->first();
                $topAssigneeCount = $assigneeCounts->first() ?? 0;
            @endphp

            @include('it_department.concern.partials.header')
            @include('it_department.concern.partials.monitoring', [
                'statusCounts' => $statusCounts,
                'issueCounts' => $issueCounts,
                'topIssue' => $topIssue,
                'topIssueCount' => $topIssueCount,
                'partCounts' => $partCounts,
                'topPart' => $topPart,
                'topPartCount' => $topPartCount,
                'assigneeCounts' => $assigneeCounts,
                'topAssignee' => $topAssignee,
                'topAssigneeCount' => $topAssigneeCount,
            ])

            <div class="row g-3">
                <div class="col-xxl-9 col-xl-8">
                    @include('it_department.concern.partials.table', ['jobOrders' => $jobOrders])
                </div>

                <div class="col-xxl-3 col-xl-4 d-none d-xl-block">
                    @include('it_department.concern.partials.desktop-filter')
                </div>
            </div>

            @include('it_department.concern.partials.mobile-filter')
            @include('it_department.concern.partials.create-modal')
            @include('it_department.concern.partials.edit-modals', ['jobOrders' => $jobOrders])
        </div>
    </div>
@endsection

@include('it_department.concern.partials.scripts')
@include('it_department.concern.partials.styles')
