<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped mb-0">
        <thead class="bg-body-tertiary">
            <tr>
                <th>Garage</th>
                <th>Bus Name</th>
                <th>Body Number</th>
                <th>Plate Number</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($buses as $bus)
                <tr>
                    <td>{{ $bus->garage }}</td>
                    <td>{{ $bus->name }}</td>
                    <td>{{ $bus->body_number }}</td>
                    <td>{{ $bus->plate_number }}</td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('allbus.edit', $bus->id) }}" class="btn btn-falcon-primary btn-sm">
                                <span class="fas fa-edit"></span>
                            </a>

                            <form action="{{ route('allbus.destroy', $bus->id) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete this bus?')">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-falcon-danger btn-sm">
                                    <span class="fas fa-trash"></span>
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        No bus records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    {{ $buses->links('pagination.custom') }}
</div>

