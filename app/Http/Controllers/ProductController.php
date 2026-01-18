<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ProductController extends SupabaseAuthContextController
{
    public function index(Request $request)
    {
        $this->initSupabaseContext($request);

        $response = Http::withHeaders([
            'apikey'        => config('services.supabase.key'),
            'Authorization' => 'Bearer ' . $this->supabaseToken,
        ])->get(
            config('services.supabase.url') . '/rest/v1/productos',
            [
                'select' => 'id,descripcion,stock,stock_minimo,precioventa,id_empresa'
            ]
        );

        return response()->json([
            'ok' => true,
            'data' => $response->json(),
        ]);
    }

    public function store(Request $request)
    {
        $this->initSupabaseContext($request);

        $validated = $request->validate([
            'descripcion'   => ['required', 'string'],
            'idmarca'       => ['nullable', 'integer'],
            'stock'         => ['nullable', 'numeric'],
            'stock_minimo'  => ['nullable', 'integer'],
            'codigobarras'  => ['nullable', 'string'],
            'codigointerno' => ['nullable', 'string'],
            'precioventa'   => ['nullable', 'numeric'],
            'preciocompra'  => ['nullable', 'numeric'],
            'id_categoria'  => ['nullable', 'integer'],
        ]);

        $response = Http::withHeaders([
            'apikey'        => config('services.supabase.key'),
            'Authorization' => 'Bearer ' . $this->supabaseToken,
            'Prefer'        => 'return=representation',
        ])->post(
            config('services.supabase.url') . '/rest/v1/productos',
            $validated
        );

        if (!$response->successful()) {
            return response()->json([
                'ok' => false,
                'message' => 'Error al crear producto',
                'details' => $response->json(),
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Producto creado correctamente',
            'data' => $response->json()[0] ?? null,
        ], 201);
    }
}
