<?php
$candidates = ['us-east-2', 'ap-southeast-1', 'ap-northeast-1', 'ap-south-1', 'ca-central-1'];
foreach ($candidates as $r) {
    echo "Checking {$r}... ";
    try {
        $dsn = "pgsql:host=aws-0-{$r}.pooler.supabase.com;port=6543;dbname=postgres;sslmode=require";
        $pdo = new PDO($dsn, "postgres.anvhgmvuatjhvpzqssxj", "zonita@26", [PDO::ATTR_TIMEOUT => 3]);
        echo "MATCH! Region is: {$r}\n";
        break;
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
