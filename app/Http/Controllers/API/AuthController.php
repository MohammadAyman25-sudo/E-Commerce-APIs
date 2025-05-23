<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => "Validation Error",
                "data" => $validator->errors()->all()
            ], 400);
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
        ]);

        $response = [];
        $response['token'] = $user->createToken("myApp")->accessToken;
        $response['user'] = $user->name;
        $response['email'] = $user->email;

        return response()->json([
            "status" => 200,
            "message" => "User Registered",
            "data" => $response,
        ]);
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => "Invalid Credentials",
                "data" => $validator->errors()->all(),
            ], 400);
        }
        if (Auth::attempt(["email"=>$request->email, "password" => $request->password], $request->boolean('remember_me'))){
            $user = Auth::user();
            $response = [];
            $response['token'] = $user->createToken("myApp")->accessToken;
            $response['user'] = $user->name;
            $response['email'] = $user->email;

            return response()->json([
                "status" => 200,
                "message" => "User Authenticated",
                "data" => $response,
            ]);
        }
        return response()->json([
            'status' => 401,
            'message' => "User Unauthenticated",
            'data' => null
        ], 401);
    }

    public function logout(string $id) {
        if (auth()->id() != $id) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized Action.',
                'data' => null,
            ], 403);
        }
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => 200,
            'message' => "User Logged Out",
            'data' => null,
        ]);
    }
}
