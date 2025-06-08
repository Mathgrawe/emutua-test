<?php

return [
    'host' => env('OPENSEARCH_HOST', 'localhost'),
    'port' => env('OPENSEARCH_PORT', 9200),
    'scheme' => env('OPENSEARCH_SCHEME', 'http'),
    'user' => env('OPENSEARCH_USER', ''),
    'pass' => env('OPENSEARCH_PASS', ''),
];