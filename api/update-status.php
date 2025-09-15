<?php
/**
 * NIRA System - Update Application Status API
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

// Only admin or officer can update status
$role = $_SESSION['admin_role'] ?? null;
if (!$role || !in_array($role, ['admin', 'officer'], true)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Unauthorized']);
	exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$nin = $payload['nin'] ?? '';
$status = $payload['status'] ?? '';

if (!$nin || !in_array($status, ['approved', 'rejected'], true)) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
	exit;
}

try {
	$stmt = $pdo->prepare('UPDATE citizens SET status = ? WHERE nin = ?');
	$stmt->execute([$status, $nin]);

	if ($stmt->rowCount() === 0) {
		echo json_encode(['success' => false, 'message' => 'Citizen not found']);
		exit;
	}

	echo json_encode(['success' => true, 'message' => 'Status updated']);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Server error']);
}
