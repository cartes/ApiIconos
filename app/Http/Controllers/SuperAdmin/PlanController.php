<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class PlanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('role:super-admin'),
        ];
    }

    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::query()
            ->orderByDesc('is_active')
            ->orderBy('price')
            ->get();

        return response()->json([
            'data' => $plans,
            'meta' => [
                'legacy_tenant_plan' => SubscriptionPlan::legacyTenantOneDefaults(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $plan = SubscriptionPlan::create($validated);

        return response()->json([
            'message' => 'Plan created successfully.',
            'data' => $plan,
        ], 201);
    }

    public function show(SubscriptionPlan $plan): JsonResponse
    {
        return response()->json([
            'data' => $plan,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $validated = $request->validate($this->rules($plan));

        $plan->update($validated);

        return response()->json([
            'message' => 'Plan updated successfully.',
            'data' => $plan->fresh(),
        ]);
    }

    public function destroy(SubscriptionPlan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }

    /**
     * Returns the legacy commercial profile that keeps Tenant 1 backward compatible.
     */
    public function legacyTenantOneProfile(): JsonResponse
    {
        return response()->json([
            'data' => SubscriptionPlan::legacyTenantOneDefaults(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    private function rules(?SubscriptionPlan $plan = null): array
    {
        return [
            'name' => [$plan ? 'sometimes' : 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => [$plan ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'storage_limit_gb' => [$plan ? 'sometimes' : 'required', 'integer', 'min:0'],
            'mp_plan_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('plans', 'mp_plan_id')->ignore($plan?->id),
            ],
            'is_active' => [$plan ? 'sometimes' : 'required', 'boolean'],
        ];
    }
}
