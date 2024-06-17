<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'discount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function findProductDiscountById($id)
    {
        return self::find($id);
    }
}
