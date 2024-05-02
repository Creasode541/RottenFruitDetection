from flask import Flask, request, jsonify
import torch
import torchvision.transforms as transforms
from torchvision import models
from PIL import Image
import json
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

@app.route('/run_model', methods=['POST'])
def run_model():
    # Load the model architecture
    model = models.resnet18(pretrained=False)
    num_ftrs = model.fc.in_features
    model.fc = torch.nn.Linear(num_ftrs, 2)

    # Extract inputs from form data
    selected_fruit = request.form['fruitSelection']
    image_file = request.files['file']

    # Save image to a temporary location
    image_path = "temp_image.jpg"
    image_file.save(image_path)

    # Set model path based on selected fruit
    model_path = selected_fruit + ".pt"

    # Load the saved model weights
    try:
        model.load_state_dict(torch.load(model_path, map_location=torch.device('cpu')))
    except Exception as e:
        return jsonify({"error": "Error loading model"}), 500

    model.eval()

    # Define the transform for the input image
    transform = transforms.Compose([
        transforms.Resize((300, 300)),
        transforms.ToTensor(),
        transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225])
    ])

    # Load and preprocess the input image
    try:
        image = Image.open(image_path).convert("RGB")
        input_tensor = transform(image).unsqueeze(0)
    except Exception as e:
        return jsonify({"error": "Error loading image"}), 500

    # Classify the image
    with torch.no_grad():
        output = model(input_tensor)

    # Get the predicted class
    _, predicted_class = torch.max(output, 1)
    predicted_class = predicted_class.item()

    # Define class labels
    class_labels = ['Not Rotten', 'Rotten']

    # Adding disclaimer for if fruit is rotten
    result_message = class_labels[predicted_class]
    if result_message == 'Rotten':
        result_message += "Disclaimer: The classification provided by this software is intended as a recommendation based on image analysis and machine learning algorithms. Consuming fruit that is visually classified as 'Not Rotten' by this software does not guarantee its freshness or safety for consumption. Users are advised to exercise caution and use their own judgment when consuming any food item, as eating fruit that is rotten can pose health risks and may lead to adverse health effects."


    # Send result back to client
    result = {
        "class": class_labels[predicted_class]
    }
    return jsonify(result)

if __name__ == '__main__':
    app.run(debug=True)  # You may want to set debug=False in production
