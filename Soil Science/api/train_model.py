import os
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.svm import SVC
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, classification_report
import joblib

# Coordinate system paths
base_dir = os.path.dirname(os.path.abspath(__file__))
dataset_path = os.path.join(base_dir, 'crop_dataset.csv')
model_path = os.path.join(base_dir, 'crop_model.pkl')
scaler_path = os.path.join(base_dir, 'scaler.pkl')

print("=" * 60)
print("🤖 DATA AUGMENTATION HYPER-ENGINE - SOIL SCIENCE SOLUTIONS")
print("=" * 60)

try:
    if not os.path.exists(dataset_path):
        raise FileNotFoundError(f"Missing core ledger at: {dataset_path}")
        
    df = pd.read_csv(dataset_path)
    df.columns = df.columns.str.strip()

    # --- 🌾 DATA AUGMENTATION LOOP ---
    # Automatically scales small datasets to high densities using safe biological variances
    augmented_rows = []
    np.random.seed(42) # Lock variance steps for reproducible training passes

    for _, row in df.iterrows():
        # Keep original sample row
        augmented_rows.append(row.to_dict())
        
        # Generate 9 highly realistic synthetic variations per row
        for _ in range(9):
            mutated_row = {
                'soil_type': row['soil_type'], # Categorical index stays identical
                'ph': max(4.0, min(8.5, row['ph'] + np.random.normal(0, 0.15))),
                'moisture': max(5.0, min(40.0, row['moisture'] + np.random.normal(0, 0.8))),
                'temperature': row['temperature'] + np.random.normal(0, 0.5),
                'rainfall': max(200.0, row['rainfall'] + np.random.normal(0, 25.0)),
                'crop': row['crop']
            }
            augmented_rows.append(mutated_row)

    augmented_df = pd.DataFrame(augmented_rows)
    print(f"📈 Synthetic Data Pipeline Active: Expanded 80 rows into {len(augmented_df)} dense records.")

    # Feature target separation
    X = augmented_df[['soil_type', 'ph', 'moisture', 'temperature', 'rainfall']]
    y = augmented_df['crop']

    # 1. STANDARDIZE THE FEATURE BALANCER
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(X)
    joblib.dump(scaler, scaler_path)

    # 2. DATASET SPLIT (80% training / 20% validation verification)
    X_train, X_test, y_train, y_test = train_test_split(
        X_scaled, y, 
        test_size=0.20, 
        random_state=42, 
        stratify=y
    )

    # 3. HIGH-DENSITY SVM SEPARATION MATRIX
    model = SVC(kernel='rbf', C=15.0, gamma='scale', class_weight='balanced', random_state=42)
    model.fit(X_train, y_train)

    # Validate high-density performance
    predictions = model.predict(X_test)
    accuracy = accuracy_score(y_test, predictions)

    # Save compiled model weights binary
    joblib.dump(model, model_path)
    
    print("✨ SUCCESS: Augmentation classification pipeline compiled successfully.")
    print(f"🏆 Optimized Model Boundary Accuracy: {accuracy * 100:.1f}%\n")

    print("📝 DETAILED TECHNICAL CLASSIFICATION REPORT:")
    print(classification_report(y_test, predictions, zero_division=0)) 
    print("=" * 60)

except Exception as e:
    print(f"❌ COMPILATION CRITICAL FAULT: {str(e)}")
    print("=" * 60)