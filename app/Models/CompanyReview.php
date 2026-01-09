<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'action',
        'reason',
        'performed_by',
    ];

    /**
     * Empresa relacionada
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Usuario RUBRO que realizó la acción
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
