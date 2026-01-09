<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSecurity extends Model
{
    use HasFactory;

    protected $table = 'user_security';

    protected $fillable = [
        'user_id',
        'failed_attempts',
        'locked_until',
        'last_login_at',
        'last_ip',
        'last_user_agent',
        'last_failed_at',
        'last_failed_ip',
        'last_token_id',
        'device_fingerprint',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
