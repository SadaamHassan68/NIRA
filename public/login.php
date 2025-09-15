<?php
/**
 * NIRA System - Admin Login
 * Somalia National Identification & Registration Authority
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
	header('Location: dashboard.php');
	exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = ['success' => false, 'message' => ''];
	
	try {
		// Verify CSRF token
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			throw new Exception('Security validation failed. Please refresh the page and try again.');
		}
		
		// Brute force protection - check for too many failed attempts
		if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5 && 
			time() - $_SESSION['last_attempt_time'] < 900) { // 15 minutes lockout
			throw new Exception('Too many failed login attempts. Please try again later.');
		}
		
		$username = trim($_POST['username']);
		$password = $_POST['password'];
		
		if (empty($username) || empty($password)) {
			throw new Exception('Please enter both username and password.');
		}
		
		// Get admin user
		$stmt = $pdo->prepare("
            SELECT id, username, email, password_hash, role, full_name, is_active 
            FROM admins 
            WHERE username = ? AND is_active = 1
        ");
		$stmt->execute([$username]);
		$admin = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if (!$admin || !password_verify($password, $admin['password_hash'])) {
			// Track failed login attempts
			if (!isset($_SESSION['login_attempts'])) {
				$_SESSION['login_attempts'] = 1;
			} else {
				$_SESSION['login_attempts']++;
			}
			$_SESSION['last_attempt_time'] = time();
			
			throw new Exception('Invalid username or password.');
		}
		
		// Set session variables
		$_SESSION['admin_id'] = $admin['id'];
		$_SESSION['admin_username'] = $admin['username'];
		$_SESSION['admin_role'] = $admin['role'];
		$_SESSION['admin_name'] = $admin['full_name'];
		
		// Reset login attempts on successful login
		$_SESSION['login_attempts'] = 0;
		unset($_SESSION['last_attempt_time']);
		
		// Update last login
		$stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
		$stmt->execute([$admin['id']]);
		
		$response['success'] = true;
		$response['message'] = 'Login successful!';
		$response['redirect'] = 'dashboard.php';
		
	} catch (Exception $e) {
		$response['message'] = $e->getMessage();
	}
	
	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Login - Somalia NIRA</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">
	<style>
		body {
			background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
		}
		.login-card {
			background: rgba(255, 255, 255, 0.95);
			backdrop-filter: blur(10px);
			border-radius: 20px;
			box-shadow: 0 20px 40px rgba(0,0,0,0.1);
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-6 col-lg-4">
				<div class="card login-card border-0">
					<div class="card-body p-5">
						<div class="text-center mb-4">
							<i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
							<h3 class="fw-bold">Admin Login</h3>
							<p class="text-muted">Somalia NIRA System</p>
						</div>
						
						<form id="loginForm" class="needs-validation" novalidate>
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<div class="mb-3">
								<label for="username" class="form-label">Username</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-user"></i>
									</span>
									<input type="text" class="form-control" id="username" name="username" 
									       placeholder="Enter username" required>
								</div>
								<div class="invalid-feedback">Please enter your username.</div>
							</div>
							
							<div class="mb-4">
								<label for="password" class="form-label">Password</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-lock"></i>
									</span>
									<input type="password" class="form-control" id="password" name="password" 
									       placeholder="Enter password" required>
									<button class="btn btn-outline-secondary" type="button" id="togglePassword">
										<i class="fas fa-eye"></i>
									</button>
								</div>
								<div class="invalid-feedback">Please enter your password.</div>
							</div>
							
							<div class="mb-3 form-check">
								<input type="checkbox" class="form-check-input" id="remember">
								<label class="form-check-label" for="remember">
									Remember me
								</label>
							</div>
							
							<button type="submit" class="btn btn-primary w-100 btn-lg">
								<i class="fas fa-sign-in-alt me-2"></i>Login
							</button>
						</form>
						
						<div class="text-center mt-4">
							<a href="index.php" class="text-decoration-none">
								<i class="fas fa-arrow-left me-1"></i>Back to Home
							</a>
						</div>
					</div>
				</div>
				
				<div class="text-center mt-4 text-white">
					<small>
						<i class="fas fa-lock me-1"></i>
						Secure login with encrypted data transmission
					</small>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script>
		// Toggle password visibility
		document.getElementById('togglePassword').addEventListener('click', function() {
			const password = document.getElementById('password');
			const icon = this.querySelector('i');
			
			if (password.type === 'password') {
				password.type = 'text';
				icon.classList.remove('fa-eye');
				icon.classList.add('fa-eye-slash');
			} else {
				password.type = 'password';
				icon.classList.remove('fa-eye-slash');
				icon.classList.add('fa-eye');
			}
		});
		
		// Handle form submission
		document.getElementById('loginForm').addEventListener('submit', function(e) {
			e.preventDefault();
			
			const formData = new FormData(this);
			const submitBtn = this.querySelector('button[type="submit"]');
			
			showLoading(submitBtn);
			
			fetch('login.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				hideLoading(submitBtn);
				
				if (data.success) {
					showAlert(data.message, 'success');
					setTimeout(() => {
						window.location.href = data.redirect;
					}, 1000);
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
	</script>
</body>
</html>
