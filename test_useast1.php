<?php
try {
    $dsn = "pgsql:host=aws-0-us-east-1.pooler.supabase.com;port=6543;dbname=postgres;sslmode=require";
    $pdo = new PDO($dsn, "postgres.anvhgmvuatjhvpzqssxj", "zonita@26", [PDO::ATTR_TIMEOUT => 4]);
    echo "SUCCESS ON US-EAST-1!\n";
} catch (Exception $e) {
    echo "US-EAST-1 error: " . $e->getMessage() . "\n";
}
try {
    $dsn = "pgsql:host=aws-0-us-east-1.pooler.supabase.com;port=5432;dbname=postgres;sslmode=require";
    $pdo = new PDO($dsn, "postgres.anvhgmvuatjhvpzqssxj", "zonita@26", [PDO::ATTR_TIMEOUT => 4]);
    echo "SUCCESS ON US-EAST-1 PORT 5432!\n";
} catch (Exception $e) {
    echo "US-EAST-1 port 5432 error: " . $e->getMessage() . "\n";
}
