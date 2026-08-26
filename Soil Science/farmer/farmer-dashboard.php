<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header('Location: ../index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard | Soil Science</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div id="wrapper" class="d-flex">
    <nav id="sidebar" class="bg-dark text-white" style="min-width: 260px; min-height: 100vh;">
        <div class="sidebar-header p-4"><h4 class="text-success fw-bold"><i class="bi bi-tree-fill me-2"></i>Soil Science</h4></div>
        <ul class="list-unstyled components px-3">
            <li class="active p-2"><a href="farmer-dashboard.php" class="text-white text-decoration-none"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li class="p-2"><a href="soil-analysis.html" class="text-white text-decoration-none"><i class="bi bi-moisture me-2"></i> Soil Analysis</a></li>
            <li class="p-2"><a href="feedback.html" class="text-white text-decoration-none"><i class="bi bi-chat-dots me-2"></i> Feedback</a></li>
            <li class="p-2"><a href="../api/logout.php" class="text-danger text-decoration-none"><i class="bi bi-box-arrow-left me-2"></i> Sign Out</a></li>
        </ul>
    </nav>

    <div id="content" class="w-100 p-4">
        <nav class="navbar bg-white py-3 px-4 rounded shadow-sm mb-4">
            <h4 class="mb-0 fw-bold">Farmer Operational Deck</h4>
        </nav>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 shadow-sm border-0 text-center">
                    <h6 class="text-muted">Total Soil Samples</h6>
                    <h2 class="fw-bold" id="totalSamples">0</h2>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr><th>Sample ID</th><th>pH</th><th>Moisture</th><th>Recommendation</th><th>Date</th></tr>
                            </thead>
                            <tbody id="farmerHistoryTableBody">
                                <tr><td colspan="5" class="text-center py-4">Syncing telemetry...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function fetchDashboardData() {
        try {
            const res = await fetch(`../api/get_farmer_soil_logs.php?t=${Date.now()}`);
            const data = await res.json();
            const tbody = document.getElementById('farmerHistoryTableBody');
            
            if (data.status === 'success') {
                document.getElementById('totalSamples').innerText = data.count;
                tbody.innerHTML = data.logs.length > 0 ? data.logs.map(log => `
                    <tr>
                        <td>#${log.id}</td>
                        <td>${parseFloat(log.ph_level).toFixed(1)}</td>
                        <td>${parseFloat(log.moisture_level).toFixed(1)}%</td>
                        <td><span class="badge bg-success">${log.crop_recommendation}</span></td>
                        <td>${new Date(log.analysis_date).toLocaleDateString()}</td>
                    </tr>
                `).join('') : '<tr><td colspan="5" class="text-center">No logs found.</td></tr>';
            }
        } catch (err) {
            console.error("Sync Error:", err);
        }
    }
    document.addEventListener('DOMContentLoaded', fetchDashboardData);
</script>
</body>
</html>