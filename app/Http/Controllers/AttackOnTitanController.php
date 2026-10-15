<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttackOnTitanController extends Controller
{
    public const COLLECTIONS = [
        'characters',
        'episodes',
        'locations',
        'organizations',
        'titans',
    ];

    public function index(Request $request, string $collection): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        try {
            $response = Http::connectTimeout(3)
                ->timeout(8)
                ->get(
                    rtrim(config('services.attack_on_titan.url'), '/').'/'.$collection,
                    ['page' => $validated['page'] ?? 1],
                );
        } catch (ConnectionException $exception) {
            Log::warning('No se pudo conectar con la API de Attack on Titan.', [
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'La API de Attack on Titan no respondió. Inténtalo de nuevo más tarde.',
            ], 504);
        }

        return response()->json($response->json(), $response->status());
    }
}
