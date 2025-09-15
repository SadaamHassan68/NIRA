<?php
/**
 * NIRA System - Fingerprint Service
 * Somalia National Identification & Registration Authority
 * Fingerprint SDK integration for biometric capture and verification
 */

class FingerprintService {
    private $sdk_path;
    private $device_connected = false;
    
    public function __construct($sdk_path = null) {
        $this->sdk_path = $sdk_path ?: __DIR__ . '/sdk/';
    }
    
    /**
     * Initialize fingerprint device
     */
    public function initializeDevice() {
        try {
            // Check if device is connected
            $this->device_connected = $this->checkDeviceConnection();
            
            if (!$this->device_connected) {
                throw new Exception('Fingerprint device not connected');
            }
            
            return [
                'success' => true,
                'message' => 'Device initialized successfully',
                'device_info' => $this->getDeviceInfo()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Capture fingerprint template
     */
    public function captureFingerprint($quality_threshold = 50) {
        try {
            if (!$this->device_connected) {
                throw new Exception('Device not initialized');
            }
            
            // Simulate fingerprint capture (replace with actual SDK calls)
            $template = $this->simulateFingerprintCapture();
            
            if (!$template) {
                throw new Exception('Failed to capture fingerprint');
            }
            
            // Validate template quality
            $quality = $this->validateTemplateQuality($template);
            
            if ($quality < $quality_threshold) {
                throw new Exception('Fingerprint quality too low. Please try again.');
            }
            
            return [
                'success' => true,
                'message' => 'Fingerprint captured successfully',
                'template' => base64_encode($template),
                'quality' => $quality
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify fingerprint against stored template
     */
    public function verifyFingerprint($stored_template, $captured_template, $threshold = 0.8) {
        try {
            if (!$stored_template || !$captured_template) {
                throw new Exception('Invalid templates provided');
            }
            
            // Decode base64 templates
            $stored = base64_decode($stored_template);
            $captured = base64_decode($captured_template);
            
            // Calculate similarity score
            $similarity = $this->calculateSimilarity($stored, $captured);
            
            $is_match = $similarity >= $threshold;
            
            return [
                'success' => true,
                'is_match' => $is_match,
                'similarity' => $similarity,
                'confidence' => $similarity * 100
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if device is connected
     */
    private function checkDeviceConnection() {
        // Simulate device check (replace with actual SDK calls)
        // In real implementation, this would check USB/Serial connection
        return true; // For demo purposes
    }
    
    /**
     * Get device information
     */
    private function getDeviceInfo() {
        return [
            'device_type' => 'SecuGen Hamster Pro',
            'serial_number' => 'SG001234567',
            'firmware_version' => '1.2.3',
            'status' => 'Ready'
        ];
    }
    
    /**
     * Simulate fingerprint capture
     */
    private function simulateFingerprintCapture() {
        // Generate random template data (replace with actual SDK capture)
        $template = '';
        for ($i = 0; $i < 1024; $i++) {
            $template .= chr(rand(0, 255));
        }
        return $template;
    }
    
    /**
     * Validate template quality
     */
    private function validateTemplateQuality($template) {
        // Simulate quality check (replace with actual SDK quality assessment)
        return rand(60, 95);
    }
    
    /**
     * Calculate similarity between two templates
     */
    private function calculateSimilarity($template1, $template2) {
        // Simple similarity calculation (replace with actual SDK comparison)
        $length = min(strlen($template1), strlen($template2));
        $matches = 0;
        
        for ($i = 0; $i < $length; $i++) {
            if ($template1[$i] === $template2[$i]) {
                $matches++;
            }
        }
        
        return $matches / $length;
    }
    
    /**
     * Store fingerprint template in database
     */
    public function storeTemplate($citizen_id, $template) {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                UPDATE citizens 
                SET fingerprint_template = ? 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$template, $citizen_id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Fingerprint template stored successfully'
                ];
            } else {
                throw new Exception('Failed to store template');
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Retrieve fingerprint template from database
     */
    public function getTemplate($citizen_id) {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT fingerprint_template 
                FROM citizens 
                WHERE id = ? AND fingerprint_template IS NOT NULL
            ");
            
            $stmt->execute([$citizen_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'success' => true,
                    'template' => $result['fingerprint_template']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No fingerprint template found'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

// API endpoints for fingerprint service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    $fp_service = new FingerprintService();
    
    switch ($action) {
        case 'initialize':
            echo json_encode($fp_service->initializeDevice());
            break;
            
        case 'capture':
            $quality_threshold = $input['quality_threshold'] ?? 50;
            echo json_encode($fp_service->captureFingerprint($quality_threshold));
            break;
            
        case 'verify':
            $stored_template = $input['stored_template'] ?? '';
            $captured_template = $input['captured_template'] ?? '';
            $threshold = $input['threshold'] ?? 0.8;
            echo json_encode($fp_service->verifyFingerprint($stored_template, $captured_template, $threshold));
            break;
            
        case 'store':
            $citizen_id = $input['citizen_id'] ?? '';
            $template = $input['template'] ?? '';
            echo json_encode($fp_service->storeTemplate($citizen_id, $template));
            break;
            
        case 'get':
            $citizen_id = $input['citizen_id'] ?? '';
            echo json_encode($fp_service->getTemplate($citizen_id));
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
}
?>
