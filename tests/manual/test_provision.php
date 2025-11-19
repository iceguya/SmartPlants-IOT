<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/api/provision/claim',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'token' => 'DEMO-TOKEN-12345',
            'device_id' => 'ESP_TEST_001',
            'name' => 'Test Device',
            'location' => 'Lab'
        ])
    )
);

echo "Status: " . $response->getStatusCode() . PHP_EOL;
echo "Response: " . $response->getContent() . PHP_EOL;

$kernel->terminate($request, $response);
