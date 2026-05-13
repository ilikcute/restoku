<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/{any}', function () {
    $path = public_path('app/index.html');

    if (file_exists($path)) {
        return file_get_contents($path);
    }

    return response()->json([
        'message' => 'Frontend build not found. Please run "npm run build" in the frontend directory.',
        'path_expected' => $path
    ], 404);
})->where('any', '^(?!api|storage|app).*$');
