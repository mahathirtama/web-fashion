@extends('layout.main')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('inventory.update', $product['id']) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- KIRI --}}
                <div class="col-md-8">

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ $product['name'] }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="deskripsi" class="form-control" rows="5">{{ $product['deskripsi'] }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="price" class="form-control"
                                   value="{{ $product['price'] }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="text" name="image" class="form-control"
                               value="{{ $product['image'] }}">
                    </div>

                </div>

                {{-- KANAN --}}
                <div class="col-md-4">

                    <div class="mb-3">
                        <label class="form-label">Kode Product</label>
                        <input type="text" name="kode_product" class="form-control"
                               value="{{ $product['kode_product'] }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Choose...</option>
                            <option value="T-Shirts"   {{ $product['category'] === 'T-Shirts' ? 'selected' : '' }}>T-Shirts</option>
                            <option value="Jeans"      {{ $product['category'] === 'Jeans' ? 'selected' : '' }}>Jeans</option>
                            <option value="Hoodies"    {{ $product['category'] === 'Hoodies' ? 'selected' : '' }}>Hoodies</option>
                            <option value="Sneakers"   {{ $product['category'] === 'Sneakers' ? 'selected' : '' }}>Sneakers</option>
                            <option value="Accessories"{{ $product['category'] === 'Accessories' ? 'selected' : '' }}>Accessories</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Choose supplier...</option>
                            <option value="1" {{ $product['supplier_id'] == 1 ? 'selected' : '' }}>Supplier Garmentindo</option>
                            <option value="2" {{ $product['supplier_id'] == 2 ? 'selected' : '' }}>Supplier Bahan Kain</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control"
                               value="{{ $product['stock'] }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   {{ $product['status'] === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $product['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>

        </form>
    </div>
</div>
@endsection
