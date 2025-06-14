import cv2
import sys
import os
import time
import mysql.connector
from deepface import DeepFace

def get_enrolled_faces():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="Nurainmuharam02@",
            database="bcb_berhad"
        )
        cursor = conn.cursor()
        cursor.execute("SELECT emp_id, profile_picture FROM users WHERE status = 1")
        results = cursor.fetchall()

        enrolled_faces = {}
        for emp_id, img_path in results:
            if img_path:
            full_path = os.path.join("/var/www/html/bcb_berhad/admin/employee_picture", img_path)
                if os.path.exists(full_path):
                    enrolled_faces[emp_id] = full_path

        return enrolled_faces

    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        sys.exit(1)
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals() and conn.is_connected():
            conn.close()

def normalize_lighting(image):
    """Normalize lighting by equalizing the histogram of the Y channel in YCrCb color space."""
    ycrcb = cv2.cvtColor(image, cv2.COLOR_BGR2YCrCb)
    ycrcb[:, :, 0] = cv2.equalizeHist(ycrcb[:, :, 0])
    return cv2.cvtColor(ycrcb, cv2.COLOR_YCrCb2BGR)

def recognize_face(cropped_face, enrolled_faces):
    try:
        normalized_face = normalize_lighting(cropped_face)
        face_rgb = cv2.cvtColor(normalized_face, cv2.COLOR_BGR2RGB)

        for emp_id, img_path in enrolled_faces.items():
            print(f"Comparing with {emp_id}: {img_path}")
            result = DeepFace.verify(
                face_rgb,
                img_path,
                model_name="Facenet",
                enforce_detection=False
            )
            print(f"Result for {emp_id}: {result}")

            if result["verified"] and result["distance"] < 0.4:
                return emp_id

    except Exception as e:
        print(f"Recognition error: {e}")
    return None


def open_camera(action):
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("Error: Could not open camera.")
        sys.exit(1)

    face_cascade = cv2.CascadeClassifier('face_ref.xml')
    enrolled_faces = get_enrolled_faces()
    matched_emp_id = None
    start_time = None

    while True:
        ret, frame = cap.read()
        if not ret:
            print("Error: Frame capture failed.")
            break

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5, minSize=(30, 30))

        if len(faces) > 0 and start_time is None:
            start_time = time.time()

        for (x, y, w, h) in faces:
            face_roi = frame[y:y+h, x:x+w]
            matched_emp_id = recognize_face(face_roi, enrolled_faces)

            color = (0, 255, 0) if matched_emp_id else (0, 0, 255)
            label = f"Employee ID: {matched_emp_id}" if matched_emp_id else "No match"
            cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
            cv2.putText(frame, label, (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2)

            if matched_emp_id:
                break

        cv2.putText(frame, f"{action.capitalize()} Mode", (10, 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (100, 255, 100), 2)

        cv2.imshow("Camera", frame)

        if start_time and time.time() - start_time >= 3 and matched_emp_id is None:
            print("No match found")
            break

        if matched_emp_id:
            break

        if cv2.waitKey(1) & 0xFF == ord('q'):
            print("User quit")
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
    if len(sys.argv) < 2:
        print("Usage: python match_face.py <action>")
        sys.exit(1)

    action = sys.argv[1]
    open_camera(action)


