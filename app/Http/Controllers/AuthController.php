<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute is required',
        ];

        $attributes = [
            'username' => 'Username',
            'password' => 'Password',
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return ResponseFormatter::error([
                'errors' => $validator->errors()
            ], 'Please fill in the required fields.', 422);
        }

        $credentials = $request->only('username', 'password');

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ResponseFormatter::error(null, 'Invalid credentials.', 401);
        }

        if (!$user->is_active) {
            return ResponseFormatter::error(null, 'User is not active.', 401);
        }


        return ResponseFormatter::success([
            'user' => $user,
            'token' => $user->createToken('authToken')->plainTextToken,
        ], 'Login successful.', 200);
    }

    public function register(Request $request)
    {
        DB::beginTransaction();

        try {
            $rules = [
                'username' => 'required|string|unique:users,username',
                'password' => 'required|string|confirmed|min:8',
                'password_confirmation' => 'required|string|min:8',
                'type' => 'required|in:CV,PT',
                'address' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|string|email|unique:vendors,email',
                'name' => 'required|string',
            ];

            $messages = [
                'required' => ':attribute is required',
                'confirmed' => 'Password confirmation does not match',
                'min' => ':attribute must be at least 8 characters',
                'unique' => ':attribute has already been taken',
                'email' => 'Invalid email format',
                'string' => ':attribute must be a string',
                'in' => ':attribute must be either CV or PT',
            ];

            $attributes = [
                'username' => 'Username',
                'password' => 'Password',
                'password_confirmation' => 'Password Confirmation',
                'type' => 'Type',
                'address' => 'Address',
                'phone' => 'Phone',
                'email' => 'Email',
                'name' => 'Name',
            ];

            $validator = Validator::make($request->all(), $rules, $messages, $attributes);

            if ($validator->fails()) {
                DB::rollBack();
                return ResponseFormatter::error([
                    'errors' => $validator->errors()
                ], 'Please fill in the required fields.', 422);
            }

            $user = new User();
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->role = 'vendor';

            $user->save();

            $vendor = new Vendor();
            $vendor->user_id = $user->id;
            $vendor->type = $request->type;
            $vendor->name = $request->name;
            $vendor->phone = $request->phone;
            $vendor->email = $request->email;
            $vendor->address = $request->address;
            $vendor->save();

            DB::commit();

            return ResponseFormatter::success(null, 'Registration successful.', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'A connection error occurred.' . $e->getMessage() , 500);
        }
    }
}
