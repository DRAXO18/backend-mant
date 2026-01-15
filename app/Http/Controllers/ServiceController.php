<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $services = Service::query()
            ->with(['vehicle', 'client', 'serviceType', 'details'])
            ->when($status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($status === 'all', fn ($q) => $q->withTrashed())
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('vehicle', fn ($v) =>
                    $v->where('plate_number', 'like', "%{$q}%")
                )->orWhereHas('client.user', fn ($c) =>
                    $c->where('name', 'like', "%{$q}%")
                );
            })
            ->orderByDesc('service_date')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'filters' => compact('status', 'q'),
            'data' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'client_id' => ['required', 'exists:clients,id'],
            'service_type_id' => ['required', 'exists:service_types,id'],
            'service_date' => ['required', 'date'],
            'mileage_at_service' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'in:0,1,2,3,4'],

            // details opcionales
            'details.observations' => ['nullable', 'string'],
            'details.recommendation' => ['nullable', 'string'],
        ]);

        $service = DB::transaction(function () use ($validated) {
            $service = Service::create([
                'vehicle_id' => $validated['vehicle_id'],
                'client_id' => $validated['client_id'],
                'service_type_id' => $validated['service_type_id'],
                'service_date' => $validated['service_date'],
                'mileage_at_service' => $validated['mileage_at_service'],
                'status' => $validated['status'] ?? 1,
            ]);

            if (!empty($validated['details'])) {
                $this->serviceDetailsStore($service->id, $validated['details']);
            }

            return $service->load(['vehicle', 'client', 'serviceType', 'details']);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Service created successfully.',
            'data' => $service,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'service_date' => ['sometimes', 'required', 'date'],
            'mileage_at_service' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'in:0,1,2,3,4'],

            'details.observations' => ['nullable', 'string'],
            'details.recommendation' => ['nullable', 'string'],
        ]);

        $service = DB::transaction(function () use ($service, $validated) {
            $service->update(
                collect($validated)->except('details')->toArray()
            );

            if (isset($validated['details'])) {
                $this->serviceDetailsStore($service->id, $validated['details']);
            }

            return $service->fresh()->load(['vehicle', 'client', 'serviceType', 'details']);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Service updated successfully.',
            'data' => $service,
        ]);
    }

    public function destroy(int $id)
    {
        $service = Service::findOrFail($id);
        $service->delete(); // soft delete

        return response()->json([
            'ok' => true,
            'message' => 'Service deleted (soft) successfully.',
        ]);
    }

    public function serviceDetailsStore(int $serviceId, array $data)
    {
        return ServiceDetail::updateOrCreate(
            ['service_id' => $serviceId],
            [
                'observations' => $data['observations'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
            ]
        );
    }
}
