document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const alertBox = document.getElementById("alertBox");

    const showAlert = (message, type) => {
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = message;
        alertBox.classList.remove("d-none");
    };

    // Handle Registration Submit Action
    if (registerForm) {
        registerForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);

            try {
                const response = await fetch("../api/register.php", {
                    method: "POST",
                    body: formData
                });
                const data = await response.json();

                if (data.status === "success") {
                    showAlert(data.message, "success");
                    setTimeout(() => window.location.href = "login.html", 2000);
                } else {
                    showAlert(data.message, "danger");
                }
            } catch (error) {
                showAlert("An unexpected network fault occurred.", "danger");
            }
        });
    }

    // Handle Login Submit Action
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);

            try {
                const response = await fetch("../api/login.php", {
                    method: "POST",
                    body: formData
                });
                const data = await response.json();

                if (data.status === "success") {
                    showAlert("Authentication successful! Redirecting...", "success");
                    // Route user dynamically based on role response field from index directory tracking
                    setTimeout(() => window.location.href = `../${data.redirect}`, 1000);
                } else {
                    showAlert(data.message, "danger");
                }
            } catch (error) {
                showAlert("An unexpected network fault occurred.", "danger");
            }
        });
    }
});