<?php

use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(AkademikRefService::class);
    $response = $service->sendRequest('GetCountMataKuliah'); // Call sendRequest directly to bypass getCountMataKuliah casting

    echo "Raw Response Type: " . gettype($response) . "\n";
    echo "Raw Response Content: \n";
    print_r($response);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
