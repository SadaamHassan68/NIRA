<?php
/**
 * NIRA System - Citizen Verification Portal
 * Somalia National Identification & Registration Authority
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Handle verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = ['success' => false, 'message' => '', 'data' => null];
	
	try {
		$nin = trim($_POST['nin']);
		$verification_type = $_POST['verification_type'] ?? 'web';
		
		if (empty($nin)) {
			throw new Exception('Please enter a National Identification Number.');
		}
		
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
			logVerification($nin, $verification_type, 'not_found');
			throw new Exception('Citizen not found or not approved.');
		}
		
		// Log successful verification
		logVerification($nin, $verification_type, 'success');
		
		$response['success'] = true;
		$response['message'] = 'Citizen verified successfully.';
		$response['data'] = $citizen;
		
	} catch (Exception $e) {
		$response['message'] = $e->getMessage();
	}
	
	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function logVerification($nin, $type, $result) {
	global $pdo;
	
	$stmt = $pdo->prepare("
        INSERT INTO verification_logs (nin, verifier_id, verification_type, ip_address, user_agent, result)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
	
	$verifier_id = $_SESSION['admin_id'] ?? null;
	$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
	
	$stmt->execute([$nin, $verifier_id, $type, $ip_address, $user_agent, $result]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Citizen Verification - Somalia NIRA</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		<div class="container">
			<a class="navbar-brand" href="index.php">
				<i class="fas fa-id-card me-2"></i>
				<strong>Somalia NIRA</strong>
			</a>
			<div class="navbar-nav ms-auto">
				<a class="nav-link" href="index.php">
					<i class="fas fa-home me-1"></i>Home
				</a>
				<a class="nav-link" href="register.php">
					<i class="fas fa-user-plus me-1"></i>Register
				</a>
				<?php if (isset($_SESSION['admin_id'])): ?>
				<a class="nav-link" href="dashboard.php">
					<i class="fas fa-tachometer-alt me-1"></i>Dashboard
				</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<div class="container my-5">
		<div class="row justify-content-center">
			<div class="col-lg-8">
				<div class="card shadow">
					<div class="card-header bg-primary text-white">
						<h3 class="card-title mb-0">
							<i class="fas fa-search me-2"></i>
							Citizen Verification Portal
						</h3>
					</div>
					<div class="card-body p-4">
						<div class="row">
							<div class="col-md-6">
								<h5 class="text-primary mb-3">Verify by NIN</h5>
								<form id="verificationForm" class="needs-validation" novalidate>
									<div class="mb-3">
										<label for="nin" class="form-label">National Identification Number</label>
										<input type="text" class="form-control" id="nin" name="nin" 
										       placeholder="SO-2024-123456" required>
										<div class="invalid-feedback">Please enter a valid NIN.</div>
									</div>
									
									<button type="submit" class="btn btn-primary w-100">
										<i class="fas fa-search me-2"></i>Verify Citizen
									</button>
								</form>
							</div>
							
							<div class="col-md-6">
								<h5 class="text-primary mb-3">QR Code Scanner</h5>
								<div class="text-center">
									<div class="qr-scanner-placeholder bg-light border rounded p-4 mb-3">
										<i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
										<p class="text-muted">QR Code Scanner</p>
										<button class="btn btn-outline-primary" id="qr-scanner-btn">
											<i class="fas fa-camera me-2"></i>Scan QR Code
										</button>
									</div>
									<small class="text-muted">
										Scan the QR code on the ID card for instant verification
									</small>
								</div>
							</div>
						</div>
						
						<!-- Verification Result -->
						<div id="verificationResult" class="mt-4" style="display: none;">
							<hr>
							<h5 class="text-primary mb-3">Verification Result</h5>
							<div id="resultContent"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- API Documentation -->
		<div class="row justify-content-center mt-5">
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header">
						<h5 class="mb-0">
							<i class="fas fa-code me-2"></i>API Documentation
						</h5>
					</div>
					<div class="card-body">
						<p class="text-muted mb-3">
							For institutions and third-party integrations, use our verification API:
						</p>
						
						<div class="bg-dark text-light p-3 rounded mb-3">
							<code>
								POST /api/verify.php<br>
								Content-Type: application/json<br><br>
								{<br>
								&nbsp;&nbsp;"nin": "SO-2024-123456"<br>
								}
							</code>
						</div>
						
						<div class="row">
							<div class="col-md-6">
								<h6>Success Response:</h6>
								<div class="bg-light p-3 rounded">
									<code>
										{<br>
										&nbsp;&nbsp;"success": true,<br>
										&nbsp;&nbsp;"message": "Citizen verified successfully",<br>
										&nbsp;&nbsp;"data": {<br>
										&nbsp;&nbsp;&nbsp;&nbsp;"nin": "SO-2024-123456",<br>
										&nbsp;&nbsp;&nbsp;&nbsp;"full_name": "John Doe",<br>
										&nbsp;&nbsp;&nbsp;&nbsp;"status": "approved"<br>
										&nbsp;&nbsp;}<br>
										}
									</code>
								</div>
							</div>
							<div class="col-md-6">
								<h6>Error Response:</h6>
								<div class="bg-light p-3 rounded">
									<code>
										{<br>
										&nbsp;&nbsp;"success": false,<br>
										&nbsp;&nbsp;"message": "Citizen not found"<br>
										}
									</code>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script>
		// Handle verification form submission
		document.getElementById('verificationForm').addEventListener('submit', function(e) {
			e.preventDefault();
			
			const formData = new FormData(this);
			formData.append('verification_type', 'web');
			const submitBtn = this.querySelector('button[type="submit"]');
			
			showLoading(submitBtn);
			
			fetch('verify.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				hideLoading(submitBtn);
				
				const resultDiv = document.getElementById('verificationResult');
				const resultContent = document.getElementById('resultContent');
				
				if (data.success) {
					const photoSrc = data.data.photo && data.data.photo.length ? data.data.photo : 'assets/images/default-avatar.png';
					resultContent.innerHTML = `
						<div class="alert alert-success">
							<i class="fas fa-check-circle me-2"></i>
							${data.message}
						</div>
						<div class="row">
							<div class="col-md-4">
								<img src="${photoSrc}" 
									 class="img-fluid rounded" alt="Citizen Photo">
							</div>
							<div class="col-md-8">
								<table class="table table-borderless">
									<tr>
										<td><strong>NIN:</strong></td>
										<td><code>${data.data.nin}</code></td>
									</tr>
									<tr>
										<td><strong>Name:</strong></td>
										<td>${data.data.full_name}</td>
									</tr>
									<tr>
										<td><strong>Gender:</strong></td>
										<td>${data.data.gender}</td>
									</tr>
									<tr>
										<td><strong>Date of Birth:</strong></td>
										<td>${formatDate(data.data.dob)}</td>
									</tr>
									<tr>
										<td><strong>Region:</strong></td>
										<td>${data.data.region}</td>
									</tr>
									<tr>
										<td><strong>District:</strong></td>
										<td>${data.data.district}</td>
									</tr>
									<tr>
										<td><strong>Status:</strong></td>
										<td><span class="badge bg-success">Approved</span></td>
									</tr>
								</table>
							</div>
						</div>
					`;
				} else {
					resultContent.innerHTML = `
						<div class="alert alert-danger">
							<i class="fas fa-exclamation-triangle me-2"></i>
							${data.message}
						</div>
					`;
				}
				
				resultDiv.style.display = 'block';
				resultDiv.scrollIntoView({ behavior: 'smooth' });
			})
			.catch(error => {
				hideLoading(submitBtn);
				showAlert('An error occurred. Please try again.', 'danger');
				console.error('Error:', error);
			});
		});
	</script>
</body>
</html>
