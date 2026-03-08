<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::check()) {
            return;
        }

        $companyId = Auth::user()
            ->companies()
            ->first()?->id;

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }
}