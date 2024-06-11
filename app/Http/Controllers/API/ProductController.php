<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{


    public function index()
    {
        $products = Product::with('categories', 'attributes')->get();

        $productData = $products->map(function ($product) {
            $product->image_url = $product->image ? url('/') . '/images/' . basename($product->image) : null;
            return $product;
        });

        return response()->json([
            'products' => $productData
        ]);
    }

    public function show($id)
    {
        try{
            $product = Product::with('categories', 'attributes')->findOrFail($id);
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            $product->image_url = $product->image ? url('/') . '/images/' . basename($product->image) : null;

            return response()->json($product);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'string|nullable',
            'image' => 'image|nullable|mimes:jpeg,png,jpg,gif,svg',
            'price' => 'required|numeric',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'attributes' => 'array',
            'attributes.*.key' => 'required|string',
            'attributes.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $imagePath = null;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imagePath = 'images/' . $imageName;
        }

        // Create the product
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'price' => $request->price,
        ]);

        if ($request->has('categories')) {
            $product->categories()->attach($request->categories);
        }

        if ($request->has('attributes')) {
            foreach ($request->input('attributes') as $attribute) {
                Attribute::create([
                    'product_id' => $product->id,
                    'key' => $attribute['key'],
                    'value' => $attribute['value'],
                ]);
            }
        }

        $product->image_url = $imagePath ? url('/') . '/images/' . $imageName : null;
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }


    public function getProducts(Request $request)
    {
        $query = Product::query();
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        if ($request->has('sort_by')) {
            if ($request->sort_by == 'newest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($request->sort_by == 'highest_price') {
                $query->orderBy('price', 'desc');
            } elseif ($request->sort_by == 'lowest_price') {
                $query->orderBy('price', 'asc');
            }
        }

        $products = $query->get();

        return response()->json($products, 200);
    }




}
