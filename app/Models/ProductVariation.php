<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class ProductVariation extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ginee_id',
        'product_id',
        'name',
        'purchase_price',
        'price',
        'stock',
        'msku',
        'barcode',
        'combinations',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'combinations' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
            if (empty($model->updated_at)) {
                $model->updated_at = now();
            }
        });

        static::updating(function ($model) {
            if (empty($model->updated_at)) {
                $model->updated_at = now();
            }
        });
    }
}
