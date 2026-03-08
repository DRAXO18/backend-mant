<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToCompany;


class Admin extends Model
{
    use SoftDeletes;
    use BelongsToCompany;


    protected $fillable = [
        'user_id',
        'uid',
        'company_id',

    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
