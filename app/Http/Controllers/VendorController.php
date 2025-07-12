<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index(Request $request){

        $vendors = Vendor::with('user');

        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $vendors = $vendors->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) ILIKE ?', ["%{$search}%"]);
            });
        }

        $sort     = $request->get('sort', 'id');
        $orderBy  = $request->get('order_by', 'desc');
        $limit    = (int) $request->get('limit', 10);

        $vendors = $vendors->orderBy($sort, $orderBy)->paginate($limit);

        return ResponseFormatter::success($vendors, 'Vendors retrieved successfully', 200);

    }

    public function show($id){
        $vendor = Vendor::with('user')->find($id);
        if(!$vendor){
            return ResponseFormatter::error(null, 'Vendor not found', 404);
        }
        return ResponseFormatter::success($vendor, 'Vendor retrieved successfully');
    }

    public function store(Request $request)
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

            return ResponseFormatter::success(null, 'Vendor has been successfully added.', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'A connection error occurred.' . $e->getMessage() , 500);
        }
    }

    public function update(Request $request, $id){
        DB::beginTransaction();

        try {

            $vendor =  Vendor::with(['user'])->find($id);


            if(!$vendor){
                return ResponseFormatter::error(null, 'Vendor not found.', 404);
            }


            $rules = [
                'username' => 'required|string|unique:users,username,' . $vendor->user_id,
                'password' => 'required|string|confirmed|min:8',
                'password_confirmation' => 'required|string|min:8',
                'type' => 'required|in:CV,PT',
                'address' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|string|email|unique:vendors,email,' . $vendor->id,
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

            $user = $vendor->user;

            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->save();

            $vendor->type = $request->type;
            $vendor->name = $request->name;
            $vendor->phone = $request->phone;
            $vendor->email = $request->email;
            $vendor->address = $request->address;
            $vendor->save();

            DB::commit();

            return ResponseFormatter::success($vendor, 'Vendor has been successfully updated.', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'A connection error occurred.' . $e->getMessage() , 500);
        }

    }

    public function destroy($id){
        DB::beginTransaction();
        try{

            $vendor =  Vendor::with(['user'])->find($id);
            if(!$vendor){
                return ResponseFormatter::error(null, 'Vendor not found.', 404);
            }

            $vendor->delete();
            $vendor->user->delete();
            DB::commit();

            return ResponseFormatter::success(null, 'Vendor has been succesfully deleted', 200);

        }catch (\Exception $e){
            DB::rollBack();
            return ResponseFormatter::error(null, 'A connection error occurred.'. $e->getMessage(), 500);
        }
    }
    public function vendorVerification($id)
    {
        DB::beginTransaction();
        try {
            $vendor = Vendor::where('id', $id)->where('is_verified', false)->first();

            if (!isset($vendor)) {
                return ResponseFormatter::error(null, 'Vendor not found.', 404);
            }

            $vendor->is_verified = true;
            $vendor->verified_at = now();
            $vendor->save();
            DB::commit();

            return ResponseFormatter::success($vendor, 'Vendor Successfully Verified', 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'A connection error occurred.' . $e->getMessage(), 500);
        }
    }

    public function updateStatus($id){
        DB::beginTransaction();

        try{

            $vendor =  Vendor::with(['user'])->find($id);

            if(!$vendor){
                return ResponseFormatter::error(null, 'Vendor not found.', 404);
            }

            $vendor->is_active = !$vendor->is_active;
            $vendor->save();
            DB::commit();

            return ResponseFormatter::success($vendor, 'Status has been successfully updated', 200);

        }catch (\Exception $e){
            DB::rollBack();
            return ResponseFormatter::error(null, 'A connection error occurred.' . $e->getMessage(), 500);
        }
    }

}

