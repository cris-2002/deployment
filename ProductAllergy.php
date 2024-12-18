<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAllergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'allergy_id',
        'product_id',
    ];

    public function Product()
    {
        return $this->belongsTo(Product::class);
    }

    public function allergy()
    {
        return $this->belongsTo(Allergy::class);
    }
}
