<?php

$regions = [
    'eu-central-1', // Frankfurt (very common for European / African latency)
    'eu-west-1',    // Ireland
    'eu-west-2',    // London
    'eu-west-3',    // Paris
    'us-east-1',    // N. Virginia
    'us-east-2',    // Ohio
    'us-west-1',    // N. California
    'us-west-2',    // Oregon
    'ap-southeast-1', // Singapore
    'af-south-1',   // Cape Town
];

$user = "postgres.anvhgmvuatjhvpzqssxj";
$pass = "zonita@26";
$db = "postgres";

foreach ($regions as $r) {
    $host = "aws-0-{$r}.pooler.supabase.com";
    echo "Testing $r ($host:5432)... ";
    try {
        $dsn = "pgsql:host={$host};port=5432;dbname={$db};sslmode=require";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
        echo "SUCCESS! Connected via $r!\n";
        echo "Version: " . $pdo->query("SELECT version()")->fetchColumn() . "\n";
        break;
    } catch (Exception $e) {
        echo "Failed: " . substr($e->getMessage(), 0, 80) . "\n";
    }
}
