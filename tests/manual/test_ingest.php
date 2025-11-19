<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/api/ingest',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_DEVICE_ID' => 'ESP_TEST_001',
            'HTTP_X_API_KEY' => '0EI6EExOGYzBPqGvEi8p5qoCcgWR6aAhueUJbb7q'
        ],
        json_encode([
            'readings' => [
                ['type' => 'soil', 'value' => 45.5],
                ['type' => 'temp', 'value' => 25.3],
                ['type' => 'hum', 'value' => 65.2]
            ]
        ])
    )
);

echo "Status: " . $response->getStatusCode() . PHP_EOL;
echo "Response: " . $response->getContent() . PHP_EOL;

$kernel->terminate($request, $response);
