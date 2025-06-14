import cv2
import sys
import os
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
    ycrcb = cv2.cvtColor(image, cv2.COLOR_BGR2YCrCb)
    ycrcb[:, :, 0] = cv2.equalizeHist(ycrcb[:, :, 0])
    return cv2.cvtColor(ycrcb, cv2.COLOR_YCrCb2BGR)

def recognize_face(image_path, enrolled_faces):
    try:
        image = cv2.imread(image_path)
        if image is None:
            print("Error: Image not found or unreadable.")
            return None

        face_cascade = cv2.CascadeClassifier('face_ref.xml')
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5, minSize=(60, 60))

        if len(faces) == 0:
            print("No face detected.")
            return None

        for (x, y, w, h) in faces:
            face_roi = image[y:y+h, x:x+w]
            normalized = normalize_lighting(face_roi)
            face_rgb = cv2.cvtColor(normalized, cv2.COLOR_BGR2RGB)

            for emp_id, enrolled_path in enrolled_faces.items():
                try:
                    result = DeepFace.verify(
                        face_rgb,
                        enrolled_path,
                        model_name="Facenet",
                        enforce_detection=False
                    )
                    if result["verified"] and result["distance"] < 0.4:
                        return emp_id
                except Exception as e:
                    print(f"Comparison error with {emp_id}: {e}")

    except Exception as e:
        print(f"Recognition error: {e}")
    
    return None

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python match_face.py <image_path> <action>")
        sys.exit(1)

    image_path = sys.argv[1]
    action = sys.argv[2]  # Not used now, but can be logged or extended

    enrolled_faces = get_enrolled_faces()
    matched_emp_id = recognize_face(image_path, enrolled_faces)

    if matched_emp_id:
        print(matched_emp_id)
        sys.exit(0)
    else:
        print("No match found")
        sys.exit(1)
