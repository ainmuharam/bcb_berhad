import os
import sys
from deepface import DeepFace

if len(sys.argv) < 2:
    print("No filename provided.")
    sys.exit(1)

CAPTURED_IMAGE = os.path.join("/var/www/html/bcb_berhad/temp", sys.argv[1])
ENROLLED_FOLDER = "/var/www/html/bcb_berhad/admin/employee_picture"

def get_enrolled_faces_from_folder():
    enrolled_faces = {}

    for filename in os.listdir(ENROLLED_FOLDER):
        if filename.lower().endswith(('.png', '.jpg', '.jpeg')):
            emp_id = os.path.splitext(filename)[0]  # get employee ID from filename
            full_path = os.path.join(ENROLLED_FOLDER, filename)
            enrolled_faces[emp_id] = full_path

    return enrolled_faces

def find_match(captured_image_path, enrolled_faces):
    for emp_id, img_path in enrolled_faces.items():
        try:
            result = DeepFace.verify(img1_path=captured_image_path, img2_path=img_path, enforce_detection=True)
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

    faces = get_enrolled_faces_from_folder()
    emp_id, filename = find_match(CAPTURED_IMAGE, faces)

    if emp_id:
        print(f"MATCHED: {filename}")
    else:
        print("NO MATCH")
