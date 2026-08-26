document.addEventListener("DOMContentLoaded", () => {
    // Select dashboard metric card and display components
    const userGreeting = document.getElementById("userGreeting");
    const phMetric = document.querySelector(".card h3.text-dark"); // Selects first metric text
    const activityTable = document.getElementById("dashboardActivityTable");

    // Dynamic selectors based on card grouping structures
    const metricHeaders = document.querySelectorAll(".card h3.fw-bold");
    
    const fetchDashboardTelemetry = async () => {
        try {
            const response = await fetch("../api/get_soil_data.php");
            
            if (!response.ok) {
                if (response.status === 403) {
                    window.location.href = "../farmer/login.html";
                    return;
                }
                throw new Error("Network metrics response failure.");
            }

            const data = await response.json();

            if (data.status === "success") {
                // Update identity greeting panels
                if(userGreeting) {
                    userGreeting.textContent = `Welcome, ${data.username}`;
                }

                // Hydrate summary metric cards safely
                if (metricHeaders.length >= 3) {
                    metricHeaders[0].textContent = data.metrics.recent_ph;
                    metricHeaders[1].textContent = `${data.metrics.active_crops} Crops`;
                    metricHeaders[2].textContent = `${data.metrics.pending_orders} Active`;
                }

                // Hydrate historical telemetry log rows
                renderActivityTable(data.records);
            } else {
                console.error("System alert message payload:", data.message);
                showTableError(data.message);
            }
        } catch (error) {
            console.error("Dashboard engine connection error:", error);
            showTableError("Failed to synchronize dashboard metrics.");
        }
    };

    const renderActivityTable = (records) => {
        if (!activityTable) return;
        
        if (records.length === 0) {
            activityTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-info-circle me-2"></i>No historical soil analytics telemetry recorded.
                    </td>
                </tr>`;
            return;
        }

        // Generate and safely inject sanitized structural table layouts
        activityTable.innerHTML = records.map(record => {
            // Clean temporal processing outputs 
            const formattedDate = new Date(record.analysis_date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            return `
                <tr>
                    <td><strong>#SR-${record.id}</strong></td>
                    <td>${formattedDate}</td>
                    <td><span class="badge bg-success-subtle text-success">${record.nitrogen_level} mg/kg</span></td>
                    <td><span class="badge bg-primary-subtle text-primary">${record.phosphorus_level} mg/kg</span></td>
                    <td><span class="badge bg-warning-subtle text-warning">${record.potassium_level} mg/kg</span></td>
                    <td><span class="fw-semibold text-dark">${record.crop_recommendation || 'Processing...'}</span></td>
                </tr>
            `;
        }).join('');
    };

    const showTableError = (message) => {
        if (!activityTable) return;
        activityTable.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ${message}
                </td>
            </tr>`;
    };

    // Run initial data synchronization
    fetchDashboardTelemetry();
});