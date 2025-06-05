import sys
from deepface import DeepFace

def recognize_face(image_path):
    # For demo: just verify the face is detected in the image
    try:
        obj = DeepFace.analyze(img_path = image_path, actions = ['age', 'gender'])
        # You can customize your recognition logic here
        return f"Face detected: Age={obj['age']}, Gender={obj['gender']}"
    except Exception as e:
        return f"Error: {str(e)}"

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python testCamera.py <image_path>")
        sys.exit(1)

    image_path = sys.argv[1]
    result = recognize_face(image_path)
    print(result)
