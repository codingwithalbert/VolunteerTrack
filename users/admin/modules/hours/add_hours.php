<?php
require_once '../../../../includes/auth.php';
require_once '../../../../config/dbconfig.php';

requireRole(['admin', 'coordinator']);

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get active activities
$query = "SELECT activity_id, activity_name, description, location FROM activities WHERE status = 'active' ORDER BY activity_name";
$stmt = $db->prepare($query);
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get volunteers
$query = "SELECT user_id, full_name, email FROM users WHERE role = 'volunteer' AND status = 'active' ORDER BY full_name";
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
    
    if (empty($volunteer_id) || empty($activity_id) || empty($hours_worked) || empty($work_date)) {
        $error = 'Please fill in all required fields';
    } elseif ($hours_worked <= 0 || $hours_worked > 24) {
        $error = 'Hours must be between 0 and 24';
    } elseif (strtotime($work_date) > time()) {
        $error = 'Work date cannot be in the future';
    } else {
        $query = "INSERT INTO hours (volunteer_id, activity_id, hours_worked, work_date, description, status) 
                  VALUES (:volunteer_id, :activity_id, :hours_worked, :work_date, :description, 'pending')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->bindParam(':activity_id', $activity_id);
        $stmt->bindParam(':hours_worked', $hours_worked);
        $stmt->bindParam(':work_date', $work_date);
        $stmt->bindParam(':description', $description);
        
        if ($stmt->execute()) {
            $success = 'Hours logged successfully!';
            $_POST = array();
        } else {
            $error = 'Failed to log hours';
        }
    }
}

$role = getUserRole();
$dashboard = $role == 'admin' ? 'a_dashboard.php' : 'c_dashboard.php';
$panel_name = $role == 'admin' ? 'Admin Panel' : 'Coordinator Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hours - <?php echo SITE_NAME; ?></title>
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
        
        .info-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 13px;
            color: #666;
            display: none;
        }
    </style> -->
    <script>
        function showActivityInfo() {
            const select = document.getElementById('activity_id');
            const infoDiv = document.getElementById('activity-info');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                const desc = selectedOption.getAttribute('data-description');
                const loc = selectedOption.getAttribute('data-location');
                let info = '';
                if (desc) info += '<strong>Description:</strong> ' + desc + '<br>';
                if (loc) info += '<strong>Location:</strong> ' + loc;
                
                infoDiv.innerHTML = info;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }
        
        function showVolunteerInfo() {
            const select = document.getElementById('volunteer_id');
            const infoDiv = document.getElementById('volunteer-info');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                const email = selectedOption.getAttribute('data-email');
                infoDiv.innerHTML = '<strong>Email:</strong> ' + email;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }
    </script>
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
            <h2>Add Volunteer Hours</h2>
            <a href="manage_hours.php" class="btn btn-secondary">Back to Hours</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <a href="manage_hours.php">View all hours</a>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <?php if (count($volunteers) > 0 && count($activities) > 0): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Volunteer <span class="required">*</span></label>
                        <select name="volunteer_id" id="volunteer_id" required onchange="showVolunteerInfo()">
                            <option value="">Select Volunteer</option>
                            <?php foreach ($volunteers as $volunteer): ?>
                                <option value="<?php echo $volunteer['user_id']; ?>"
                                        data-email="<?php echo htmlspecialchars($volunteer['email']); ?>"
                                        <?php echo (isset($_POST['volunteer_id']) && $_POST['volunteer_id'] == $volunteer['user_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($volunteer['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="volunteer-info" class="info-display"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Activity <span class="required">*</span></label>
                        <select name="activity_id" id="activity_id" required onchange="showActivityInfo()">
                            <option value="">Select Activity</option>
                            <?php foreach ($activities as $activity): ?>
                                <option value="<?php echo $activity['activity_id']; ?>"
                                        data-description="<?php echo htmlspecialchars($activity['description']); ?>"
                                        data-location="<?php echo htmlspecialchars($activity['location']); ?>"
                                        <?php echo (isset($_POST['activity_id']) && $_POST['activity_id'] == $activity['activity_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($activity['activity_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="activity-info" class="info-display"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Hours Worked <span class="required">*</span></label>
                        <input type="text" name="hours_worked" required 
                               value="<?php echo isset($_POST['hours_worked']) ? htmlspecialchars($_POST['hours_worked']) : ''; ?>"
                               placeholder="Ex: 2:30 or 2.5">
                    </div>

                    <div class="form-group">
                        <label>Work Date <span class="required">*</span></label>
                        <input type="date" name="work_date" required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo isset($_POST['work_date']) ? htmlspecialchars($_POST['work_date']) : date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" 
                                  placeholder="Describe what was done during this volunteer session..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Hours</button>
                        <a href="manage_hours.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    <?php if (count($volunteers) == 0): ?>
                        No active volunteers available. Please add volunteers first.
                    <?php elseif (count($activities) == 0): ?>
                        No active activities available. Please add activities first.
                    <?php endif; ?>
                </p>
                <div style="text-align: center;">
                    <a href="../../<?php echo $dashboard; ?>" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>