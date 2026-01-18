<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class SupabaseAuthContextController extends Controller
{
    protected ?string $supabaseUid = null;
    protected ?string $supabaseToken = null;

    protected function initSupabaseContext(Request $request): void
    {
        $this->supabaseUid = $request->attributes->get('supabase_uid');
        $this->supabaseToken = $request->cookie('sb_access_token');
    }
}
