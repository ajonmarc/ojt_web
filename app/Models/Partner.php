<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Application;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'partnerName',
        'partnerAddress',
        'partnerPhone',
        'partnerEmail',
        'partnerContact',
        'status'
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
