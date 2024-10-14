<?php

return [
    'hosts' => array_map('trim', explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200'))),
    'user' => env('ELASTICSEARCH_USER', ''),
    'pass' => env('ELASTICSEARCH_PASS', ''),
];
