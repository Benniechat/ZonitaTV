<?php

$regions = [
    'eu-central-1', 'eu-west-1', 'eu-west-2', 'eu-west-3',
    'us-east-1', 'us-east-2', 'us-west-1', 'us-west-2',
    'ap-southeast-1', 'ap-southeast-2', 'ap-northeast-1', 'ap-northeast-2', 'ap-south-1',
    'sa-east-1', 'ca-central-1'
];

foreach ($regions as $r) {
    $host = "aws-0-{$r}.pooler.supabase.com";
    try {
        $dsn = "pgsql:host={$host};port=6543;dbname=postgres;sslmode=require";
        $pdo = new PDO($dsn, "postgres.anvhgmvuatjhvpzqssxj", "zonita@26", [PDO::ATTR_TIMEOUT => 2]);
        echo "FOUND! Region is: {$r} on port 6543\n";
        exit(0);
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Tenant or user not found') === false && strpos($msg, 'timeout') === false) {
            echo "{$r} returned: {$msg}\n";
        }
    }
}
echo "Finished checking all regions.\n";
