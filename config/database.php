<?php
/**
 * NIRA System Database Configuration and PDO Bootstrap
 * Somalia National Identification & Registration Authority
 */

// Basic env-style configuration (edit as needed or load from .env)
$db_host = getenv('NIRA_DB_HOST') ?: '127.0.0.1';
$db_port = getenv('NIRA_DB_PORT') ?: '3306';
$db_name = getenv('NIRA_DB_NAME') ?: 'nira_system';
$db_user = getenv('NIRA_DB_USER') ?: 'root';
$db_pass = getenv('NIRA_DB_PASS') ?: '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$db_charset}";
$options = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
];

try {
	$pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (Throwable $e) {
	http_response_code(500);
	echo 'Database connection failed.';
	if (php_sapi_name() === 'cli-server') {
		// Show detail in local dev server
		echo "\n" . $e->getMessage();
	}
	exit;
}

// Helper: ensure uploads directory exists
$uploadsRoot = __DIR__ . '/../assets/uploads';
if (!is_dir($uploadsRoot)) {
	@mkdir($uploadsRoot, 0755, true);
}
