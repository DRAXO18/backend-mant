<?php

namespace App\Http\Controllers\Rubro;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\Client;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function technicians(Request $request)
    {
        $technicians = Technician::query()
            ->select(
                'id',
                'user_id',
                'company_id',
                'status',
                'created_at'
            )
            ->with([
                'user:id,name,email',
                'company:id,name,ruc'
            ])
            ->latest()
            ->paginate(15);

        return response()->json($technicians);
    }

    public function clients(Request $request)
    {
        $clients = Client::query()
            ->select(
                'id',
                'user_id',
                'created_at'
            )
            ->with([
                'user:id,name,email'
            ])
            ->latest()
            ->paginate(15);

        return response()->json($clients);
    }
}
