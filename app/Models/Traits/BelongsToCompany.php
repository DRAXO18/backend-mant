<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Scopes\CompanyScope;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        static::addGlobalScope(new CompanyScope);

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

            if (auth()->check() && Schema::hasColumn($model->getTable(), 'created_by')) {
                $model->created_by = auth()->id();
            }
        });
    }
}