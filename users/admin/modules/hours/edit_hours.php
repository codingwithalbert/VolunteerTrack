<?php
require_once '../../../../includes/auth.php';
require_once '../../../../config/dbconfig.php';

requireRole(['admin', 'coordinator']);

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_hours.php');
    exit();
}

$hour_id = $_GET['id'];

// Fetch hour entry
$query = "SELECT * FROM hours WHERE hour_id = :hour_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':hour_id', $hour_id);
$stmt->execute();
$hour = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hour) {
    header('Location: manage_hours.php');
    exit();
}

// Get active activities
$query = "SELECT activity_id, activity_name FROM activities WHERE status = 'active' ORDER BY activity_name";
$stmt = $db->prepare($query);
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get volunteers
$query = "SELECT user_id, full_name FROM users WHERE role = 'volunteer' AND status = 'active' ORDER BY full_name";
$stmt = $db->prepare($query);
$stmt->execute();
$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $volunteer_id = $_POST['volunteer_id'];
    $activity_id = $_POST['activity_id'];
    $hours_input = trim($_POST['hours_worked']);
    if (strpos($hours_input, ':') !== false) {
        list($h, $m) = array_map('intval', explode(':', $hours_input));
        $hours_worked = $h + ($m / 60);
    } else {
        $hours_worked = floatval($hours_input);
    }
    $work_date = $_POST['work_date'];
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    
    if (empty($volunteer_id) || empty($activity_id) || empty($hours_worked) || empty($work_date)) {
        $error = 'Please fill in all required fields';
    } elseif ($hours_worked <= 0 || $hours_worked > 24) {
        $error = 'Hours must be between 0 and 24';
    } else {
        // Handle status updates properly
        if ($status == 'verified' || $status == 'rejected') {
            // Verified or rejected: mark verifier
            $query = "UPDATE hours 
                      SET volunteer_id = :volunteer_id, 
                        activity_id = :activity_id, 
                        hours_worked = :hours_worked, 
                        work_date = :work_date, 
                        description = :description, 
                        status = :status, 
                        verified_by = :verified_by, 
                        verified_at = NOW() 
                      WHERE hour_id = :hour_id";

            $stmt = $db->prepare($query);
        
            $verified_by = getUserId();
        
            $stmt->bindParam(':volunteer_id', $volunteer_id);
            $stmt->bindParam(':activity_id', $activity_id);
            $stmt->bindParam(':hours_worked', $hours_worked);
            $stmt->bindParam(':work_date', $work_date);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':verified_by', $verified_by);
            $stmt->bindParam(':hour_id', $hour_id);
        
        } elseif ($status == 'pending') {
            // Reset to pending: clear verifier info
            $query = "UPDATE hours 
                      SET volunteer_id = :volunteer_id, 
                        activity_id = :activity_id, 
                        hours_worked = :hours_worked, 
                        work_date = :work_date, 
                        description = :description, 
                        status = 'pending', 
                        verified_by = NULL, 
                        verified_at = NULL 
                      WHERE hour_id = :hour_id";

            $stmt = $db->prepare($query);
        
            $stmt->bindParam(':volunteer_id', $volunteer_id);
            $stmt->bindParam(':activity_id', $activity_id);
            $stmt->bindParam(':hours_worked', $hours_worked);
            $stmt->bindParam(':work_date', $work_date);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':hour_id', $hour_id);
        
        } else {
            // Other statuses
            $query = "UPDATE hours 
                      SET volunteer_id = :volunteer_id, 
                        activity_id = :activity_id, 
                        hours_worked = :hours_worked, 
                        work_date = :work_date, 
                        description = :description, 
                        status = :status 
                      WHERE hour_id = :hour_id";

            $stmt = $db->prepare($query);
        
            $stmt->bindParam(':volunteer_id', $volunteer_id);
            $stmt->bindParam(':activity_id', $activity_id);
            $stmt->bindParam(':hours_worked', $hours_worked);
            $stmt->bindParam(':work_date', $work_date);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':hour_id', $hour_id);
        }

        // Execute safely
        if ($stmt->execute()) {
            $_SESSION['success'] = "Hours updated successfully!";
            header("Location: edit_hours.php?id=" . $hour_id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update hours.";
            header("Location: edit_hours.php?id=" . $hour_id);
            exit();
        }
        
    }
}
$role = getUserRole();
$dashboard = $role == 'coordinator' ? 'c_dashboard.php' : 'a_dashboard.php';
$panel_name = $role == 'coordinator' ? 'Coordinator Panel' : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hours - <?php echo SITE_NAME; ?></title>
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
            min-height: 100px;
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
        
        .help-text {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
    </style> -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><span class="logo-emoji">🤝</span><?php echo SITE_NAME; ?> - <?php echo $panel_name; ?></h1>
            <div class="header-links">
                <a href="../../<?php echo $dashboard; ?>">Dashboard</a>
                <a href="../../../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>Edit Hour Entry</h2>
            <a href="manage_hours.php" class="btn btn-secondary">Back to Hours</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Volunteer <span class="required">*</span></label>
                    <select name="volunteer_id" required>
                        <?php foreach ($volunteers as $volunteer): ?>
                            <option value="<?php echo $volunteer['user_id']; ?>"
                                    <?php echo ($hour['volunteer_id'] == $volunteer['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($volunteer['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Activity <span class="required">*</span></label>
                    <select name="activity_id" required>
                        <?php foreach ($activities as $activity): ?>
                            <option value="<?php echo $activity['activity_id']; ?>"
                                    <?php echo ($hour['activity_id'] == $activity['activity_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($activity['activity_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Hours Worked <span class="required">*</span></label>
                    <input type="text" name="hours_worked" required 
                            value="<?php echo isset($_POST['hours_worked']) ? htmlspecialchars($_POST['hours_worked']) : ''; ?>"
                            placeholder="Ex: 2:30 or 2.5">
                </div>
                
                <div class="form-group">
                    <label>Work Date <span class="required">*</span></label>
                    <input type="date" name="work_date" required
                           value="<?php echo htmlspecialchars($hour['work_date']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($hour['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status <span class="required">*</span></label>
                    <select name="status" required>
                        <option value="pending" <?php echo ($hour['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="verified" <?php echo ($hour['status'] == 'verified') ? 'selected' : ''; ?>>Verified</option>
                        <option value="rejected" <?php echo ($hour['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <div class="help-text">Changing status to Verified or Rejected will record you as the verifier</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Hours</button>
                    <a href="manage_hours.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>