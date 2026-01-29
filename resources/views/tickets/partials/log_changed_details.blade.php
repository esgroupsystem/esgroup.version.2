<details class="mt-1">
    <summary class="small text-muted" style="cursor:pointer;">
        View changes ({{ count($items) }})
    </summary>

    <div class="mt-2">
        @foreach($items as $it)
            <div class="small">
                <strong>{{ $it['field'] }}</strong>:
                <span class="text-muted">{{ $it['from'] }}</span>
                <span class="mx-1">→</span>
                <span class="fw-semibold">{{ $it['to'] }}</span>
            </div>
        @endforeach
    </div>
</details>
