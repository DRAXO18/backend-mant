<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index()
    {
        $response = Http::withHeaders([
            'apikey' => config('services.supabase.key'),
            'Authorization' => 'Bearer ' . config('services.supabase.key'),
            'Content-Type' => 'application/json',
        ])->get(
            config('services.supabase.url') . '/rest/v1/productos',
            [
                'select' => 'id,descripcion,stock,stock_minimo,precioventa,id_empresa'
            ]
        );

        if (!$response->successful()) {
            return response()->json([
                'ok' => false,
                'error' => 'Error fetching products from Supabase',
                'details' => $response->body(),
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'data' => $response->json(),
        ]);
    }
}
