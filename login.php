<?php
require_once 'includes/auth.php';
require_once 'config/dbconfig.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    switch($role) {
        case 'admin':
            header('Location: users/admin/a_dashboard.php');
            break;
        case 'coordinator':
            header('Location: users/coordinator/c_dashboard.php');
            break;
        case 'volunteer':
            header('Location: users/volunteer/v_dashboard.php');
            break;
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT user_id, username, password, email, full_name, role, status FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['status'] != 'active') {
                $error = 'Your account is inactive. Please contact administrator.';
            } elseif (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Redirect based on role
                switch($user['role']) {
                    case 'admin':
                        header('Location: users/admin/a_dashboard.php');
                        break;
                    case 'coordinator':
                        header('Location: users/coordinator/c_dashboard.php');
                        break;
                    case 'volunteer':
                        header('Location: users/volunteer/v_dashboard.php');
                        break;
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-50px, -50px); }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.5);
            width: 100%;
            max-width: 440px;
            padding: 50px 40px;
            animation: slideUp 0.6s ease;
            position: relative;
            z-index: 1;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .logo-icon span {
            font-size: 36px;
            color: white;
        }
        
        .logo h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .logo p {
            color: #718096;
            font-size: 15px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f7fafc;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group input::placeholder {
            color: #a0aec0;
        }

        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .forgot-password a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .error {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #c53030;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #fc8181;
            animation: shake 0.4s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            background: rgba(255, 255, 255, 0.98);
            padding: 0 15px;
            color: #718096;
            font-size: 13px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            color: #718096;
            font-size: 14px;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .demo-accounts {
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            border: 2px dashed #cbd5e0;
        }
        
        .demo-accounts h3 {
            font-size: 13px;
            color: #4a5568;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        
        .demo-accounts p {
            font-size: 13px;
            color: #718096;
            margin: 8px 0;
            padding: 8px 12px;
            background: white;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        
        .demo-accounts strong {
            color: #2d3748;
            font-weight: 600;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
        }

        .password-wrapper {
            position: relative;
        }
    
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-10%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    
        .toggle-password:hover svg {
            stroke: #667eea;
        }
        
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <span>🤝</span>
            </div>
            <h1><?php echo SITE_NAME; ?></h1>
            <p>Hour Logging & Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus placeholder="Enter your username">
            </div>
        
        <div class="form-group password-wrapper">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Show password">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#a0aec0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>
        
        <div class="divider">
            <span>OR</span>
        </div>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Create one here</a>
        </div>
        
        <div class="demo-accounts">
            <h3>🎯 Demo Accounts</h3>
            <p><strong>Admin:</strong> admin / admin123</p>
            <p><strong>Coordinator:</strong> coordinator1 / coord123</p>
            <p><strong>Volunteer:</strong> volunteer1 / volunteer123</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                // change to "eye-off" icon
                eyeIcon.innerHTML = `
                    <line x1="1" y1="1" x2="23" y2="23"/>
                    <path d="M10.58 10.58a3 3 0 0 0 4.24 4.24"/>
                    <path d="M9.88 4.12A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a20.29 20.29 0 0 1-3.34 4.62"/>
                    <path d="M6.42 6.42A10.94 10.94 0 0 0 1 12s4 7 11 7a10.94 10.94 0 0 0 5.88-1.88"/>
                `;
            } else {
        passwordField.type = 'password';
        // restore normal eye
        eyeIcon.innerHTML = `
            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
            <circle cx="12" cy="12" r="3"/>
        `;
    }
}
</script>
</body>
</html>