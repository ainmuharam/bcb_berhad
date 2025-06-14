from deepface import DeepFace
import sys
import json

try:
    img1_path = sys.argv[1]
    img2_path = sys.argv[2]

    result = DeepFace.verify(img1_path, img2_path, enforce_detection=False)

    if result["verified"]:
        print("MATCH")
    else:
        print("NO_MATCH")

except Exception as e:
    print("ERROR:", str(e))
