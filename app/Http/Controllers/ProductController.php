<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    // product
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required',
            'slug' => 'required|string|max:255|unique:products',
            'price' => 'required|integer',
            'active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::saveProduct($request->all());

        return response()->json(['product' => $product, 'message' => 'Product created successfully.'], 201);
    }
    public function deleteProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::findProductById($request->id);
        // print_r($product);
        // die;

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
    public function getAllProduct()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function getAllProductById($id)
    {
        $product = Product::findProductByIdWIthOthers($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    // images
    public function addProductImages(Request $request, $id)
    {
        // print_r($request->file('images'));
        // die;
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::findProductById($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }


        // save the image
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $filename = uniqid() . '_' . $image->getClientOriginalName();

            $image->move(storage_path('app/public/images'), $filename);

            $productImage = new ProductImage();
            $productImage->product_id = $product->id;
            $productImage->path = 'images/' . $filename;
            $productImage->save();
        }
        return response()->json(['message' => 'Images added successfully.']);
    }

    public function getAllProductImages()
    {
        $images = ProductImage::all();
        return response()->json($images);
    }
    public function deleteImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $productImage = ProductImage::findProductImageById($request->id);
        // print_r($productImage);
        // die;

        // check, product exists or not
        if (!$productImage) {
            return response()->json(['message' => 'Product Image not found'], 404);
        }
        $productImage->delete();

        // delete the image
        $filePath = $productImage->path;
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return response()->json(['message' => 'Product Image deleted successfully.']);
    }

    // discounts
    public function addProductDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'type' => 'required|in:percent,amount',
            'discount' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::findProductById($request->id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // check, discount already assigned or not for the product
        $existingDiscount = $product->discount;
        if ($existingDiscount) {
            return response()->json(['message' => 'Product already has a discount!'], 400);
        }

        // save it
        $productDiscount = new ProductDiscount();
        $productDiscount->product_id = $product->id;
        $productDiscount->type = $request->input('type');
        $productDiscount->discount = $request->input('discount');
        $productDiscount->save();

        return response()->json(['message' => 'Discount added successfully!']);
    }
    public function getAllProductDiscount()
    {
        $discounts = ProductDiscount::all();
        return response()->json($discounts);
    }
    public function deleteDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $productDiscount = ProductDiscount::findProductDiscountById($request->id);

        // check, product exists or not
        if (!$productDiscount) {
            return response()->json(['message' => 'Product Discount not found'], 404);
        }
        $productDiscount->delete();

        return response()->json(['message' => 'Product Discount deleted successfully.']);
    }

    // get all product info (main api)
    public function getProductInfo($id)
    {

        $product = Product::findProductByIdWIthOthers($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $response = $this->getInfo($product);

        return response()->json($response);
    }

    public function updateProductStatus($id, $status)
    {
        if (!in_array($status, ['0', '1'])) { //check if status is valid or not
            return response()->json(['message' => 'Invalid status value'], 400);
        }

        $product = Product::findProductById($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->active = $status;
        $product->save();

        return response()->json(['message' => 'Product status updated successfully']);
    }

    public function getProductInfoIfActive($id)
    {
        $product = Product::findActiveProductById($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found or inactive'], 404);
        }

        $response = $this->getInfo($product);

        return response()->json($response);
    }

    // get all active product
    public function getAllActiveProduct()
    {
        $products = Product::getActiveProducts();

        return response()->json($products);
    }

    // reusable function
    public function getInfo($product)
    {
        $discountedPrice = 0;
        if ($product->discount) {
            if ($product->discount->type === 'percent') {
                $discountedPrice = $product->price - ($product->price * $product->discount->discount / 100);
            } elseif ($product->discount->type === 'amount') {
                $discountedPrice = $product->price - $product->discount->discount;
            }
        }

        $response = [
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

        return $response;
    }
}
