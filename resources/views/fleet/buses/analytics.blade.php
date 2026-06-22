@extends('layouts.app')

@section('content')
    <div class="container">

        <h3>Fleet Analytics Dashboard</h3>

        {{-- GARAGE SUMMARY --}}
        <h5 class="mt-4">Garage Summary</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Garage</th>
                    <th>Active</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($garage_summary as $garage => $data)
                    <tr>
                        <td>{{ $garage }}</td>
                        <td>{{ $data['active'] }}</td>
                        <td>{{ $data['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- COMPANY SUMMARY --}}
        <h5 class="mt-4">Company Summary</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Active</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($company_summary as $company => $data)
                    <tr>
                        <td>{{ $company }}</td>
                        <td>{{ $data['active'] }}</td>
                        <td>{{ $data['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL UNITS --}}
        <div class="alert alert-warning mt-4">
            <strong>Total Units:</strong> {{ $total_units }}
        </div>

    </div>
@endsection
