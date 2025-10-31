<?php
require_once '../../../../includes/auth.php';
require_once '../../../../config/dbconfig.php';

requireRole('admin');

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}

$user_id = $_GET['id'];

// Handle form submission BEFORE fetching user data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];
    $new_password = trim($_POST['new_password']);
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($role)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (!in_array($role, ['volunteer', 'coordinator', 'admin'])) {
        $error = 'Invalid role selected';
    } else {
        // Check duplicate username/email
        $query = "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $email, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            try {
                // Update query
                if (!empty($new_password) && strlen($new_password) >= 6) {
                    // With password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users 
                              SET username = ?, email = ?, password = ?, full_name = ?, 
                                  role = ?, phone = ?, address = ?, status = ?
                              WHERE user_id = ?";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([
                        $username, 
                        $email, 
                        $hashed_password, 
                        $full_name, 
                        $role, 
                        $phone, 
                        $address, 
                        $status, 
                        $user_id
                    ]);
                } else {
                    // Without password
                    $query = "UPDATE users 
                              SET username = ?, email = ?, full_name = ?, 
                                  role = ?, phone = ?, address = ?, status = ?
                              WHERE user_id = ?";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([
                        $username, 
                        $email, 
                        $full_name, 
                        $role, 
                        $phone, 
                        $address, 
                        $status, 
                        $user_id
                    ]);
                }
                
                if ($result && $stmt->rowCount() > 0) {
                    $success = "User updated successfully! Role changed to: " . ucfirst($role);
                } elseif ($result && $stmt->rowCount() == 0) {
                    $success = "No changes were made (values were the same)";
                } else {
                    $error = "Failed to update user";
                }
                
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch user data (AFTER processing the update)
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: manage_users.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">    
    <!--<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 16px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
            transition: all 0.3s;
        }
        
        .header-links a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #333;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
            font-size: 16px;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-success::before {
            content: '✓';
            font-size: 20px;
            font-weight: bold;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-error::before {
            content: '✗';
            font-size: 20px;
            font-weight: bold;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .help-text {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        
        .current-value {
            display: inline-block;
            background: #e7f3ff;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #0c5460;
        }
    </style> -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><span class="logo-emoji">🤝</span><?php echo SITE_NAME; ?> - Admin Panel</h1>
            <div class="header-links">
                <a href="../../a_dashboard.php">Dashboard</a>
                <a href="../../../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>Edit User</h2>
            <a href="manage_users.php" class="btn btn-secondary">← Back to Users</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="username" required 
                           value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required 
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" required 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" minlength="6" autocomplete="new-password">
                    <div class="help-text">Leave blank to keep current password</div>
                </div>
                
                <div class="form-group">
                    <label>Role <span class="required">*</span> 
                        <span class="current-value">Current: <?php echo ucfirst($user['role']); ?></span>
                    </label>
                    <select name="role" required>
                        <option value="volunteer" <?php echo ($user['role'] == 'volunteer') ? 'selected' : ''; ?>>Volunteer</option>
                        <option value="coordinator" <?php echo ($user['role'] == 'coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status <span class="required">*</span>
                        <span class="current-value">Current: <?php echo ucfirst($user['status']); ?></span>
                    </label>
                    <select name="status" required>
                        <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>