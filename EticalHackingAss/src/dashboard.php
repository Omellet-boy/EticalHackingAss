<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$update_message = "";

$col_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_pic'");
if ($col_check && $col_check->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD profile_pic VARCHAR(255) DEFAULT NULL");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bio'])) {
    $new_bio = $_POST['bio'] ?? '';

    $sql_update = "UPDATE users SET bio = '$new_bio' WHERE id = $user_id";
    
    if ($conn->query($sql_update)) {
        $_SESSION['user']['bio'] = $new_bio;
        $user['bio'] = $new_bio;
        $update_message = "<div class='alert alert-success'>Biography updated successfully!</div>";
    } else {
        $update_message = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pic']) && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_exts)) {
            $update_message = "<div class='alert alert-danger'>Error: Only JPG, PNG, and GIF images are allowed.</div>";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);
            
            if (!in_array($mime_type, $allowed_mimes)) {
                $update_message = "<div class='alert alert-danger'>Error: Invalid image content type.</div>";
            } else {
                $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                $upload_dir = __DIR__ . '/uploads/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_filename, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['user']['profile_pic'] = $new_filename;
                        $user['profile_pic'] = $new_filename;
                        $update_message = "<div class='alert alert-success'>Profile picture updated successfully!</div>";
                    } else {
                        $update_message = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
                    }
                    $stmt->close();
                } else {
                    $update_message = "<div class='alert alert-danger'>Error: Failed to save profile picture.</div>";
                }
            }
        }
    } else {
        $update_message = "<div class='alert alert-danger'>Error: File upload encountered an error (Code: " . $file['error'] . ").</div>";
    }
}

$sql_select = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql_select);
if ($result && $result->num_rows > 0) {
    $db_user = $result->fetch_assoc();
    $user['bio'] = $db_user['bio'] ?? '';
    $user['profile_pic'] = $db_user['profile_pic'] ?? null;
    $_SESSION['user']['bio'] = $user['bio'];
    $_SESSION['user']['profile_pic'] = $user['profile_pic'];
}

$profile_pic = $_SESSION['user']['profile_pic'] ?? 'uploads/default.png';

$enrolled_courses = [];
$sql_courses = "SELECT course_name FROM grades WHERE student_id = $user_id";
$res_courses = $conn->query($sql_courses);
if ($res_courses) {
    while ($row = $res_courses->fetch_assoc()) {
        $enrolled_courses[] = $row['course_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MyEduConnect Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --mmu-dark: #1E1E24;
            --slate-bg: #2f3542;
            --slate-hover: #57606f;
            --slate-text: #ced6e0;
            
            --header-blue: #0A3663;
            --header-teal: #079992;
            --header-purple: #82589f;
            --header-amber: #f39c12;
        }
        body{
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
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
        .header-blue{
            background-color: var(--header-blue);
            color: white;
        }
        .header-teal{
            background-color: var(--header-teal);
            color: white;
        }
        .header-purple{
            background-color: var(--header-purple);
            color: white;
        }
        .header-amber 
            background-color: var(--header-amber);
            color: white;
        }
        
        .card-header-custom{
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
        }
        
        .profile-img-preview{
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ddd;
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
                    Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong> (Role: <?php echo htmlspecialchars($user['role']); ?>)
                </span>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                
                <div class="col-md-3 col-lg-2 sidebar-slate py-4 px-3 d-flex flex-column" style="min-height: calc(100vh - 56px);">
                    <h5 class="sidebar-heading px-3 pb-3 mb-3">Navigation</h5>
                    <div class="list-group list-group-flush flex-grow-1">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active rounded mb-1">Dashboard</a>
                        <a href="profile.php?id=<?php echo $user_id; ?>" class="list-group-item list-group-item-action rounded mb-1">Student Directory</a>
                        <a href="courses.php" class="list-group-item list-group-item-action rounded mb-1">Courses</a>
                        <a href="upload.php" class="list-group-item list-group-item-action rounded mb-1">Coursework</a>
                        <a href="feedback.php" class="list-group-item list-group-item-action rounded mb-1">Feedback</a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="admin.php" class="list-group-item list-group-item-action rounded mb-1">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="list-group-item list-group-item-action rounded text-danger mt-4">Logout</a>
                    </div>
                </div>

                <div class="col-md-9 col-lg-10 py-4 px-4 bg-light">
                    
                    <div class="row">
                        <div class="col-lg-8">
                            
                            <div class="card card-custom">
                                <div class="card-header card-header-custom header-blue">
                                    Academic Dashboard
                                </div>
                                <div class="card-body p-4">
                                    <h4 class="mb-3">Welcome to your MyEduConnect Workspace</h4>
                                    <p>Manage coursework, submit assignments, and update your academic details securely through this unified student hub.</p>
                                    
                                    <div class="mt-4">
                                        <a href="profile.php?id=<?php echo $user_id; ?>" class="btn btn-primary me-2" style="background-color: var(--header-blue); border: none;">View Full Profile</a>
                                        <a href="transcript.php" class="btn btn-outline-secondary">View Academic Transcript</a>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-custom">
                                <div class="card-header card-header-custom header-purple">
                                    Academic Biography & Student Profile Picture
                                </div>
                                <div class="card-body p-4">
                                    <?php echo $update_message; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-4 text-center border-end mb-3 mb-md-0">
                                            <h6 class="mb-3 text-muted">Profile Picture</h6>
                                            <?php if ($profile_pic !== 'uploads/default.png' && file_exists(__DIR__ . '/uploads/' . $profile_pic)): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-img-preview mb-3">
                                            <?php else: ?>
                                                <svg class="profile-img-preview mb-3 bg-secondary text-white p-2" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            <?php endif; ?>
                                            
                                            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="mt-2">
                                                <div class="mb-2">
                                                    <input type="file" name="profile_pic" class="form-control form-control-sm" required>
                                                </div>
                                                <button type="submit" name="upload_pic" class="btn btn-sm btn-outline-primary w-100">Upload Picture</button>
                                            </form>
                                        </div>
                                        
                                        <div class="col-md-8">
                                            <div class="p-3 mb-4 bg-light rounded border border-warning" style="min-height: 80px;">
                                                <strong>Current Bio:</strong><br>
                                                <?php 
                                                    echo $user['bio']; 
                                                ?>
                                            </div>

                                            <form action="dashboard.php" method="POST">
                                                <div class="mb-3">
                                                    <label for="bio" class="form-label">Update Academic Biography</label>
                                                    <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Update your academic description..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                                    <div class="form-text text-muted">HTML rich tags are permitted in student bios.</div>
                                                </div>
                                                <button type="submit" name="save_bio" class="btn btn-primary" style="background-color: var(--header-purple); border: none;">Save Bio</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <div class="col-lg-4">
                            
                            <div class="card card-custom">
                                <div class="card-header card-header-custom header-teal">
                                    My Enrolled Courses
                                </div>
                                <div class="card-body p-4">
                                    <?php if (count($enrolled_courses) > 0): ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($enrolled_courses as $course): ?>
                                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                    <span><?php echo htmlspecialchars($course); ?></span>
                                                    <span class="badge bg-success rounded-pill">Active</span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">You are not enrolled in any courses yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card card-custom" style="background-color: #1e293b; color: #94a3b8; border: 1px solid #334155;">
                                <div class="card-header card-header-custom" style="border-top: 4px solid #ef4444; background-color: #0f172a; color: #ffffff; border-bottom: 1px solid #334155; font-weight: 600; font-size: 1.1rem; padding: 15px 20px;">
                                    Upcoming Deadlines
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <span class="d-block text-white-50 small">Course</span>
                                        <span class="text-white fw-semibold">CYB3024 - Ethical Hacking & Pentesting</span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="d-block text-white-50 small">Task</span>
                                        <span class="text-white"><strong>Final Project: End-to-End Security Engagement</strong></span>
                                    </div>
                                    <div class="mb-4">
                                        <span class="d-block text-white-50 small">Deadline</span>
                                        <span style="color: #f87171; font-weight: 600;">Friday, 19 June 2026 - 11:59 PM (Strict No Extension)</span>
                                    </div>
                                    <a href="upload.php" class="btn btn-danger w-100 fw-semibold" style="background-color: #ef4444; border: none; color: #ffffff;">Submit Now</a>
                                </div>
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
