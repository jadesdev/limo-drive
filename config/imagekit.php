<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ImageKit Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the ImageKit integration.
    |
    */

    'public_key' => env('IMAGEKIT_PUBLIC_KEY'),
    'private_key' => env('IMAGEKIT_PRIVATE_KEY'),
    'url_endpoint' => env('IMAGEKIT_URL_ENDPOINT'), // e.g. https://ik.imagekit.io/your_imagekit_id
];
