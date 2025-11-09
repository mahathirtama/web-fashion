@extends('layout.main')

@section('title', 'Add New Product')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Product</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf

            <div class="row">
                {{-- KIRI --}}
                <div class="col-md-8">

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="deskripsi" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="text" name="image" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                </div>

                {{-- KANAN --}}
                <div class="col-md-4">

                    <div class="mb-3">
                        <label class="form-label">Kode Product</label>
                        <input type="text" name="kode_product" class="form-control" placeholder="PRD001" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Choose category...</option>
                            <option value="T-Shirts">T-Shirts</option>
                            <option value="Jeans">Jeans</option>
                            <option value="Hoodies">Hoodies</option>
                            <option value="Sneakers">Sneakers</option>
                            <option value="Accessories">Accessories</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Choose supplier...</option>
                            <option value="1">Supplier Garmentindo</option>
                            <option value="2">Supplier Bahan Kain</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" name="stock" class="form-control" value="10" required>
                    </div>

                    {{-- default status --}}
                    <input type="hidden" name="status" value="active">

                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>

        </form>
    </div>
</div>
@endsection
