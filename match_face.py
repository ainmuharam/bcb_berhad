import os
import sys
import mysql.connector
from deepface import DeepFace

if len(sys.argv) < 2:
    print("No filename provided.")
    sys.exit(1)

CAPTURED_IMAGE = os.path.join("/var/www/html/bcb_berhad/temp", sys.argv[1])

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


def find_match(captured_image_path, enrolled_faces):
    for emp_id, img_path in enrolled_faces.items():
        try:
            result = DeepFace.verify(img1_path=captured_image_path, img2_path=img_path, enforce_detection=False)
            if result["verified"]:
                print(f"✅ Match found: {emp_id} ({os.path.basename(img_path)})")
                return emp_id, os.path.basename(img_path)
        except Exception as e:
            print(f"Error comparing with {img_path}: {e}")
    print("❌ No match found.")
    return None, None


if __name__ == "__main__":
    if not os.path.exists(CAPTURED_IMAGE):
        print("Captured image not found.")
        sys.exit(1)

    faces = get_enrolled_faces()
    emp_id, filename = find_match(CAPTURED_IMAGE, faces)

    if emp_id:
        print(f"MATCHED: {filename}")
    else:
        print("NO MATCH")
