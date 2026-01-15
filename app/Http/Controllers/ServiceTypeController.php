<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $types = ServiceType::query()
            ->when($status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($status === 'all', fn ($q) => $q->withTrashed())
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'filters' => compact('status', 'q'),
            'data' => $types,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:service_types,name'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        $type = DB::transaction(function () use ($validated) {
            return ServiceType::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'status' => $validated['status'] ?? 1,
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Service type created successfully.',
            'data' => $type,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $type = ServiceType::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                Rule::unique('service_types', 'name')->ignore($type->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'category' => ['sometimes', 'nullable', 'string', 'max:60'],
            'status' => ['sometimes', 'required', 'in:0,1'],
        ]);

        $type = DB::transaction(function () use ($type, $validated) {
            $type->update($validated);
            return $type->fresh();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Service type updated successfully.',
            'data' => $type,
        ]);
    }

    /**
     * NO hard delete.
     * Se desactiva o se soft-deletea para proteger servicios existentes.
     */
    public function destroy(int $id)
    {
        $type = ServiceType::findOrFail($id);

        DB::transaction(function () use ($type) {
            // opción segura: desactivar + soft delete
            $type->update(['status' => 0]);
            $type->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Service type disabled successfully.',
        ]);
    }
}
