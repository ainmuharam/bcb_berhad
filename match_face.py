import os
import sys
import json
from deepface import DeepFace
os.environ["HOME"] = "/var/www"

def main():
    try:
        if len(sys.argv) < 2:
            return {"status": "error", "message": "No filename provided"}

        CAPTURED_IMAGE = os.path.join("/var/www/html/bcb_berhad/temp", sys.argv[1])
        ENROLLED_FOLDER = "/var/www/html/bcb_berhad/admin/employee_picture"

        if not os.path.exists(CAPTURED_IMAGE):
            return {"status": "error", "message": "Captured image not found"}

        # Get enrolled faces
        enrolled_faces = {}
        for filename in os.listdir(ENROLLED_FOLDER):
            if filename.lower().endswith(('.png', '.jpg', '.jpeg')):
                emp_id = os.path.splitext(filename)[0]
                full_path = os.path.join(ENROLLED_FOLDER, filename)
                enrolled_faces[emp_id] = full_path

        # Find matches
        for emp_id, img_path in enrolled_faces.items():
            try:
                result = DeepFace.verify(
                    img1_path=CAPTURED_IMAGE,
                    img2_path=img_path,
                    enforce_detection=False
                )
                if result["verified"]:
                    # Return just the employee ID as a string (not JSON) when matched
                    return emp_id
            except Exception as e:
                continue  # Skip any comparison errors

        return {"status": "no_match"}

    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    result = main()
    # If result is just an employee ID (string), print it directly
    if isinstance(result, str):
        print(result)
    # Otherwise print as JSON (for errors/no_match cases)
    else:
        print(json.dumps(result))