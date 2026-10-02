<?php

// config/cors.php
return [
    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie', '*'], // Tambahkan bintang di akhir
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
