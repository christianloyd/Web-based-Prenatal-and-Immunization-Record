<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vite Manifest Path
    |--------------------------------------------------------------------------
    |
    | This value determines the path to the Vite manifest file within your
    | public directory. Vite 7+ uses .vite/manifest.json by default.
    |
    */

    'manifest' => env('VITE_MANIFEST', '.vite/manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | Build Path
    |--------------------------------------------------------------------------
    |
    | The build path where Vite will output your bundled assets.
    |
    */

    'build_path' => env('VITE_BUILD_PATH', 'build'),

    /*
    |--------------------------------------------------------------------------
    | Hot File Path
    |--------------------------------------------------------------------------
    |
    | The path to the Vite "hot" file that indicates the dev server is running.
    |
    */

    'hot_file' => env('VITE_HOT_FILE', public_path('hot')),

];