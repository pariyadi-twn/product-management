<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateProductException;
use App\Http\Requests\ProductIndexRequest;
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

    public function index(ProductIndexRequest $request): View
    {
        $startedAt = microtime(true);
        $products = $this->products->paginate($request->filters());
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $request->attributes->set('performance.result_count', $products->total());
        $request->attributes->set('performance.duration_ms', $durationMs);

        return view('products.index', [
            'products' => $products,
            'summary' => $this->products->summary(),
            'filters' => $request->filters(),
            'hasActiveFilters' => $request->hasActiveFilters(),
            'searchDurationMs' => $durationMs,
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
        try {
            $product = $this->products->create($request->validated(), $request->user());
        } catch (DuplicateProductException $exception) {
            return $this->duplicateRedirect($exception);
        }

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
        try {
            $product = $this->products->update($product, $request->validated(), $request->user());
        } catch (DuplicateProductException $exception) {
            return $this->duplicateRedirect($exception);
        }

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

    private function duplicateRedirect(DuplicateProductException $exception): RedirectResponse
    {
        return back()
            ->withInput()
            ->withErrors([$exception->field => $exception->userMessage()])
            ->with('duplicate_product_id', $exception->product->id)
            ->with('duplicate_product_name', $exception->product->name);
    }
}
