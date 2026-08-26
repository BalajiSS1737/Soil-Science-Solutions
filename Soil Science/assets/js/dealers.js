document.addEventListener("DOMContentLoaded", () => {
    console.log("Dealers script initialized...");

    const catalogGrid = document.getElementById("catalogGrid");
    const categoryFilter = document.getElementById("categoryFilter");
    const requestForm = document.getElementById("marketplaceRequestForm");
    const alertBox = document.getElementById("marketplaceAlert");

    let localInventoryCache = [];

  const fetchMarketplaceCatalog = async () => {
    console.log("Checking marketplace...");
    try {
        const response = await fetch(`../api/get_dealers.php?t=${Date.now()}`);
        
        // Check if the response is actually valid JSON
        const text = await response.text();
        console.log("Raw API Response:", text); // Check your F12 console to see if this is "Unauthorized"
        
        const data = JSON.parse(text);

        if (data.status === "success") {
            localInventoryCache = data.inventory;
            renderCatalogCards(localInventoryCache);
            // Hide the spinner if successful
            const loadingSpinner = document.querySelector('.spinner-border');
            if(loadingSpinner) loadingSpinner.parentElement.innerHTML = "";
        } else {
            console.error("API returned error:", data.message);
            catalogGrid.innerHTML = `<div class="col-12 text-center text-danger py-4">Error: ${data.message}</div>`;
        }
    } catch (err) {
        console.error("Critical Sync Error:", err);
        catalogGrid.innerHTML = `<div class="col-12 text-center text-danger py-4">Sync Failed. Check Console (F12).</div>`;
    }
};

    const renderCatalogCards = (items) => {
        if (!catalogGrid) return;
        if (!items || items.length === 0) {
            catalogGrid.innerHTML = `<div class="col-12 text-center py-5">No products found.</div>`;
            return;
        }

        catalogGrid.innerHTML = items.map(item => `
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 p-4">
                    <h5 class="fw-bold">${item.product_name || 'Unnamed Product'}</h5>
                    <p>Category: ${item.category || 'N/A'}</p>
                    <p>Price: $${parseFloat(item.price || 0).toFixed(2)}</p>
                    <button class="btn btn-sm btn-success trigger-request-btn" 
                            data-id="${item.id}" 
                            data-name="${item.product_name}">Order</button>
                </div>
            </div>
        `).join('');

        // Re-bind buttons safely
        document.querySelectorAll(".trigger-request-btn").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const id = e.currentTarget.getAttribute("data-id");
                const name = e.currentTarget.getAttribute("data-name");
                console.log("Opening modal for:", name);
                
                // Safety check: Does bootstrap exist?
                if (typeof bootstrap !== 'undefined') {
                    const modalEl = document.getElementById('requestModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    alert("Bootstrap is not loaded!");
                }
            });
        });
    };

    fetchMarketplaceCatalog();
});