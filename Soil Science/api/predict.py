import sys
import json
import os
import joblib
import pandas as pd
import warnings
warnings.filterwarnings("ignore")

def run_prediction():
    try:
        # 🎯 READ JSON PIPED FROM PHP STDIN
        raw_input = sys.stdin.read()
        if not raw_input:
            raise ValueError("No input data received via stdin.")
            
        input_data = json.loads(raw_input)
        
        # Load model and scaler
        base_dir = os.path.dirname(os.path.abspath(__file__))
        model = joblib.load(os.path.join(base_dir, 'crop_model.pkl'))
        scaler = joblib.load(os.path.join(base_dir, 'scaler.pkl'))

        # Prepare features
        features = pd.DataFrame([{
            'soil_type': float(input_data['soil_type']),
            'ph': float(input_data['ph']),
            'moisture': float(input_data['moisture']),
            'temperature': float(input_data['temperature']),
            'rainfall': float(input_data['rainfall'])
        }])

        # Predict
        prediction = model.predict(scaler.transform(features))[0]
        
        # Output clean JSON to stdout
        print(json.dumps({"status": "success", "crop_recommendation": str(prediction)}))

    except Exception as e:
        # Output error to stdout so PHP can catch it
        print(json.dumps({"status": "error", "message": str(e)}))

if __name__ == "__main__":
    run_prediction()