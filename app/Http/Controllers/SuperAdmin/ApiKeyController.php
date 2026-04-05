<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the API keys for all tenants.
     */
    public function index()
    {
        $keys = ApiKey::with('tokenable')->latest()->paginate(20);

        return response()->json([
            'data' => $keys,
        ]);
    }

    /**
     * Store a newly created API key for a specific tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|string|exists:tenants,id',
            'name' => 'required|string|max:255',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string|url',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);

        // Create the token linked to the Tenant model
        $newAccessToken = $tenant->createToken($validated['name']);

        // Attach allowed domains using our customized model
        /** @var ApiKey $tokenModel */
        $tokenModel = $newAccessToken->accessToken;

        if (! empty($validated['allowed_domains'])) {
            $tokenModel->domains = $validated['allowed_domains'];
            $tokenModel->save();
            Cache::forget('cors_allowed_origins_db');
        }

        return response()->json([
            'message' => 'API Key created successfully.',
            'token' => $newAccessToken->plainTextToken,
            'api_key' => $tokenModel,
        ], 201);
    }

    /**
     * Remove the specified API key.
     */
    public function destroy(string $id)
    {
        $token = ApiKey::findOrFail($id);

        $token->delete();

        Cache::forget('cors_allowed_origins_db');

        return response()->json([
            'message' => 'API Key revoked successfully.',
        ]);
    }
}
