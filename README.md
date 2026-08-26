# Soil-Science-Solutions
# Soil Science: Agricultural Input Marketplace & Inventory System

An integrated, web-based platform designed to bridge the gap between agricultural input suppliers (Dealers) and crop producers (Farmers). The system provides real-time inventory management for dealers and a direct procurement marketplace for farmers.

---

## 🚀 Key Features

* **Dealer Inventory Management:** Allows dealers to register, track, and update active warehouse stock lines, manage pricing (in Indian Rupees, ₹), and monitor low-stock alerts.
* **Agricultural Marketplace:** Empowers farmers to browse regional supplies categorized by fertilizers, seeds, pesticides, and tools with instant category filters.
* **Procurement & Requests:** Farmers can dispatch custom supply volume and drop-off instructions directly to regional vendors.
* **Role-Based Access Control:** Secure session handling ensuring separated workflows for Farmers and Dealers.

---

## 🛠️ Technology Stack

* **Frontend:** HTML5, Bootstrap 5, JavaScript (ES6+), Bootstrap Icons
* **Backend:** PHP (Native with PDO)
* **Database:** MySQL (Relational database with InnoDB engine constraints)

---

## 📂 Project Structure

```text
Soil Science/
│
├── api/                   # Backend PHP API endpoints
│   ├── add_product.php
│   ├── get_dealer_products.php
│   └── get_dealers.php
│
├── assets/                # Static frontend resources
│   ├── css/               # Custom stylesheets (style.css, dashboard.css)
│   └── js/                # Client-side scripts (dealers.js, etc.)
│
├── dealer/                # Dealer portal views
│   ├── products.html      # Inventory Manager
│   └── dashboard.html     # Dealer metrics & controls
│
├── farmer/                # Farmer portal views
│   └── dealers.html       # Marketplace & Supplier locator
│
├── includes/              # Shared core configuration
│   └── db_connect.php     # PDO Database connection script
│
└── schema.sql             # Database definition script
