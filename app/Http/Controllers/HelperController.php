<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class HelperController extends Controller
{
    public function image($path)
    {
        try {
            $decryptedPath = Crypt::decryptString($path);
            if (Storage::disk('local')->exists($decryptedPath)) {
                return response()->file(Storage::disk('local')->path($decryptedPath));
            }

        } catch (\Throwable $th) {
            abort(404);
        }

        abort(404);
    }
}
