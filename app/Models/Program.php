<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Program extends Model
{
    protected $fillable = [
        'programName',
        'programDescription',
    ];

    protected $table = 'programs'; 
    protected $primaryKey = 'id';

    /**
     * Get the users enrolled in this program.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'ojtProgram', 'programName');
    }
}
