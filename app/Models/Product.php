<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_product',
        'name',
        'category',
        'status',
        'description',
        'stock',
        'purchase_price',
        'selling_price',
        'image',
        'supplier_id'
    ];

    // Relasi ke supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi ke sales_detail
    public function salesDetails()
    {
        return $this->hasMany(SalesDetail::class);
    }
}
