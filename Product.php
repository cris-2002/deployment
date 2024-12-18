<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'calories', 'images', 'stock', 'created_by', 'updated_by', 'status', 'sku', 'barcode'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function order_product()
    {
        return $this->hasMany(Order_product::class);
    }

    public function product_allergies()
    {
        return $this->hasMany(ProductAllergy::class);
    }
}
