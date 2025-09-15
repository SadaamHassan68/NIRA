<?php
/**
 * NIRA System - Verification API
 * Somalia National Identification & Registration Authority
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['nin'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. NIN is required.'
    ]);
    exit();
}

$nin = trim($input['nin']);
$api_key = $input['api_key'] ?? null;

// Validate NIN format
if (!preg_match('/^SO-\d{4}-\d{6}$/', $nin)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid NIN format. Expected format: SO-YYYY-NNNNNN'
    ]);
    exit();
}

try {
    // Get citizen information
    $stmt = $pdo->prepare("
        SELECT nin, full_name, gender, dob, region, district, address, 
               photo, status, created_at
        FROM citizens 
        WHERE nin = ? AND status = 'approved'
    ");
    $stmt->execute([$nin]);
    $citizen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$citizen) {
        // Log failed verification
        logVerification($nin, 'api', 'not_found', $api_key);
        
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Citizen not found or not approved.'
        ]);
        exit();
    }
    
    // Log successful verification
    logVerification($nin, 'api', 'success', $api_key);
    
    // Remove sensitive data
    unset($citizen['address']);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Citizen verified successfully.',
        'data' => $citizen,
        'timestamp' => date('Y-m-d H:i:s'),
        'verification_id' => uniqid('VER_')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error. Please try again later.'
    ]);
    
    // Log error
    error_log("Verification API Error: " . $e->getMessage());
}

function logVerification($nin, $type, $result, $api_key = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO verification_logs (nin, verifier_id, verification_type, ip_address, user_agent, result)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $verifier_id = null; // API calls don't have a specific verifier
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt->execute([$nin, $verifier_id, $type, $ip_address, $user_agent, $result]);
    } catch (Exception $e) {
        error_log("Failed to log verification: " . $e->getMessage());
    }
}
?>
