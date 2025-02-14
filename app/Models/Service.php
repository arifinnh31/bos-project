<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Service extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'services';
    protected $fillable = [
        'nama_jasa',
        'harga_beli',
        'kategori_jasa',
        'satuan_perhitungan',
        'harga_jual'
    ];
}
