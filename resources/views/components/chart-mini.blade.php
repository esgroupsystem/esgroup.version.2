<div class="col-md-6 {{ $wrapperClass ?? '' }}">
    <div class="row g-0">
        <div class="col-6">
            <img class="mt-1" src="{{ asset($icon) }}" width="39">

            <h2 class="mt-2 mb-1 text-700 fw-normal">
                {{ $value }}
                <span class="fas fa-caret-up ms-2 me-1 fs-10 {{ $trendColor }}"></span>
                <span class="fs-10 fw-semi-bold {{ $trendColor }}">
                    {{ number_format($percent, 2) }}%
                </span>
            </h2>
            <h6 class="mb-0">{{ $label }}</h6>
        </div>

        <div class="col-6 d-flex align-items-center px-0">

            @php
                // FIX #1: If chart has no real data, force a thin line
                if (collect($series)->every(fn($v) => $v == 0)) {
                    $series = [0.1, 0.1, 0.1, 0.1, 0.1, 0.1];
                }

                // FIX #2: Ensure line is always visible even if very small
                $minVisible = 0.3;
                $series = collect($series)->map(function ($v) use ($minVisible) {
                    if ($v == 0)
                        return 0.1;
                    return max($v, $minVisible);
                })->toArray();

                // UPGRADED CHART STYLE (like your first screenshot)
                $chartConfig = [
                    'animationDuration' => 1800,
                    'animationEasing' => 'cubicOut',

                    'tooltip' => [
                        'trigger' => 'axis',
                        'backgroundColor' => 'rgba(0,0,0,0.7)',
                        'textStyle' => ['color' => '#fff']
                    ],

                    'xAxis' => [
                        'type' => 'category',
                        'data' => $labels ?? ['W1', 'W2', 'W3', 'W4', 'W5', 'W6'],
                        'axisLine' => ['show' => false],
                        'axisTick' => ['show' => false],
                        'axisLabel' => ['show' => false],
                    ],

                    'yAxis' => ['show' => false],

                    'series' => [
                        [
                            'type' => 'line',
                            'smooth' => true,
                            'symbol' => 'none',

                            // UPGRADED LINE STYLE
                            'lineStyle' => [
                                'width' => 3,
                                'color' => (string) $color,
                                'shadowColor' => (string) $color . '55',
                                'shadowBlur' => 10,
                                'shadowOffsetY' => 6,
                                'cap' => 'round',
                                'join' => 'round'
                            ],

                            // UPGRADED GRADIENT
                            'areaStyle' => [
                                'color' => [
                                    'type' => 'linear',
                                    'x' => 0,
                                    'y' => 0,
                                    'x2' => 0,
                                    'y2' => 1,
                                    'colorStops' => [
                                        ['offset' => 0, 'color' => (string) $areaStart],
                                        ['offset' => 1, 'color' => (string) $areaEnd],
                                    ]
                                ]
                            ],

                            'data' => $series
                        ]
                    ],

                    'grid' => [
                        'left' => 0,
                        'right' => 0,
                        'top' => 0,
                        'bottom' => 0
                    ]
                ];

                $chartJson = json_encode($chartConfig, JSON_UNESCAPED_SLASHES);
            @endphp

            <div class="h-50 w-100" data-echarts="{{ $chartJson }}" data-echart-responsive="true">
            </div>
        </div>
    </div>
</div>