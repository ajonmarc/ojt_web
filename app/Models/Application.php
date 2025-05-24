<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'status',
        'remarks',
        'application_date',
        'start_date',
        'end_date',
        'required_hours',
        'completed_hours',
        'resume_path',
        'letter_path',
        'preferred_company'
    ];

    protected $casts = [
        'application_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
