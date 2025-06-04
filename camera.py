import cv2
import sys
import os
import time
import mysql.connector
from deepface import DeepFace

# Optional: suppress TensorFlow warnings if TensorFlow is installed but you don't want to use it
import warnings
warnings.filterwarnings("ignore")
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'  # Suppress TensorFlow logs

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
        enrolled_faces = {}
        for emp_id, img_path in results:
            if img_path:
                full_path = os.path.join(r"C:\xampp\htdocs\bcb_berhad\admin", img_path)
                if os.path.exists(full_path):
                    enrolled_faces[emp_id] = full_path
        return enrolled_faces
    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        sys.exit(1)
    finally:
        cursor.close()
        conn.close()

face_cascade = cv2.CascadeClassifier('face_ref.xml')

def normalize_lighting(image):
    ycrcb = cv2.cvtColor(image, cv2.COLOR_BGR2YCrCb)
    ycrcb[:, :, 0] = cv2.equalizeHist(ycrcb[:, :, 0])
    return cv2.cvtColor(ycrcb, cv2.COLOR_YCrCb2BGR)

def recognize_face(cropped_face, enrolled_faces):
    try:
        normalized_face = normalize_lighting(cropped_face)
        cropped_face_rgb = cv2.cvtColor(normalized_face, cv2.COLOR_BGR2RGB)
        resized_face = cv2.resize(cropped_face_rgb, (160, 160))

        for emp_id, img_path in enrolled_faces.items():
            result = DeepFace.verify(
                resized_face,
                img_path,
                model_name="Dlib",
                enforce_detection=False,
                framework='pytorch'  # FORCE PyTorch here
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

    enrolled_faces = get_enrolled_faces()
    matched_emp_id = None
    start_time = None

    while True:
        ret, frame = cap.read()
        if not ret:
            print("Error: Could not read frame.")
            break

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5, minSize=(30, 30))

        if len(faces) > 0 and start_time is None:
            start_time = time.time()

        for (x, y, w, h) in faces:
            face_roi = frame[y:y+h, x:x+w]
            matched_emp_id = recognize_face(face_roi, enrolled_faces)

            if matched_emp_id:
                color = (0, 255, 0)
                message = f"Employee ID: {matched_emp_id}"
            else:
                color = (0, 0, 255)
                message = "No match found"

            cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
            cv2.putText(frame, message, (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2, cv2.LINE_AA)

            if matched_emp_id:
                break

        cv2.putText(frame, f"{action} Mode", (50, 50), cv2.FONT_HERSHEY_SIMPLEX,
                    1, (0, 255, 0), 2, cv2.LINE_AA)

        cv2.imshow("Camera", frame)

        if start_time and time.time() - start_time >= 3 and matched_emp_id is None:
            print("No match found (timeout)")
            break

        if matched_emp_id:
            break

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()

    if matched_emp_id:
        print(matched_emp_id)
        sys.exit(0)
    else:
        print("No match found")
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) > 1:
        action = sys.argv[1]
        open_camera(action)
    else:
        print("Usage: python camera.py <clock_in/clock_out>")
