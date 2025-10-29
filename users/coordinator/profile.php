<?php
require_once '../../includes/auth.php';
require_once '../../config/dbconfig.php';

requireRole('coordinator');

$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();

$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
   <!-- <style>
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
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .profile-info {
            display: grid;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .info-item strong {
            width: 150px;
            color: #495057;
        }
        
        .info-item span {
            color: #212529;
            flex: 1;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .badge-coordinator {
            background: #28a745;
            color: white;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
    </style> -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><span class="logo-emoji">🤝</span><?php echo SITE_NAME; ?> - Coordinator Panel</h1>
            <div class="header-links">
                <a href="c_dashboard.php">Dashboard</a>
                <a href="../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>My Profile</h2>
            <a href="c_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card">
            <div class="profile-info">
                <div class="info-item">
                    <strong>Username:</strong>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Full Name:</strong>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Role:</strong>
                    <span><span class="badge badge-coordinator"><?php echo ucfirst($user['role']); ?></span></span>
                </div>
                
                <div class="info-item">
                    <strong>Status:</strong>
                    <span><span class="badge badge-active"><?php echo ucfirst($user['status']); ?></span></span>
                </div>
                
                <div class="info-item">
                    <strong>Phone:</strong>
                    <span><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Address:</strong>
                    <span><?php echo htmlspecialchars($user['address'] ?: 'Not provided'); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Member Since:</strong>
                    <span><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
            
            <div class="actions">
                <a href="change_password.php" class="btn btn-primary">Change Password</a>
                <a href="c_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>