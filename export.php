<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Render the welcome view
$html = view('welcome')->render();

// Replace default local URLs with relative ones
$html = str_replace('http://localhost/', '', $html);
$html = str_replace('http://localhost:8000/', '', $html);
$html = str_replace('http://127.0.0.1:8000/', '', $html);

// Save to index.html at root
file_put_contents(__DIR__.'/index.html', $html);
echo "Successfully exported welcome view to index.html!\n";
