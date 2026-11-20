<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        string $action,
        ?User $user,
        ?Product $product = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
    ): ActivityLog {
        $request = request();

        $log = ActivityLog::query()->create([
            'user_id' => $user?->id,
            'product_id' => $product?->id,
            'action' => $action,
            'description' => $description ?? ActivityLog::actionLabels()[$action] ?? $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() !== null
                ? mb_substr((string) $request->userAgent(), 0, 255)
                : null,
            'created_at' => now(),
        ]);

        Log::info('activity', [
            'action' => $action,
            'user_id' => $user?->id,
            'product_id' => $product?->id,
            'description' => $log->description,
            'ip' => $log->ip_address,
        ]);

        return $log;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Product $product): array
    {
        $product->loadMissing('category');

        return [
            'sku' => $product->sku,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'price' => (string) $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'description' => $product->description,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array{old: array<string, mixed>, new: array<string, mixed>}
     */
    public function changedValues(array $before, array $after): array
    {
        $old = [];
        $new = [];

        foreach ($after as $key => $value) {
            $previous = $before[$key] ?? null;

            if ((string) $previous !== (string) $value) {
                $old[$key] = $previous;
                $new[$key] = $value;
            }
        }

        return ['old' => $old, 'new' => $new];
    }
}
