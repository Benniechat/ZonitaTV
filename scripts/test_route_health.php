<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$routes = [
    '/' => 'Home',
    '/comedy' => 'Comedy Showcase',
    '/nollywood' => 'Nollywood Cinema Showcase',
    '/islamic' => 'Islamic Television Showcase',
    '/gospel-cinema' => 'Gospel Cinema Showcase',
    '/category/christian-tv' => 'Christian TV Category',
    '/category/movies' => 'Movies Category',
    '/category/comedy' => 'Comedy Category',
    '/category/islamic' => 'Islamic Category',
    '/category/sports' => 'Sports Category',
    '/category/news' => 'News Category',
    '/category/kids' => 'Kids Category',
    '/category/education' => 'Education Category',
    '/channel/the-winlos' => 'The Winlos Channel Page',
    '/channel/zonita-comedy-central' => 'Zonita Comedy Central Page',
    '/channel/arewa24' => 'Arewa24 Channel Page',
];

echo "=== ROUTE HEALTH CHECK ===\n";
foreach ($routes as $uri => $label) {
    $request = Request::create($uri, 'GET');
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $content = $response->getContent();

    $checkStatus = ($status === 200) ? 'OK' : "FAIL ($status)";
    echo sprintf("[%s] %-35s -> %s (bytes: %d)\n", $checkStatus, $label, $uri, strlen($content));

    if ($uri === '/category/christian-tv') {
        if (str_contains($content, 'Africa TV3')) {
            echo "  [FAIL] Africa TV3 still visible in Christian TV category!\n";
        } else {
            echo "  [PASS] Africa TV3 correctly excluded from Christian TV category.\n";
        }
    }

    if ($uri === '/category/islamic') {
        if (str_contains($content, 'Arewa24') || str_contains($content, 'Zonita Arewa Movies') || str_contains($content, 'Zonita Islamic Cinema')) {
            echo "  [PASS] Islamic channels correctly present in Islamic Television category.\n";
        } else {
            echo "  [FAIL] Islamic channels missing in Islamic Television category!\n";
        }
    }

    if ($uri === '/category/comedy') {
        if (str_contains($content, 'The Winlos') || str_contains($content, 'Zonita Comedy Central') || str_contains($content, 'FAAN TV')) {
            echo "  [PASS] Remediated comedy channels correctly present in Comedy category.\n";
        } else {
            echo "  [FAIL] Remediated comedy channels missing in Comedy category!\n";
        }
    }

    if ($uri === '/category/movies') {
        if (str_contains($content, 'Nollywood Gold') || str_contains($content, 'Arewa24') || str_contains($content, 'Zonita African Cinema')) {
            echo "  [PASS] Remediated cinema channels correctly present in Movies category.\n";
        } else {
            echo "  [FAIL] Remediated cinema channels missing in Movies category!\n";
        }
    }

    $kernel->terminate($request, $response);
}
