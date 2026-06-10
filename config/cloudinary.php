<?php

return [
    'cloudinary_url' => env('CLOUDINARY_URL'),

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    'gallery_folder' => env('CLOUDINARY_GALLERY_FOLDER', 'sendang-gile/galleries'),
];
