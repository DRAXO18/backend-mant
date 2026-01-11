<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $vehicles = Vehicle::query()
            ->with('owner')
            ->when($status === 'deleted', fn($q) => $q->onlyTrashed())
            ->when($status === 'all', fn($q) => $q->withTrashed())
            ->when($q !== '', function ($query) use ($q) {
                $query->where('plate_number', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'filters' => [
                'status' => $status,
                'q' => $q,
            ],
            'data' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => [
                'required',
                Rule::exists('owners', 'id')
                    ->whereNull('deleted_at')
            ],
            'plate_number' => ['required', 'string', 'max:15', 'unique:vehicles,plate_number'],
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'current_mileage' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        $vehicle = Vehicle::create([
            'owner_id' => $validated['owner_id'],
            'plate_number' => strtoupper($validated['plate_number']),
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'current_mileage' => $validated['current_mileage'] ?? 0,
            'status' => $validated['status'] ?? 1,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Vehicle created successfully.',
            'data' => $vehicle->load('owner'),
        ], 201);
    }


    public function update(Request $request, int $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'plate_number' => [
                'sometimes',
                'required',
                'string',
                'max:15',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id),
            ],
            'brand' => ['sometimes', 'required', 'string', 'max:80'],
            'model' => ['sometimes', 'required', 'string', 'max:80'],
            'current_mileage' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'in:0,1'],
        ]);

        if (isset($validated['plate_number'])) {
            $validated['plate_number'] = strtoupper($validated['plate_number']);
        }

        $vehicle->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Vehicle updated successfully.',
            'data' => $vehicle->fresh()->load('owner'),
        ]);
    }

    public function destroy(int $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete(); // soft delete

        return response()->json([
            'ok' => true,
            'message' => 'Vehicle deleted (soft) successfully.',
        ]);
    }
}
