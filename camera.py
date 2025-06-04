import cv2
import sys
import os
import time  # Added for timer
import mysql.connector
from deepface import DeepFace

def get_enrolled_faces():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="bcb_berhad"
        )
        cursor = conn.cursor()

        cursor.execute("SELECT emp_id, profile_picture FROM users WHERE status = 1")
        results = cursor.fetchall()

        enrolled_faces = {}  # Dictionary to store emp_id and image path

        for emp_id, img_path in results:
            if img_path:
                full_path = os.path.join(r"C:\xampp\htdocs\bcb_berhad\admin", img_path)
                if os.path.exists(full_path):  # Ensure the image exists
                    enrolled_faces[emp_id] = full_path  # Store absolute path

        return enrolled_faces

    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        sys.exit(1)

    finally:
        cursor.close()
        conn.close()

face_cascade = cv2.CascadeClassifier('face_ref.xml')

def normalize_lighting(image):
    """Enhance image contrast and brightness to handle poor lighting."""
    ycrcb = cv2.cvtColor(image, cv2.COLOR_BGR2YCrCb)
    ycrcb[:, :, 0] = cv2.equalizeHist(ycrcb[:, :, 0])  # Equalize Y channel
    return cv2.cvtColor(ycrcb, cv2.COLOR_YCrCb2BGR)

def recognize_face(cropped_face, enrolled_faces):
    try:
        normalized_face = normalize_lighting(cropped_face)
        cropped_face_rgb = cv2.cvtColor(normalized_face, cv2.COLOR_BGR2RGB)
        resized_face = cv2.resize(cropped_face_rgb, (160, 160))  # For Facenet

        for emp_id, img_path in enrolled_faces.items():
            result = DeepFace.verify(
                resized_face,
                img_path,
                model_name="Dlib",
                enforce_detection=False,
                framework='pytorch'
            )

            if result["verified"] and result["distance"] < 0.4:
                return emp_id

    except Exception as e:
        print(f"Error in face recognition: {e}")
    
    return None



def open_camera(action):
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("Error: Could not open camera.")
        sys.exit(1)

    enrolled_faces = get_enrolled_faces()  # Get employee images from database
    matched_emp_id = None
    start_time = None  # Timer for unmatched faces

    while True:
        ret, frame = cap.read()
        if not ret:
            print("Error: Could not read frame.")
            break

        # Convert to grayscale for face detection
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        # Detect faces
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5, minSize=(30, 30))

        if len(faces) > 0:
            if start_time is None:  # Start timer when face is detected
                start_time = time.time()

        for (x, y, w, h) in faces:
            face_roi = frame[y:y+h, x:x+w]  # Crop the detected face
            
            # Recognize face using DeepFace
            matched_emp_id = recognize_face(face_roi, enrolled_faces)

            if matched_emp_id:
                color = (0, 255, 0)  
                message = f"Employee ID: {matched_emp_id}"
            else:
                color = (0, 0, 255)  # ❌ Red for unmatched face
                message = "No match found"

            cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
            cv2.putText(frame, message, (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2, cv2.LINE_AA)

            if matched_emp_id:
                break  # Exit loop after recognizing a face

        # Display action mode text
        cv2.putText(frame, f"{action} Mode", (50, 50), cv2.FONT_HERSHEY_SIMPLEX, 
                    1, (0, 255, 0), 2, cv2.LINE_AA)

        cv2.imshow("Camera", frame)

        # **Check if 3 seconds passed with no match**
        if start_time and time.time() - start_time >= 3 and matched_emp_id is None:
            print("No match found (timeout)")
            break  # Auto-exit camera after 3 seconds

        if matched_emp_id:
            break  # Exit loop when a recognized face is found

        # Press 'q' to close the camera manually
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()

    if matched_emp_id:
        print(matched_emp_id) 
        sys.exit(0)  # Return success
    else:
        print("No match found")  # ❌ Print "No match found"
        sys.exit(1)  # Return failure

if __name__ == "__main__":
    if len(sys.argv) > 1:
        action = sys.argv[1]  # Get 'clock_in' or 'clock_out' from PHP
        open_camera(action)
    else:
        print("Usage: python camera.py <clock_in/clock_out>")