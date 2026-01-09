<?php

namespace App\Http\Controllers\Rubro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    protected function currentUser(Request $request)
    {
        return Auth::user() ?? $request->user();
    }

    protected function createCompanyReview(
        int $companyId,
        string $action,
        ?string $reason,
        int $performedBy
    ): void {
        CompanyReview::create([
            'company_id'   => $companyId,
            'action'       => $action,
            'reason'       => $reason,
            'performed_by' => $performedBy,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'ruc'        => 'required|string|max:20|unique:companies,ruc',
            'email'      => 'required|email|unique:companies,email',
            'phone'      => 'nullable|string|max:20',
            'ubigeo_id' => 'nullable|exists:ubigeo,id',
        ]);

        DB::transaction(function () use ($validated) {
            Company::create([
                'name'            => $validated['name'],
                'ruc'             => $validated['ruc'],
                'email'           => $validated['email'],
                'phone'           => $validated['phone'] ?? null,
                'ubigeo_id' => $validated['ubigeo_id'] ?? null,

                // Estados iniciales
                'approval_status' => 0, // pending
                'status'          => 0, // inactive
            ]);
        });

        return response()->json([
            'message' => 'Solicitud de afiliación enviada correctamente.'
        ], 201);
    }

    public function index(Request $request)
    {
        $companies = Company::query()
            ->select(
                'id',
                'name',
                'ruc',
                'email',
                'status',
                'approval_status',
                'created_at'
            )
            ->latest()
            ->paginate(15);

        return response()->json($companies);
    }

    public function show(Company $company)
    {
        $company->load([
            'reviews' => function ($q) {
                $q->latest();
            }
        ]);

        return response()->json($company);
    }

    public function approve(Request $request, Company $company)
    {
        $user = $this->currentUser($request);

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($company->approval_status !== 0) {
            return response()->json([
                'message' => 'Solo se pueden aprobar empresas pendientes.'
            ], 409);
        }

        DB::transaction(function () use ($company, $user) {
            $company->update([
                'approval_status' => 1,
                'status'          => 1,
                'approved_at'     => now(),
            ]);

            $this->createCompanyReview(
                $company->id,
                'approved',
                null,
                $user->id
            );
        });

        return response()->json([
            'message' => 'Empresa aprobada correctamente.'
        ]);
    }


    public function reject(Request $request, Company $company)
    {
        $user = $this->currentUser($request);

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($company->approval_status !== 0) {
            return response()->json([
                'message' => 'Solo se pueden rechazar empresas pendientes.'
            ], 409);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        DB::transaction(function () use ($company, $validated, $user) {
            $company->update([
                'approval_status' => 2,
                'status'          => 0,
            ]);

            $this->createCompanyReview(
                $company->id,
                'rejected',
                $validated['reason'],
                $user->id
            );
        });

        return response()->json([
            'message' => 'Empresa rechazada correctamente.'
        ]);
    }


    public function suspend(Request $request, Company $company)
    {
        $user = $this->currentUser($request);

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($company->approval_status !== 1) {
            return response()->json([
                'message' => 'Solo se pueden suspender empresas aprobadas.'
            ], 409);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string'
        ]);

        DB::transaction(function () use ($company, $validated, $user) {
            $company->update([
                'approval_status' => 3,
                'status'          => 2,
                'suspended_at'    => now(),
            ]);

            $this->createCompanyReview(
                $company->id,
                'suspended',
                $validated['reason'] ?? null,
                $user->id
            );
        });

        return response()->json([
            'message' => 'Empresa suspendida correctamente.'
        ]);
    }
}
