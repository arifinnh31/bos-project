<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'is_ginee',
        'ginee_id',
        'name',
        'spu',
        'full_category_id',
        'full_category_name',
        'brand',
        'sale_status',
        'condition',
        'has_shelf_life',
        'shelf_life_duration',
        'inbound_limit',
        'outbound_limit',
        'min_purchase',
        'short_description',
        'description',
        'has_variations',
        'variant_options',
        'images',
        'length',
        'width',
        'height',
        'weight',
        'preorder',
        'preorder_duration',
        'preorder_unit',
        'customs_chinese_name',
        'customs_english_name',
        'hs_code',
        'invoice_amount',
        'gross_weight',
        'source_url',
        'purchase_duration',
        'purchase_unit',
        'sales_tax_amount',
        'remarks1',
        'remarks2',
        'remarks3',
        'sold',
        'review',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'full_category_id' => 'array',
        'full_category_name' => 'array',
        'variant_options' => 'array',
        'images' => 'array',
    ];

    public function productVariations()
    {
        return $this->hasMany(ProductVariation::class);
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
