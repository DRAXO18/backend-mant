<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\Scopes\CompanyScope;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        // ACTIVA EL GLOBAL SCOPE
        static::addGlobalScope(new CompanyScope);

        // AUTO RELLENAR AL CREAR
        static::creating(function ($model) {

            if (!$model->company_id) {

                $companyId = DB::table('company_user')
                    ->where('user_id', auth()->id())
                    ->value('company_id');

                if (!$companyId) {
                    throw new \Exception('User does not belong to a company.');
                }

                $model->company_id = $companyId;
            }

            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}