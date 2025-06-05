import cv2

def capture_image():
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("Error: Could not open camera.")
        return
    
    ret, frame = cap.read()
    if ret:
        cv2.imwrite("captured_image.jpg", frame)
        print("Image captured and saved.")
    else:
        print("Failed to capture image.")
    
    cap.release()

if __name__ == "__main__":
    capture_image()
