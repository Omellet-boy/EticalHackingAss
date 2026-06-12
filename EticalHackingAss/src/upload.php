<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['assignment_file'])) {
    $file = $_FILES['assignment_file'];
    
    $allowed_exts = ['pdf', 'docx', 'doc', 'txt', 'zip'];
    $allowed_mimes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'text/plain',
        'application/zip',
        'application/x-zip-compressed'
    ];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "<div class='alert alert-danger'>Error: File upload failed with code " . $file['error'] . ".</div>";
    } else {
        $filename = basename($file['name']);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_exts)) {
            $message = "<div class='alert alert-danger'>Error: File type not allowed. Only PDF, DOCX, TXT, and ZIP are permitted.</div>";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);

            if (!in_array($mime_type, $allowed_mimes)) {
                $message = "<div class='alert alert-danger'>Error: Invalid file content type.</div>";
            } else {
                $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;

                $upload_dir = __DIR__ . '/uploads/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $message = "<div class='alert alert-success'>Assignment uploaded successfully! Reference ID: " . htmlspecialchars($new_filename) . "</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error: Failed to save the uploaded file.</div>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assignment - MyEduConnect Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --mmu-dark: #1E1E24;
            --slate-bg: #2f3542;
            --slate-hover: #57606f;
            --slate-text: #ced6e0;
            
            --header-blue: #0A3663;
            --header-red: #D12B2B;
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
        
        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header-custom{
            background-color: var(--header-red);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
            border-bottom: 1px solid var(--header-red);
        }
        
        .card-slate{
            background-color: #1e293b;
            color: #94a3b8;
            border: 1px solid #334155;
            border-radius: 10px;
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-slate-header{
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
            border-bottom: 1px solid #334155;
        }
        .table-slate{
            color: #94a3b8;
            --bs-table-bg: #1e293b;
            --bs-table-striped-bg: #243046;
        }
        .table-slate th{
            color: #ffffff;
            border-bottom: 2px solid #334155;
        }
        .table-slate td{
            color: #94a3b8;
            border-bottom: 1px solid #334155;
        }
        
        footer{
            background-color: var(--mmu-dark);
            color: #d1d1d1;
            padding: 20px 0;
            font-size: 0.9rem;
            border-top: 3px solid var(--header-red);
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
                        <a href="dashboard.php" class="list-group-item list-group-item-action rounded mb-1">Dashboard</a>
                        <a href="profile.php?id=<?php echo $user_id; ?>" class="list-group-item list-group-item-action rounded mb-1">Student Directory</a>
                        <a href="courses.php" class="list-group-item list-group-item-action rounded mb-1">Courses</a>
                        <a href="upload.php" class="list-group-item list-group-item-action active rounded mb-1">Coursework</a>
                        <a href="feedback.php" class="list-group-item list-group-item-action rounded mb-1">Feedback</a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="admin.php" class="list-group-item list-group-item-action rounded mb-1">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="list-group-item list-group-item-action rounded text-danger mt-4">Logout</a>
                    </div>
                </div>

                <div class="col-md-9 col-lg-10 py-4 px-4 bg-light">
                    
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10 col-md-12">
                            
                            <div class="card card-slate shadow-sm">
                                <div class="card-slate-header">
                                    Upcoming Due Coursework
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-slate mb-0 align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Assignment Title</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>CYB3024</strong> - Ethical Hacking & Pentesting</td>
                                                    <td>Final Project: End-to-End Security Engagement</td>
                                                    <td><span class="text-warning fw-semibold">Friday, 19 June 2026 - 11:59 PM (Strict No Extension)</span></td>
                                                    <td><span class="badge text-white" style="background-color: #d35400;">Pending</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card card-custom">
                                <div class="card-header card-header-custom">
                                    Assignment Submission Portal
                                </div>
                                <div class="card-body p-4">
                                    <p class="text-muted mb-4">
                                        This assignment portal allows students to submit coursework files directly to instructors for grading. Your submission will be timestamped and logged under your student profile.
                                    </p>
                                    <h4 class="mb-3">Submit Your Coursework</h4>
                                    <p class="text-muted">
                                        Please select your assignment file to upload. Accepted file formats include PDF, DOCX, TXT, and ZIP. Ensure the file size does not exceed the limit.
                                    </p>
                                    
                                    <?php echo $message; ?>

                                    <form action="upload.php" method="POST" enctype="multipart/form-data" class="mt-4">
                                        <div class="mb-4">
                                            <label for="assignment_file" class="form-label">Select Assignment File</label>
                                            <input class="form-control" type="file" id="assignment_file" name="assignment_file" required>
                                            <div class="form-text text-muted">Max file size: 5MB. Only safe formats allowed.</div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                                            <button type="submit" class="btn btn-danger" style="background-color: var(--header-red); border: none;">Upload File</button>
                                        </div>
                                    </form>
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