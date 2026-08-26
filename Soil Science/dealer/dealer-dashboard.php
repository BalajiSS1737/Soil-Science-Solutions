<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Ensure only verified regional supply vendors can enter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dealer') {
    header('Location: ../index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Distribution Hub - Soil Science Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar { min-width: 250px; max-width: 250px; background: #1a1d24; color: #fff; min-height: 100vh; transition: all 0.3s; }
        #sidebar.active { margin-left: -250px; }
        #sidebar .sidebar-header { padding: 20px; background: #11141a; }
        #sidebar ul.components { padding: 20px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 1.05em; display: block; color: #a2aab7; text-decoration: none; }
        #sidebar ul li a:hover, #sidebar ul li.active > a { color: #fff; background: #232833; border-left: 4px solid #0d9488; }
        #content { width: 100%; padding: 25px; min-height: 100vh; transition: all 0.3s; }
        .text-dealer { color: #0d9488 !important; }
        .btn-dealer { background-color: #1a1d24; color: white; }
        .btn-dealer:hover { background-color: #0d9488; color: white; }
        .badge-dealer { background-color: rgba(13, 148, 136, 0.12); color: #0d9488; border: 1px solid rgba(13, 148, 136, 0.25); }
    </style>
</head>
<body>

<div id="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0 text-info fw-bold"><i class="bi bi-shop-window me-2"></i>AgriPulse Hub</h4>
        </div>
        <ul class="list-unstyled components">
            <li class="active"><a href="dealer-dashboard.php"><i class="bi bi-broadcast me-2"></i> Regional Live Feed</a></li>
            <li><a href="../api/logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Disconnect Portal</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 px-4 rounded shadow-sm mb-4">
            <div class="container-fluid p-0">
                <button type="button" id="sidebarCollapse" class="btn btn-dealer">
                    <i class="bi bi-list"></i>
                </button>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted">Distribution Node ID: <strong class="text-dark"><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                    <span class="badge bg-dark px-3 py-2 text-capitalize"><?php echo htmlspecialchars($_SESSION['role']); ?> Mode</span>
                </div>
            </div>
        </nav>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Total Regional Demand Triggers</h6>
                            <h2 class="fw-bold mb-0 text-dark" id="cardDemandsCount">--</h2>
                        </div>
                        <div class="bg-light text-primary p-3 rounded-circle"><i class="bi bi-clipboard2-data h2 mb-0"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Ecosystem Status</h6>
                            <h2 class="fw-bold mb-0 text-success">Active Pipeline</h2>
                        </div>
                        <div class="bg-light text-success p-3 rounded-circle"><i class="bi bi-shield-check h2 mb-0"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Anonymized Regional Soil Telemetry Feed</h5>
                    <p class="text-muted small mb-0">This ledger tracks aggregate agricultural requests within your operational boundary, helping you balance stock inventories for regional target crops.</p>
                </div>
                <span class="badge badge-dealer rounded-pill px-3 py-2"><i class="bi bi-patch-check-fill me-1"></i> Live DB Sync</span>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Log Reference Token</th>
                            <th>Indian Soil Profile</th>
                            <th>pH Level</th>
                            <th>Moisture Threshold</th>
                            <th>Predicted Crop Recommendation</th>
                        </tr>
                    </thead>
                    <tbody id="dealerMonitorTableBody">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Re-connecting telemetry handshake pipelines...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Indian Taxonomy Code Map Array for Vendor UI translation
    const indianSoilMap = {
        1: "Alluvial Soil",
        2: "Black Soil (Regur)",
        3: "Red/Yellow Soil",
        4: "Laterite Soil",
        5: "Arid/Desert Soil"
    };

    // Sidebar Layout Toggle
    document.getElementById('sidebarCollapse').addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // BACKEND API SYNC FOR DISTRIBUTED LEDGER DATA
    const loadRegionalTelemetryFeed = async () => {
        try {
            const response = await fetch(`../api/get_farmer_soil_logs.php?t=${Date.now()}`);
            const data = await response.json();
            const tableBody = document.getElementById("dealerMonitorTableBody");

            if (!tableBody) return;

            if (data.status === "success") {
                if (!data.logs || data.logs.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-hdd-network me-2"></i>No regional soil logging data recorded on this ledger sector.</td></tr>`;
                    document.getElementById("cardDemandsCount").textContent = "0 Logs";
                    return;
                }

                document.getElementById("cardDemandsCount").textContent = `${data.logs.length} Submissions`;

                tableBody.innerHTML = data.logs.map(log => {
                    const translatedSoil = indianSoilMap[log.soil_type] || `Unknown Profile (${log.soil_type})`;
                    return `
                        <tr>
                            <td><code>#REG-LOG-${log.id}</code></td>
                            <td><span class="badge bg-success-subtle text-success fw-bold px-3 py-1.5">${translatedSoil}</span></td>
                            <td><strong>${parseFloat(log.ph_level).toFixed(1)}</strong></td>
                            <td><span class="text-primary fw-medium">${parseFloat(log.moisture_level).toFixed(1)}%</span></td>
                            <td><span class="badge bg-light text-dark border border-secondary-subtle text-uppercase px-3 py-1.5 font-monospace">${log.crop_recommendation}</span></td>
                        </tr>
                    `;
                }).join('');

            } else {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">API Pipeline Sync Interrupted: ${data.message}</td></tr>`;
            }
        } catch (err) {
            console.error("Dealer synchronization routine fault:", err);
            document.getElementById("dealerMonitorTableBody").innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4"><i class="bi bi-exclamation-octagon-fill me-2"></i>Network Communication Error.</td></tr>`;
        }
    };

    // Instantiate execution loops on frame load
    document.addEventListener("DOMContentLoaded", loadRegionalTelemetryFeed);
</script>
</body>
</html>