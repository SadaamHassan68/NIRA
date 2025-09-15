<?php
/**
 * NIRA System - Landing Page
 * Somalia National Identification & Registration Authority
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
	header('Location: dashboard.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Somalia NIRA - National ID System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		<div class="container">
			<a class="navbar-brand" href="#">
				<i class="fas fa-id-card me-2"></i>
				<strong>Somalia NIRA</strong>
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item">
						<a class="nav-link" href="register.php">
							<i class="fas fa-user-plus me-1"></i>Register
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="verify.php">
							<i class="fas fa-search me-1"></i>Verify ID
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="login.php">
							<i class="fas fa-sign-in-alt me-1"></i>Admin Login
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<!-- Hero Section -->
	<section class="hero-section bg-gradient-primary text-white py-5">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-6">
					<h1 class="display-4 fw-bold mb-4">
						Somalia National Identification & Registration Authority
					</h1>
					<p class="lead mb-4">
						Secure digital identity management system for all Somali citizens and legal residents. 
						Get your National Identification Number (NIN) and digital ID card.
					</p>
					<div class="d-flex gap-3">
						<a href="register.php" class="btn btn-light btn-lg">
							<i class="fas fa-user-plus me-2"></i>Register Now
						</a>
						<a href="verify.php" class="btn btn-outline-light btn-lg">
							<i class="fas fa-search me-2"></i>Verify ID
						</a>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="text-center">
						<i class="fas fa-id-card fa-10x opacity-75"></i>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Features Section -->
	<section class="py-5">
		<div class="container">
			<div class="row text-center mb-5">
				<div class="col-12">
					<h2 class="display-5 fw-bold">System Features</h2>
					<p class="lead text-muted">Comprehensive digital identity management</p>
				</div>
			</div>
			<div class="row g-4">
				<div class="col-md-4">
					<div class="card h-100 border-0 shadow-sm">
						<div class="card-body text-center p-4">
							<div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3">
								<i class="fas fa-fingerprint fa-2x"></i>
							</div>
							<h5 class="card-title">Biometric Security</h5>
							<p class="card-text">Advanced fingerprint and facial recognition technology for secure identity verification.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card h-100 border-0 shadow-sm">
						<div class="card-body text-center p-4">
							<div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3">
								<i class="fas fa-qrcode fa-2x"></i>
							</div>
							<h5 class="card-title">Digital ID Cards</h5>
							<p class="card-text">Generate secure digital and physical ID cards with QR codes for instant verification.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card h-100 border-0 shadow-sm">
						<div class="card-body text-center p-4">
							<div class="feature-icon bg-info text-white rounded-circle mx-auto mb-3">
								<i class="fas fa-shield-alt fa-2x"></i>
							</div>
							<h5 class="card-title">Secure Verification</h5>
							<p class="card-text">Real-time verification services for institutions with API integration capabilities.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Statistics Section -->
	<section class="bg-light py-5">
		<div class="container">
			<div class="row text-center">
				<div class="col-md-3">
					<div class="stat-item">
						<h3 class="display-4 fw-bold text-primary" id="total-citizens">0</h3>
						<p class="text-muted">Registered Citizens</p>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stat-item">
						<h3 class="display-4 fw-bold text-success" id="total-ids">0</h3>
						<p class="text-muted">ID Cards Issued</p>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stat-item">
						<h3 class="display-4 fw-bold text-info" id="total-verifications">0</h3>
						<p class="text-muted">Verifications Today</p>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stat-item">
						<h3 class="display-4 fw-bold text-warning" id="active-regions">0</h3>
						<p class="text-muted">Active Regions</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Footer -->
	<footer class="bg-dark text-white py-4">
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<h5>Somalia NIRA</h5>
					<p class="mb-0">National Identification & Registration Authority</p>
					<p class="mb-0">Building a secure digital identity for Somalia</p>
				</div>
				<div class="col-md-6 text-md-end">
					<p class="mb-0">&copy; 2024 Somalia NIRA. All rights reserved.</p>
					<p class="mb-0">Compliant with Act No. 009 – 2023</p>
				</div>
			</div>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/main.js"></script>
</body>
</html>
