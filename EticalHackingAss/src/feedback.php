<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$username = $user['username'];
$message_status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])){
    $feedback_msg = $_POST['feedback_msg'] ?? '';

    if (!empty($feedback_msg)) {
        $conn->query("ALTER TABLE feedback ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL");

        $feedback_author = "User ID: " . $user_id . " (" . $username . ")";
        
        $sql_insert = "INSERT INTO feedback (user_id, name, message) VALUES ($user_id, '$feedback_author', '$feedback_msg')";
        
        if ($conn->query($sql_insert)) {
            $message_status = "<div class='alert alert-success'>Thank you! Your feedback has been submitted.</div>";
        } else {
            $message_status = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
        }
    } else {
        $message_status = "<div class='alert alert-warning'>Please enter a feedback message.</div>";
    }
}

$sql_select = "SELECT * FROM feedback ORDER BY id DESC";
$result_feedback = $conn->query($sql_select);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Portal - MyEduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --mmu-dark: #1E1E24;
            --slate-bg: #2f3542;
            --slate-hover: #57606f;
            --slate-text: #ced6e0;
            --header-blue: #0A3663;
            --header-teal: #079992;
        }
        body{
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Left-Sidebar Styling */
        .sidebar-slate{
            background-color: var(--slate-bg);
            color: #ffffff;
        }
        .sidebar-slate .sidebar-heading{
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid var(--slate-hover);
        }
        .sidebar-slate .list-group-item{
            color: var(--slate-text);
            background: transparent;
            border: none;
            transition: all 0.2s ease;
        }
        .sidebar-slate .list-group-item:hover{
            color: #ffffff;
            background-color: var(--slate-hover);
        }
        .sidebar-slate .list-group-item.active{
            color: #ffffff;
            background-color: var(--header-blue);
            font-weight: 600;
        }
        
        .card-custom{
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header-custom{
            background-color: var(--header-teal);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
        }
        
        .feedback-item{
            border-left: 4px solid var(--header-teal);
            background-color: #ffffff;
        }
        
        footer{
            background-color: var(--mmu-dark);
            color: #d1d1d1;
            padding: 20px 0;
            font-size: 0.9rem;
            border-top: 3px solid #D12B2B;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <div class="flex-grow-1">
        
        <nav class="navbar navbar-dark bg-dark px-3 py-2 shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">MyEduConnect Student Portal</span>
                <span class="navbar-text text-white d-none d-sm-inline">
                    Welcome, <strong><?php echo htmlspecialchars($username); ?></strong> (Role: <?php echo htmlspecialchars($user['role']); ?>)
                </span>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                
                <div class="col-md-3 col-lg-2 sidebar-slate py-4 px-3 d-flex flex-column" style="min-height: calc(100vh - 56px);">
                    <h5 class="sidebar-heading px-3 pb-3 mb-3">Navigation</h5>
                    <div class="list-group list-group-flush flex-grow-1">
                        <a href="dashboard.php" class="list-group-item list-group-item-action rounded mb-1">Dashboard</a>
                        <a href="profile.php?id=<?php echo $user_id; ?>" class="list-group-item list-group-item-action rounded mb-1">Student Directory</a>
                        <a href="courses.php" class="list-group-item list-group-item-action rounded mb-1">Courses</a>
                        <a href="upload.php" class="list-group-item list-group-item-action rounded mb-1">Coursework</a>
                        <a href="feedback.php" class="list-group-item list-group-item-action active rounded mb-1">Feedback</a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="admin.php" class="list-group-item list-group-item-action rounded mb-1">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="list-group-item list-group-item-action rounded text-danger mt-4">Logout</a>
                    </div>
                </div>

                <div class="col-md-9 col-lg-10 py-4 px-4 bg-light">
                    
                    <div class="row">
                        <div class="col-lg-7 col-md-12">
                            <div class="card card-custom">
                                <div class="card-header card-header-custom">
                                    Submit Campus Feedback
                                </div>
                                <div class="card-body p-4">
                                    <p class="text-muted">
                                        Please write your comments, feedback, or system suggestions below. All submitted logs are referenced by user ID and reviewed.
                                    </p>
                                    
                                    <?php echo $message_status; ?>

                                    <form action="feedback.php" method="POST">
                                        <div class="mb-3">
                                            <label for="feedback_msg" class="form-label">Feedback Message</label>
                                            <textarea class="form-control" id="feedback_msg" name="feedback_msg" rows="6" placeholder="Write your comments here..." required></textarea>
                                        </div>
                                        <button type="submit" name="submit_feedback" class="btn btn-primary" style="background-color: var(--header-teal); border: none;">Submit Feedback</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5 col-md-12">
                            <h4 class="mb-3 text-dark">Recent Student Feedback</h4>
                            <div style="max-height: 450px; overflow-y: auto;">
                                <?php if ($result_feedback && $result_feedback->num_rows > 0): ?>
                                    <?php while ($row = $result_feedback->fetch_assoc()): ?>
                                        <div class="card feedback-item mb-3 shadow-sm p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary"><?php echo htmlspecialchars($row['name']); ?></strong>
                                                <span class="badge bg-light text-muted">ID: <?php echo $row['id']; ?></span>
                                            </div>
                                            
                                            <div class="text-secondary small">
                                                <?php echo $row['message']; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No feedbacks submitted yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div> 

    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-2"><strong>MyEduConnect &copy; 2026</strong></p>
            <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                Authorized access only. Subject to the Personal Data Protection Act (PDPA) 2010 of Malaysia.
            </p>
        </div>
    </footer>

</body>
</html>
