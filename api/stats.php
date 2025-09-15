<?php
/**
 * NIRA System - Statistics API
 * Somalia National Identification & Registration Authority
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    $stats = [];
    
    // Total citizens
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens");
    $stats['total_citizens'] = (int)$stmt->fetch()['total'];
    
    // Total ID cards issued
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM id_card_logs");
    $stats['total_ids'] = (int)$stmt->fetch()['total'];
    
    // Today's verifications
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM verification_logs WHERE DATE(created_at) = CURDATE()");
    $stats['total_verifications'] = (int)$stmt->fetch()['total'];
    
    // Active regions
    $stmt = $pdo->query("SELECT COUNT(DISTINCT region) as total FROM citizens WHERE status = 'approved'");
    $stats['active_regions'] = (int)$stmt->fetch()['total'];
    
    // Pending applications
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens WHERE status = 'pending'");
    $stats['pending_applications'] = (int)$stmt->fetch()['total'];
    
    // Approved applications
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens WHERE status = 'approved'");
    $stats['approved_citizens'] = (int)$stmt->fetch()['total'];
    
    // Rejected applications
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens WHERE status = 'rejected'");
    $stats['rejected_applications'] = (int)$stmt->fetch()['total'];
    
    // Regional distribution
    $stmt = $pdo->query("
        SELECT region, COUNT(*) as count 
        FROM citizens 
        WHERE status = 'approved' 
        GROUP BY region 
        ORDER BY count DESC
    ");
    $stats['regional_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly registrations (last 12 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM citizens 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $stats['monthly_registrations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gender distribution
    $stmt = $pdo->query("
        SELECT gender, COUNT(*) as count 
        FROM citizens 
        WHERE status = 'approved' 
        GROUP BY gender
    ");
    $stats['gender_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent verifications
    $stmt = $pdo->query("
        SELECT nin, result, created_at
        FROM verification_logs 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stats['recent_verifications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve statistics.',
        'error' => $e->getMessage()
    ]);
    
    error_log("Stats API Error: " . $e->getMessage());
}
?>
