<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'stock',
        'msku',
        'barcode',
        'combinations'
    ];

    protected $casts = [
        'combinations' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
