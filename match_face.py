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

        model = DeepFace.build_model("Facenet")


        for emp_id, img_path in enrolled_faces.items():
            try:
                result = DeepFace.verify(
                    img1_path=CAPTURED_IMAGE,
                    img2_path=img_path,
                    enforce_detection=False,
                    model_name="Facenet",
                    model=model,
                )
            if result["verified"] and result["distance"] < 0.4:
                    return {
                        "status": "matched",
                        "employee_id": emp_id,
                        "filename": os.path.basename(img_path)
                    }
            except Exception:
                continue

        return {"status": "no_match"}

    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    result = main()
    print(json.dumps(result))  # Always print JSON for consistent PHP parsing
