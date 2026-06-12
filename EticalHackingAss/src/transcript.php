<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

if (!isset($_GET['student_id'])){
    header("Location: transcript.php?student_id=" . $user_id);
    exit;
}

$student_id_param = intval($_GET['student_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - MyEduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --mmu-dark: #1E1E24;
            --slate-bg: #2f3542;
            --slate-hover: #57606f;
            --slate-text: #ced6e0;
            --header-blue: #0A3663;
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
        .card-header-custom{
            background-color: var(--header-blue);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
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
                    Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong>
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
                    
                    <div class="row justify-content-center">
                        <div class="col-xl-9 col-lg-11 col-md-12">
                            
                            <div class="card card-custom">
                                <div class="card-header card-header-custom">
                                    Official Academic Transcript
                                </div>
                                <div class="card-body p-4">
                                    <h4 class="mb-3">Student Course Progress</h4>
                                    <p class="text-muted">
                                        This official transcript lists all completed coursework and certified grades recorded for your active student profile at MyEduConnect.
                                    </p>
                                    
                                    <div id="loading-spinner" class="text-center my-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading grades...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Retrieving records from database...</p>
                                    </div>
                                    
                                    <div id="error-container" class="alert alert-danger d-none"></div>

                                    <div class="table-responsive mt-3 d-none" id="transcript-table-container">
                                        <table class="table table-bordered table-striped" id="transcript-table">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Course ID</th>
                                                    <th>Course Name</th>
                                                    <th>Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody id="transcript-body">
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4">
                                        <a href="dashboard.php" class="btn btn-primary" style="background-color: var(--header-blue); border: none;">Return to Dashboard</a>
                                    </div>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('student_id');
            
            const spinner = document.getElementById("loading-spinner");
            const tableContainer = document.getElementById("transcript-table-container");
            const tbody = document.getElementById("transcript-body");
            const errorContainer = document.getElementById("error-container");

            if (!studentId) {
                spinner.classList.add("d-none");
                errorContainer.textContent = "Error: student_id query parameter is missing in the browser address bar.";
                errorContainer.classList.remove("d-none");
                return;
            }

            fetch(`api_grades.php?student_id=${studentId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    spinner.classList.add("d-none");
                    
                    if (data.status === "success" && Array.isArray(data.grades)) {
                        if (data.grades.length > 0) {
                            tbody.innerHTML = ""; // Clear existing rows
                            data.grades.forEach(row => {
                                const tr = document.createElement("tr");
                                
                                const tdId = document.createElement("td");
                                tdId.textContent = row.id;
                                
                                const tdName = document.createElement("td");
                                tdName.textContent = row.course_name;
                                
                                const tdGrade = document.createElement("td");
                                tdGrade.textContent = row.grade;
                                
                                tr.appendChild(tdId);
                                tr.appendChild(tdName);
                                tr.appendChild(tdGrade);
                                
                                tbody.appendChild(tr);
                            });
                            tableContainer.classList.remove("d-none");
                        } else {
                            errorContainer.textContent = "No academic grades recorded for this student ID.";
                            errorContainer.classList.remove("d-none");
                            errorContainer.classList.replace("alert-danger", "alert-info");
                        }
                    } else {
                        errorContainer.textContent = "Failed to parse database records.";
                        errorContainer.classList.remove("d-none");
                    }
                })
                .catch(error => {
                    spinner.classList.add("d-none");
                    errorContainer.textContent = "Connection Error: Unable to communicate with the REST API service. Details: " + error.message;
                    errorContainer.classList.remove("d-none");
                });
        });
    </script>

</body>
</html>
