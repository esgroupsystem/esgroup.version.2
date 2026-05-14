@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Garage</label>
        <input type="text" name="garage" class="form-control @error('garage') is-invalid @enderror"
            value="{{ old('garage', $bus->garage ?? '') }}" required>
        @error('garage')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Bus Name</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $bus->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Body Number</label>
        <input type="text" name="body_number" class="form-control @error('body_number') is-invalid @enderror"
            value="{{ old('body_number', $bus->body_number ?? '') }}" required>
        @error('body_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Plate Number</label>
        <input type="text" name="plate_number" class="form-control @error('plate_number') is-invalid @enderror"
            value="{{ old('plate_number', $bus->plate_number ?? '') }}" required>
        @error('plate_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('allbus.index') }}" class="btn btn-falcon-default">
        Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        Save
    </button>
</div>
