<?php

try {
    $dsn = "pgsql:host=db.anvhgmvuatjhvpzqssxj.supabase.co;port=5432;dbname=postgres;sslmode=require";
    $pdo = new PDO($dsn, "postgres", "zonita@26");
    echo "SUCCESS: Connected to Supabase PostgreSQL!\n";
    echo "Version: " . $pdo->query("SELECT version()")->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
