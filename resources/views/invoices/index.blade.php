@extends('layout.main')

@section('title', 'Invoices')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Invoices</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            {{-- Tombol 'Create New' bisa diarahkan ke POS atau form invoice manual --}}
            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i>
                Create New Invoice
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Invoice #</th>
                            <!-- <th scope="col">Customer</th> -->
                            <th scope="col">Date</th>
                            <th scope="col">Due Date</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                                <tr>
                                    <td>{{ 'INV-' . str_pad($inv['id'], 3, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $inv['tanggal'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($inv['tanggal'])->addDays(7)->format('Y-m-d') }}</td>
                                    <td>Rp {{ number_format($inv['total'], 0, ',', '.') }}</td>

                                    <td>
                                        <span class="badge 
                            {{ $inv['total'] <= 0 ? 'bg-danger' : 'bg-success' }}">
                                            Paid
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('invoices.show', $inv['id']) }}" class="btn btn-sm btn-outline-secondary">
                                            View
                                        </a>
                                         {{-- Button Delete --}}
    <form action="{{ route('invoices.destroy', $inv['id']) }}"
          method="POST"
          class="d-inline-block"
          onsubmit="return confirm('Delete this invoice?');">

        @csrf
        @method('DELETE')

        <button class="btn btn-sm btn-outline-danger">
            Delete
        </button>
    </form>
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection