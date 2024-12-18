<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAllergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'allergy_id',
        'user_id',
    ];

    public function Users()
    {
        return $this->belongsTo(User::class);
    }

    public function allergy()
    {
        return $this->belongsTo(Allergy::class);
    }
}
