<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(): View
    {
        return view('products.index', [
            'products' => $this->products->paginate(),
            'summary' => $this->products->summary(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($request->validated(), $request->user());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produk "'.$product->name.'" berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'createdBy', 'updatedBy']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product = $this->products->update($product, $request->validated(), $request->user());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produk "'.$product->name.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $name = $product->name;

        $this->products->delete($product);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk "'.$name.'" berhasil dihapus.');
    }
}
