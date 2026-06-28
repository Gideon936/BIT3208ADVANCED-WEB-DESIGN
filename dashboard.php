<?php
// Week 7: Session Management & Protected Pages
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$feedback = "";

// Week 6: CREATE Operation (Prepared Statement)
if (isset($_POST['add_task'])) {
    if (empty($_POST['task_name'])) {
        $feedback = "Task Name Required!";
    } else {
        $task_name = trim($_POST['task_name']);
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task_name);
        $stmt->execute();
        $feedback = "Record Saved Successfully";
    }
}

// Week 6: DELETE Operation
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    header("Location: dashboard.php?msg=deleted");
    exit();
}

// Week 6: UPDATE Operation (Mark as Completed)
if (isset($_GET['complete'])) {
    $task_id = $_GET['complete'];
    $stmt = $conn->prepare("UPDATE tasks SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    header("Location: dashboard.php?msg=updated");
    exit();
}

// User-Friendly Feedback Check
if(isset($_GET['msg'])) {
    if($_GET['msg'] == 'deleted') $feedback = "Record Deleted Successfully";
    if($_GET['msg'] == 'updated') $feedback = "Record Updated Successfully";
}

// Bonus Feature: Search Functionality & READ Operation
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND task_name LIKE ? ORDER BY created_at DESC");
    $like_search = "%" . $search . "%";
    $stmt->bind_param("is", $user_id, $like_search);
} else {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$tasks_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Viewport tag to tell browsers how to display content on mobile devices[cite: 3] -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Dashboard - Task Manager</title>
    <style>
        /* Mobile-First Design Approach: Base styles designed for small screens first[cite: 3] */
        body { 
            font-family: Arial, sans-serif; 
            background: #f4f4f9; 
            margin: 0; 
            padding: 10px; 
            font-size: 16px; 
        }
        .dashboard-container { 
            width: 100%; 
            box-sizing: border-box; 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        
        /* Flexbox for flexible alignments[cite: 3] */
        .flex-header { 
            display: flex; 
            flex-direction: column; /* Stack on mobile */
            gap: 10px;
            align-items: flex-start; 
        }
        
        .form-group { 
            display: flex; 
            flex-direction: column; /* Stack inputs on mobile */
            gap: 10px; 
            margin-bottom: 20px; 
        }
        
        input[type="text"] { 
            width: 100%; 
            box-sizing: border-box; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
        }
        
        button { 
            width: 100%; 
            padding: 10px; 
            background: #28a745; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        
        .logout { 
            background: #dc3545; 
            text-decoration: none; 
            padding: 8px 12px; 
            color: white; 
            border-radius: 4px; 
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }

        /* Responsive Table styling for mobile */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        
        /* CSS Media Queries: Expanding layout for Tablets and Desktops[cite: 3] */
        @media(min-width: 768px) {
            body { padding: 20px; }
            .dashboard-container { max-width: 900px; margin: auto; padding: 20px; }
            
            /* Revert to rows for wider screens */
            .flex-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .logout { width: auto; }
            
            .form-group { flex-direction: row; }
            input[type="text"] { flex-grow: 1; width: auto; }
            button { width: auto; }
            
            table { font-size: 16px; }
            th, td { padding: 12px; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="flex-header">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <!-- Week 7: Secure Logout -->
        <a href="logout.php" class="logout">Logout</a>
    </div>
    
    <?php if($feedback): ?>
        <p class="feedback"><?php echo $feedback; ?></p>
    <?php endif; ?>

    <!-- Bonus Feature: Search Bar -->
    <form method="GET" class="form-group" style="background: #f8f9fa; padding: 10px; border-radius: 4px;">
        <input type="text" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" style="background: #6c757d;">Search</button>
        <a href="dashboard.php" style="padding: 10px; color: #007bff; text-decoration: none;">Clear</a>
    </form>

    <!-- Week 6: Task Input Form (CREATE) -->
    <form method="POST" class="form-group">
        <input type="text" name="task_name" placeholder="Enter a new task..." required>
        <button type="submit" name="add_task">Save Task</button>
    </form>

    <!-- Week 6: Data Table (READ, UPDATE, DELETE) -->
    <table>
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $tasks_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                    <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                    <td>
                        <?php if($row['status'] == 'pending'): ?>
                            <a href="dashboard.php?complete=<?php echo $row['id']; ?>" class="action-btn btn-update">Complete</a>
                        <?php endif; ?>
                        <a href="dashboard.php?delete=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>