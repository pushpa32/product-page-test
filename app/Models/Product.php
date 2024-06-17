<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'price',
        'active',
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function discount()
    {
        return $this->hasOne(ProductDiscount::class);
    }

    // create a row to save
    public static function saveProduct($data)
    {
        return self::create($data);
    }

    public static function findProductByIdWIthOthers($productId)
    {
        return self::with(['images', 'discount'])->find($productId);
    }

    public static function findProductById($productId)
    {
        return self::find($productId);
    }

    public static function getActiveProducts()
    {
        return self::with(['images', 'discount'])->where('active', true)->get()->map(function($product) {
            $discountedPrice = 0;
            if ($product->discount) {
                if ($product->discount->type === 'percent') {
                    $discountedPrice = $product->price - ($product->price * $product->discount->discount / 100);
                } elseif ($product->discount->type === 'amount') {
                    $discountedPrice = $product->price - $product->discount->discount;
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'slug' => $product->slug,
                'price' => [
                    'full' => $product->price,
                    'discounted' => $discountedPrice,
                ],
                'discount' => $product->discount ? [
                    'type' => $product->discount->type,
                    'amount' => $product->discount->discount,
                ] : null,
                'images' => $product->images->pluck('path')->toArray(),
            ];
        });
    }

    public static function getActiveProductsById($prId)
    {
        return self::with(['images', 'discount'])->where('id', $prId)->where('active', true)->get()->map(function($product) {
            $discountedPrice = 0;
            if ($product->discount) {
                if ($product->discount->type === 'percent') {
                    $discountedPrice = $product->price - ($product->price * $product->discount->discount / 100);
                } elseif ($product->discount->type === 'amount') {
                    $discountedPrice = $product->price - $product->discount->discount;
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'slug' => $product->slug,
                'price' => [
                    'full' => $product->price,
                    'discounted' => $discountedPrice,
                ],
                'discount' => $product->discount ? [
                    'type' => $product->discount->type,
                    'amount' => $product->discount->discount,
                ] : null,
                'images' => $product->images->pluck('path')->toArray(),
            ];
        });
    }

    public static function findActiveProductById($productId)
    {
        return self::with(['images', 'discount'])
                    ->where('id', $productId)
                    ->where('active', true)
                    ->first();
    }
}
