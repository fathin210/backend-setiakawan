<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormatter;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return ResponseFormatter::error($validator->errors(), "Login Admin Gagal", 422);
        }

        if(!$token = auth("admin")->attempt($validator->validated())){
            return ResponseFormatter::error(['error' => 'Unauthorized'], "Login Admin Gagal", 401);
        }

        return $this->createNewToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:admin,username',
            'password' => 'required',
            'nama' => 'required'
        ]);

        if($validator->fails()){
            return ResponseFormatter::error($validator->errors(), "Register Admin Gagal", 422);
        }

        $admin = Admin::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return ResponseFormatter::success($admin, "Admin successfully registered");
    }

    public function logout()
    {
        if(auth()->guard('admin')->check()){  
            auth()->guard('admin')->logout();
        }
        return response()->json(['message' => 'Admin successfully signed out']);
    }

    public function adminProfile()
    {
        return response()->json(auth("admin")->user());
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth("admin")->factory()->getTTL() * 60,
            'admin' => auth("admin")->user()
        ]);
    }
    
    public function index()
    {
        try {
            $admin = Admin::all();
            return ResponseFormatter::success($admin, "List Data Admin");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }
}
