@extends('layout.main')

@section('title', 'Inventory Management')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Inventory Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('inventory.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i>
                Add New Product
            </a>
        </div>
    </div>

    {{-- Fitur Filter dan Search --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Search by product name or SKU...">
                </div>
                <div class="col-md-4">
                    <select class="form-select">
                        <option selected>Filter by category...</option>
                        <option>T-Shirts</option>
                        <option>Jeans</option>
                        <option>Hoodies</option>
                        <option>Sneakers</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-secondary w-100">Search</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Daftar Produk --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">SKU</th>
                            <th scope="col">Product</th>
                            <th scope="col">Category</th>
                            <th scope="col">Price</th>
                            <th scope="col">Stock on Hand</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $p)
                            <tr>
                                <td>{{ $p['kode_product'] }}</td>
                                <td>
                                    <img src="{{ $p['image'] }}" width="40" class="me-2">
                                    {{ $p['name'] }}
                                </td>
                                <td>{{ $p['category'] }}</td>
                                <td>Rp {{ number_format($p['price'], 0, ',', '.') }}</td>
                                <td>{{ $p['stock'] }}</td>
                                <td>
                                    @if ($p['status'] === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('inventory.edit', $p['id']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection