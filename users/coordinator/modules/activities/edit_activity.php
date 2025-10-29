<?php
require_once '../../../../includes/auth.php';
require_once '../../../../config/dbconfig.php';

requireRole(['admin', 'coordinator']);

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_activities.php');
    exit();
}

$activity_id = $_GET['id'];

// Fetch activity data
$query = "SELECT * FROM activities WHERE activity_id = :activity_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':activity_id', $activity_id);
$stmt->execute();
$activity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activity) {
    header('Location: manage_activities.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activity_name = trim($_POST['activity_name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $status = $_POST['status'];
    
    if (empty($activity_name) || empty($description)) {
        $error = 'Activity name and description are required';
    } else {
        $query = "UPDATE activities SET activity_name = :activity_name, description = :description, 
                  location = :location, status = :status WHERE activity_id = :activity_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':activity_name', $activity_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':activity_id', $activity_id);
        
        if ($stmt->execute()) {
            $success = 'Activity updated successfully!';
            // Refresh activity data
            $query = "SELECT * FROM activities WHERE activity_id = :activity_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':activity_id', $activity_id);
            $stmt->execute();
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Failed to update activity';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Activity - <?php echo SITE_NAME; ?></title>
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
            font-size: 16px;
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
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
    </style> -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><span class="logo-emoji">🤝</span><?php echo SITE_NAME; ?> - <?php echo getUserRole() == 'admin' ? 'Admin' : 'Coordinator'; ?> Panel</h1>
            <div class="header-links">
                <a href="../../<?php echo getUserRole() == 'coordinator' ? 'c' : 'a'; ?>../../c_dashboard.php">Dashboard</a>
                <a href="../../../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>Edit Activity</h2>
            <a href="manage_activities.php" class="btn btn-secondary">Back to Activities</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Activity Name <span class="required">*</span></label>
                    <input type="text" name="activity_name" required 
                           value="<?php echo htmlspecialchars($activity['activity_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" 
                           value="<?php echo htmlspecialchars($activity['location']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Status <span class="required">*</span></label>
                    <select name="status" required>
                        <option value="active" <?php echo ($activity['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($activity['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Activity</button>
                    <a href="manage_activities.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>