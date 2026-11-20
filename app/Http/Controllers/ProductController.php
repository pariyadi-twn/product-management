<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateProductException;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogService;
use App\Services\ProductService;
use App\Support\PerformanceMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly ActivityLogService $activityLogs,
        private readonly PerformanceMetrics $metrics,
    ) {}

    public function index(ProductIndexRequest $request): Response
    {
        $startedAt = microtime(true);

        try {
            $products = $this->products->paginate($request->filters());
        } catch (Throwable $exception) {
            try {
                $this->activityLogs->record(
                    ActivityLog::ACTION_SEARCH_ERROR,
                    $request->user(),
                    null,
                    null,
                    ['filters' => $request->filters(), 'error' => $exception->getMessage()],
                    'Pencarian produk gagal dijalankan.',
                );
            } catch (Throwable) {
                // Jangan menutupi error utama bila pencatatan juga gagal.
            }

            throw $exception;
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $this->metrics->resultCount = $products->total();
        $this->metrics->durationMs = $durationMs;

        return response()
            ->view('products.index', [
                'products' => $products,
                'summary' => $this->products->summary(),
                'filters' => $request->filters(),
                'hasActiveFilters' => $request->hasActiveFilters(),
                'searchDurationMs' => $durationMs,
            ])
            ->header('X-Search-Duration-Ms', (string) $durationMs);
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

        $this->products->delete($product, $request->user());

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
