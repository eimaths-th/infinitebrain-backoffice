<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$statuses = \App\Models\Statuswork::all();
foreach ($statuses as $status) {
    echo $status->id . ': ' . $status->name . PHP_EOL;
}
