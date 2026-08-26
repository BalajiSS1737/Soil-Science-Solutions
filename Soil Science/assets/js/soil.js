document.addEventListener("DOMContentLoaded", () => {
    const soilForm = document.getElementById("soilAnalysisForm");
    const historyTable = document.getElementById("soilHistoryTable");
    const formAlert = document.getElementById("formAlert");

    const showMessage = (text, type) => {
        if (!formAlert) return;
        formAlert.className = `alert alert-${type}`;
        formAlert.textContent = text;
        formAlert.classList.remove("d-none");
    };

    const loadSoilHistory = async () => {
        try {
            const response = await fetch(`../api/get_soil_data.php?t=${Date.now()}`);
            const rawText = await response.text();
            
            // Check if server returned HTML instead of clean JSON
            if (!rawText.trim().startsWith('{')) {
                console.error("Server sent back bad formatting:", rawText);
                return;
            }

            const data = JSON.parse(rawText);
            if (data.status === "success" && historyTable) {
                if (data.records.length === 0) {
                    historyTable.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No analysis records found. Add your first sample above!</td></tr>`;
                    return;
                }

                historyTable.innerHTML = data.records.map(r => `
                    <tr>
                        <td><strong>#SR-${r.id}</strong></td>
                        <td>${new Date(r.analysis_date).toLocaleDateString()}</td>
                        <td>
                            <small class="d-block text-success">N: ${r.nitrogen_level}</small>
                            <small class="d-block text-primary">P: ${r.phosphorus_level}</small>
                            <small class="d-block text-warning">K: ${r.potassium_level}</small>
                        </td>
                        <td><span class="badge bg-dark">${r.ph_level}</span></td>
                        <td><span class="text-info">${r.moisture_level}%</span></td>
                        <td><span class="fw-bold text-success">${r.crop_recommendation || 'Pending'}</span></td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error("History loader crashed:", error);
        }
    };

    if (soilForm) {
        soilForm.addEventListener("submit", async (e) => {
            e.preventDefault(); // Stop the native browser reload trap
            
            const formData = new FormData(soilForm);
            showMessage("Processing metrics payload...", "info");

            try {
                const response = await fetch(`../api/save_soil_data.php?t=${Date.now()}`, {
                    method: "POST",
                    body: formData
                });

                const responseText = await response.text();

                // Diagnostic: Check if PHP returned a crash dump or database connection warning
                if (!responseText.trim().startsWith('{')) {
                    showMessage(`Server Configuration Error: ${responseText.substring(0, 150)}...`, "danger");
                    return;
                }

                const data = JSON.parse(responseText);

                if (data.status === "success") {
                    showMessage(`Sample logged! Recommended Crop: ${data.recommendation}`, "success");
                    soilForm.reset();
                    loadSoilHistory();
                } else {
                    showMessage(`Application error: ${data.message}`, "danger");
                }
            } catch (err) {
                showMessage(`System runtime fault: ${err.message}`, "danger");
            }
        });
    }

    loadSoilHistory();
});