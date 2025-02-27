<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    public function all()
    {
        $translations = __('translations');
        return response()->json($translations);
    }
}
