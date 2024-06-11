<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    public function index(Request $request){
        $customer = Auth::user();
        $cart = Cart::where('customer_id', $customer->id)->with('items.product')->first();

        if ($cart) {
            return response()->json($cart, 200);
        }

        return response()->json(['message' => 'Cart is empty'], 200);
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $customer = Auth::user();
        $cart = Cart::firstOrCreate(['customer_id' => $customer->id]);
        $cartItem = $cart->items()->where('product_id', $request->product_id)->first();

        if($cartItem){
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        }else{
            $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Item added to cart'], 201);
    }


    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $customer = Auth::user();
        $cart = Cart::where('customer_id', $customer->id)->first();

        if($cart){
            $cartItem = $cart->items()->where('product_id', $request->product_id)->first();
            if($cartItem){
                $cartItem->delete();
                return response()->json(['message' => 'Product removed from cart'], 200);
            }
        }
        return response()->json(['message' => 'Product not found in cart'], 404);
    }

}
