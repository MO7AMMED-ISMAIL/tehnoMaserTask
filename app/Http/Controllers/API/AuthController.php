<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\Admin;

class AuthController extends Controller
{
    public function customerRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:customers,username',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $customer = Customer::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            return response()->json(['message' => 'Customer registered successfully'], 201);
        }catch(\Exception $e){
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function adminRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:admins,username',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $admin = Admin::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            return response()->json(['message' => 'Admin registered successfully'], 201);
        }catch(\Exception $e){
            return response()->json(['message' => 'Something went wrong'.$e->getMessage()], 500);
        }
    }



    public function customerLogin(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $customer = Customer::where('username', $request->username)->first();

            if (!$customer || !Hash::check($request->password, $customer->password)) {
                return response()->json(['message' => 'Invalid Username or Password'], 401);
            }

            $token = $customer->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Customer login successful',
                'token' => $token
            ], 200);

        }catch(\Exception $e){
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }


    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid Username or Password'], 401);
        }

        $token = $admin->createToken('authToken')->plainTextToken;

        return response()->json(['message' => 'Admin login successful', 'token' => $token], 200);
    }
}
