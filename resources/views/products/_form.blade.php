@php
    $current = $product ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-7">
            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $current?->name) }}"
                class="form-control @error('name') is-invalid @enderror"
                maxlength="150"
                placeholder="Contoh: Laptop Pro 14"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-5">
            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
            <input
                type="text"
                id="sku"
                name="sku"
                value="{{ old('sku', $current?->sku) }}"
                class="form-control @error('sku') is-invalid @enderror"
                maxlength="50"
                placeholder="Contoh: ELK-001"
                required
            >
            @error('sku')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">— Pilih kategori —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $current?->category_id) === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
            <input
                type="number"
                id="price"
                name="price"
                value="{{ old('price', $current?->price) }}"
                class="form-control @error('price') is-invalid @enderror"
                min="0"
                max="9999999999.99"
                step="0.01"
                placeholder="0"
                required
            >
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="stock" class="form-label">Stok <span class="text-danger">*</span></label>
            <input
                type="number"
                id="stock"
                name="stock"
                value="{{ old('stock', $current?->stock) }}"
                class="form-control @error('stock') is-invalid @enderror"
                min="0"
                step="1"
                placeholder="0"
                required
            >
            @error('stock')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="aktif" @selected(old('status', $current?->status ?? 'aktif') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $current?->status) === 'nonaktif')>Nonaktif</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="5000"
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Informasi tambahan tentang produk (opsional)"
            >{{ old('description', $current?->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan
        </button>
        <a href="{{ $current ? route('products.show', $current) : route('products.index') }}" class="btn btn-outline-secondary">
            Batal
        </a>
    </div>
</form>
