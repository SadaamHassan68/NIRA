<?php
/**
 * NIRA System - Citizen Registration
 * Somalia National Identification & Registration Authority
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = ['success' => false, 'message' => ''];
	
	try {
		// Validate required fields
		$required_fields = ['full_name', 'gender', 'dob', 'region', 'district', 'address'];
		foreach ($required_fields as $field) {
			if (empty($_POST[$field])) {
				throw new Exception("Please fill in all required fields.");
			}
		}
		
		// Generate unique NIN
		$nin = generateNIN();
		
		// Handle file uploads
		$photo_path = uploadFile('photo', 'photos');
		$birth_cert_path = uploadFile('birth_certificate', 'documents');
		$passport_path = uploadFile('passport', 'documents');
		$residency_proof_path = uploadFile('residency_proof', 'documents');
		
		// Insert citizen record
		$stmt = $pdo->prepare("
            INSERT INTO citizens (nin, full_name, gender, dob, region, district, address, 
                                phone, email, photo, birth_certificate, passport, residency_proof, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
		
		$stmt->execute([
			$nin,
			$_POST['full_name'],
			$_POST['gender'],
			$_POST['dob'],
			$_POST['region'],
			$_POST['district'],
			$_POST['address'],
			$_POST['phone'] ?? null,
			$_POST['email'] ?? null,
			$photo_path,
			$birth_cert_path,
			$passport_path,
			$residency_proof_path
		]);
		
		$response['success'] = true;
		$response['message'] = 'Registration submitted successfully! Your NIN is: ' . $nin;
		$response['nin'] = $nin;
		
	} catch (Exception $e) {
		$response['message'] = $e->getMessage();
	}
	
	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function generateNIN() {
	global $pdo;
	
	do {
		$year = date('Y');
		$random = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
		$nin = "SO-{$year}-{$random}";
		
		// Check if NIN already exists
		$stmt = $pdo->prepare("SELECT id FROM citizens WHERE nin = ?");
		$stmt->execute([$nin]);
	} while ($stmt->fetch());
	
	return $nin;
}

function uploadFile($field_name, $subfolder) {
	if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
		return null;
	}
	
	$file = $_FILES[$field_name];
	$upload_dir = __DIR__ . "/../assets/uploads/{$subfolder}/";
	$public_path = "assets/uploads/{$subfolder}/";
	
	// Create directory if it doesn't exist
	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}
	
	$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	$allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
	
	if (!in_array($file_extension, $allowed_extensions)) {
		throw new Exception("Invalid file type for {$field_name}. Only JPG, PNG, and PDF files are allowed.");
	}
	
	if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
		throw new Exception("File size too large for {$field_name}. Maximum 5MB allowed.");
	}
	
	$filename = uniqid() . '_' . time() . '.' . $file_extension;
	$file_path = $upload_dir . $filename;
	
	if (!move_uploaded_file($file['tmp_name'], $file_path)) {
		throw new Exception("Failed to upload {$field_name}.");
	}
	
	return $public_path . $filename;
}

// Get regions for dropdown
$regions = [
	'Awdal', 'Bakool', 'Banaadir', 'Bari', 'Bay', 'Galguduud', 
	'Gedo', 'Hiiraan', 'Jubbada Dhexe', 'Jubbada Hoose', 'Mudug', 
	'Nugaal', 'Sanaag', 'Shabeellaha Dhexe', 'Shabeellaha Hoose', 
	'Sool', 'Togdheer', 'Woqooyi Galbeed'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Citizen Registration - Somalia NIRA</title>
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
				<a class="nav-link" href="verify.php">
					<i class="fas fa-search me-1"></i>Verify ID
				</a>
			</div>
		</div>
	</nav>

	<div class="container my-5">
		<div class="row justify-content-center">
			<div class="col-lg-8">
				<div class="card shadow">
					<div class="card-header bg-primary text-white">
						<h3 class="card-title mb-0">
							<i class="fas fa-user-plus me-2"></i>
							Citizen Registration Form
						</h3>
					</div>
					<div class="card-body p-4">
						<form id="registrationForm" enctype="multipart/form-data" class="needs-validation" novalidate>
							<!-- Personal Information -->
							<div class="row mb-4">
								<div class="col-12">
									<h5 class="text-primary border-bottom pb-2">
										<i class="fas fa-user me-2"></i>Personal Information
									</h5>
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="full_name" class="form-label">Full Name *</label>
									<input type="text" class="form-control" id="full_name" name="full_name" required>
									<div class="invalid-feedback">Please provide your full name.</div>
								</div>
								<div class="col-md-6">
									<label for="gender" class="form-label">Gender *</label>
									<select class="form-select" id="gender" name="gender" required>
										<option value="">Select Gender</option>
										<option value="Male">Male</option>
										<option value="Female">Female</option>
										<option value="Other">Other</option>
									</select>
									<div class="invalid-feedback">Please select your gender.</div>
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="dob" class="form-label">Date of Birth *</label>
									<input type="date" class="form-control" id="dob" name="dob" required>
									<div class="invalid-feedback">Please provide your date of birth.</div>
								</div>
								<div class="col-md-6">
									<label for="phone" class="form-label">Phone Number</label>
									<input type="tel" class="form-control" id="phone" name="phone" 
									       placeholder="+252 61 123 4567" oninput="formatPhoneNumber(this)">
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="email" class="form-label">Email Address</label>
									<input type="email" class="form-control" id="email" name="email">
								</div>
								<div class="col-md-6">
									<label for="region" class="form-label">Region *</label>
									<select class="form-select" id="region" name="region" required>
										<option value="">Select Region</option>
										<?php foreach ($regions as $region): ?>
										<option value="<?php echo $region; ?>"><?php echo $region; ?></option>
										<?php endforeach; ?>
									</select>
									<div class="invalid-feedback">Please select your region.</div>
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="district" class="form-label">District *</label>
									<input type="text" class="form-control" id="district" name="district" required>
									<div class="invalid-feedback">Please provide your district.</div>
								</div>
								<div class="col-md-6">
									<label for="address" class="form-label">Address *</label>
									<textarea class="form-control" id="address" name="address" rows="2" required></textarea>
									<div class="invalid-feedback">Please provide your address.</div>
								</div>
							</div>
							
							<!-- Document Uploads -->
							<div class="row mb-4">
								<div class="col-12">
									<h5 class="text-primary border-bottom pb-2">
										<i class="fas fa-file-upload me-2"></i>Supporting Documents
									</h5>
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="photo" class="form-label">Passport Photo *</label>
									<input type="file" class="form-control" id="photo" name="photo" 
									       accept="image/*" data-preview="photo-preview" required>
									<div class="invalid-feedback">Please upload your passport photo.</div>
									<img id="photo-preview" class="img-thumbnail mt-2" style="display: none; max-width: 150px;">
								</div>
								<div class="col-md-6">
									<label for="birth_certificate" class="form-label">Birth Certificate</label>
									<input type="file" class="form-control" id="birth_certificate" name="birth_certificate" 
									       accept=".pdf,.jpg,.jpeg,.png">
								</div>
							</div>
							
							<div class="row mb-3">
								<div class="col-md-6">
									<label for="passport" class="form-label">Passport (if applicable)</label>
									<input type="file" class="form-control" id="passport" name="passport" 
									       accept=".pdf,.jpg,.jpeg,.png">
								</div>
								<div class="col-md-6">
									<label for="residency_proof" class="form-label">Residency Proof</label>
									<input type="file" class="form-control" id="residency_proof" name="residency_proof" 
									       accept=".pdf,.jpg,.jpeg,.png">
								</div>
							</div>
							
							<!-- Terms and Conditions -->
							<div class="row mb-4">
								<div class="col-12">
									<div class="form-check">
										<input class="form-check-input" type="checkbox" id="terms" required>
										<label class="form-check-label" for="terms">
											I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> 
											and confirm that all information provided is accurate.
										</label>
										<div class="invalid-feedback">You must agree to the terms and conditions.</div>
									</div>
								</div>
							</div>
							
							<!-- Submit Button -->
							<div class="row">
								<div class="col-12 text-center">
									<button type="submit" class="btn btn-primary btn-lg px-5">
										<i class="fas fa-paper-plane me-2"></i>Submit Registration
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Terms Modal -->
	<div class="modal fade" id="termsModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Terms and Conditions</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<h6>Data Collection and Usage</h6>
					<p>By registering with the Somalia NIRA system, you consent to the collection, storage, and processing of your personal and biometric data for the purpose of national identification and verification services.</p>
					
					<h6>Data Security</h6>
					<p>All personal and biometric data is encrypted and stored securely in compliance with Somalia Digital ID laws (Act No. 009 – 2023).</p>
					
					<h6>Verification Services</h6>
					<p>Your information may be used by authorized institutions for identity verification purposes through secure API endpoints.</p>
					
					<h6>Data Accuracy</h6>
					<p>You are responsible for providing accurate and up-to-date information. False information may result in rejection of your application.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script>
		// Handle form submission
		document.getElementById('registrationForm').addEventListener('submit', function(e) {
			e.preventDefault();
			
			const formData = new FormData(this);
			const submitBtn = this.querySelector('button[type="submit"]');
			
			showLoading(submitBtn);
			
			fetch('register.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				hideLoading(submitBtn);
				
				if (data.success) {
					showAlert(data.message, 'success');
					this.reset();
					document.getElementById('photo-preview').style.display = 'none';
					
					// Show NIN in a modal
					if (data.nin) {
						showNINModal(data.nin);
					}
				} else {
					showAlert(data.message, 'danger');
				}
			})
			.catch(error => {
				hideLoading(submitBtn);
				showAlert('An error occurred. Please try again.', 'danger');
				console.error('Error:', error);
			});
		});
		
		function showNINModal(nin) {
			const modal = new bootstrap.Modal(document.getElementById('ninModal'));
			document.getElementById('nin-display').textContent = nin;
			modal.show();
		}
	</script>

	<!-- NIN Display Modal -->
	<div class="modal fade" id="ninModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-success text-white">
					<h5 class="modal-title">
						<i class="fas fa-check-circle me-2"></i>Registration Successful
					</h5>
				</div>
				<div class="modal-body text-center">
					<i class="fas fa-id-card fa-4x text-success mb-3"></i>
					<h4>Your National Identification Number</h4>
					<div class="alert alert-info">
						<strong id="nin-display"></strong>
					</div>
					<p class="text-muted">Please save this number. You will need it for verification and ID card collection.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
