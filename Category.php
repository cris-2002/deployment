<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name', 'created_by', 'updated_by'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function creator_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
