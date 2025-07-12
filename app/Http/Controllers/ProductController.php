<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $user = auth()->user();

        $products = Product::with([
            'vendor',
            'product_files'
        ]);

        if ($user->role == 'vendor') {
            $products->where('vendor_id', $user->vendor->id);
        }

        if ($request->filled('search')) {
            $products = $products->whereRaw('LOWER(name) ILIKE ?', '%' . strtolower($request->search) . '%');
        }



        $sort = $request->get('sort', 'id');
        $orderBy = $request->get('order_by', 'desc');
        $limit = (int)$request->get('limit', 10);

        $products = $products->orderBy($sort, $orderBy)->paginate($limit);

        $products->getCollection()->transform(function ($product) {
            $product->product_files->transform(function ($file) {
                $file->link = route('show_file', ['path' => Crypt::encryptString($file->path)]);
                return $file;
            });
            return $product;
        });

        return ResponseFormatter::success($products, 'Product retrieved successfully', 200);

    }

    public function show($id)
    {
        $product = Product::with([
            'vendor',
            'product_files'
        ])->find($id);

        if (!$product) {
            return ResponseFormatter::error(null, 'Product not found', 404);
        }

        $product->product_files->transform(function ($file) {
            $file->link = route('show_file', ['path' => Crypt::encryptString($file->path)]);
            return $file;
        });

        return ResponseFormatter::success($product, 'Product retrieved successfully');

    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $user = auth()->user();

            if ($user->vendor->is_verified === false) {
                return ResponseFormatter::error(null, 'Vendor is not verified', 400);
            }

            $rules = [
                'name' => 'required|string',
                'price' => 'required|numeric|min:1',
                'quantity' => 'required|numeric|min:1',
                'files' => 'required|array',
                'files.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            ];

            $messages = [
                'required' => ':attribute is required.',
                'min' => ':attribute must be at least :min.',
                'numeric' => ':attribute must be a number.',
                'image' => ':attribute must be an image.',
                'mimes' => ':attribute must be a file of type: jpeg, png, jpg.',
            ];

            $attributes = [
                'name' => 'Product Name',
                'price' => 'Product Price',
                'quantity' => 'Product Quantity',
                'files' => 'Product Files',
            ];

            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->fails()) {
                DB::rollBack();
                return ResponseFormatter::error([
                    'errors' => $validator->errors()
                ], 'Please fill in the following form .', 422);
            }

            $product = new Product();
            $product->vendor_id = $user->vendor->id;
            $product->name = $request->name;
            $product->price = $request->price;
            $product->quantity = $request->quantity;
            $product->save();

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $changedName = time() . random_int(100, 999) . '.' . $file->getClientOriginalExtension();
                    $path = 'product/' . $product->id;
                    $file->storeAs($path, $changedName);

                    $product->product_files()->create([
                        'name' => $changedName,
                        'path' => $path . '/' . $changedName,
                        'ext' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            DB::commit();

            $product->product_files->transform(function ($file) {
                $file->link = route('show_file', ['path' => Crypt::encryptString($file->path)]);
                return $file;
            });

            return ResponseFormatter::success($product, 'Product has been successfuly created', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Something went wrong', 500);
        }
    }

    public function update(Request $request, $id)
    {

        DB::beginTransaction();
        try {


            $product = Product::with(['vendor', 'product_files'])->find($id);

            if (!$product) {
                return ResponseFormatter::error(null, 'Product not found', 404);
            }

            $rules = [
                'name' => 'required|string',
                'price' => 'required|numeric|min:1',
                'quantity' => 'required|numeric|min:1',
                'files' => 'nullable|array',
                'files.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ];

            $messages = [
                'required' => ':attribute is required.',
                'min' => ':attribute must be at least :min.',
                'numeric' => ':attribute must be a number.',
                'image' => ':attribute must be an image.',
                'mimes' => ':attribute must be a file of type: jpeg, png, jpg.',
            ];

            $attributes = [
                'name' => 'Product Name',
                'price' => 'Product Price',
                'quantity' => 'Product Quantity',
                'files' => 'Product Files',
                'files.*' => 'Product Files',
            ];

            $validator = Validator::make($request->all(), $rules, $messages, $attributes);

            if ($validator->fails()) {
                DB::rollBack();
                return ResponseFormatter::error([
                    'errors' => $validator->errors()
                ], 'Please fill in the following form .', 422);
            }

            $product->name = $request->name;
            $product->price = $request->price;
            $product->quantity = $request->quantity;
            $product->save();

            if ($request->hasFile('files')) {

                foreach ($product->product_files as $oldFile) {
                    if (Storage::disk('local')->exists($oldFile->path)) {
                        Storage::disk('local')->delete($oldFile->path);
                    }
                    $oldFile->delete();
                }
                foreach ($request->file('files') as $file) {
                    $changedName = time() . random_int(100, 999) . '.' . $file->getClientOriginalExtension();
                    $path = 'product/' . $product->id;
                    $file->storeAs($path, $changedName);

                    $product->product_files()->create([
                        'name' => $changedName,
                        'path' => $path . '/' . $changedName,
                        'ext' => $file->getClientOriginalExtension(),
                    ]);

                }
            }

            DB::commit();

            $product->product_files->transform(function ($file) {
                $file->link = route('show_file', ['path' => Crypt::encryptString($file->path)]);
                return $file;
            });

            return ResponseFormatter::success($product, 'Product has been successfuly updated', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Something went wrong', 500);

        }

    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::with(['vendor', 'product_files'])->find($id);
            if (!$product) {
                return ResponseFormatter::error(null, 'Product not found', 404);
            }
            $product->delete();
            DB::commit();
            return ResponseFormatter::success(null, 'Product has been successfully deleted', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Something went wrong', 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::with(['vendor', 'product_files'])->find($id);

            if (!$product) {
                return ResponseFormatter::error(null, 'Product not found', 404);
            }

            $product->is_active = !$product->is_active;
            $product->save();

            DB::commit();

            $product->product_files->transform(function ($file) {
                $file->link = route('show_file', ['path' => Crypt::encryptString($file->path)]);
                return $file;
            });


            return ResponseFormatter::success($product, 'Status Product has been successfuly updated', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Something went wrong', 500);
        }
    }
}
