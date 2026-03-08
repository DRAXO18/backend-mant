<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToCompany;


class Client extends Model
{
    use SoftDeletes;
    use BelongsToCompany;


    protected $fillable = [
        'user_id',
        'ubigeo_id',
        'address',
        'company_id',
    ];

    protected $casts = [
        'user_id'   => 'integer',
        'ubigeo_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
