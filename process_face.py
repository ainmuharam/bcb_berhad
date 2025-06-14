#!/var/www/html/bcb_berhad/venv/bin/python
# process_face.py
import sys
import json
import base64
import cv2
import numpy as np
from deepface import DeepFace

# Read POST data from stdin
input_data = sys.stdin.read()
try:
    data = json.loads(input_data)
    image_data = base64.b64decode(data['image'])
    
    # Convert to OpenCV format
    nparr = np.frombuffer(image_data, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    
    # Your recognition logic (simplified test version)
    try:
        # Use a test image for verification (replace with your DB logic)
        test_img_path = "known_employee.jpeg"
        result = DeepFace.verify(img, test_img_path, model_name="Facenet", enforce_detection=False)
        
        if result["verified"] and result["distance"] < 0.4:
            print(json.dumps({"emp_id": "123"}))  # Return test ID
        else:
            print(json.dumps({"error": "No match found"}))
    
    except Exception as e:
        print(json.dumps({"error": str(e)}))

except Exception as e:
    print(json.dumps({"error": f"Processing error: {str(e)}"}))