# -*- coding: utf-8 -*-
"""Pomegrante.ipynb

Automatically generated by Colaboratory.

Original file is located at
    https://colab.research.google.com/drive/123qy-_o39CcytxLc-EIL9tO999Bt79ao
"""

import torch
import numpy as np
import torchvision
import torchvision.transforms as transforms
import matplotlib.pyplot as plt
import torch.nn as nn
import torch.optim as optim
import os
import cv2
from torchvision import datasets
from torch.utils.data import DataLoader
import torch.nn.functional as F
from sklearn.neighbors import KNeighborsClassifier
from sklearn.model_selection import train_test_split
from sklearn.decomposition import PCA
from tqdm import tqdm

from google.colab import drive
drive.mount('/content/drive')
folder_path = '/content/drive/MyDrive/SeniorCapstone/Datasets'

transform = transforms.Compose([
    transforms.Resize((300, 300)),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225])
])
train_dataset = datasets.ImageFolder('/content/drive/MyDrive/SeniorCapstone/Datasets/PomegranteDataset/Train', transform=transform)
test_dataset = datasets.ImageFolder('/content/drive/MyDrive/SeniorCapstone/Datasets/PomegranteDataset/Test', transform=transform)

train_loader = DataLoader(train_dataset, batch_size=32, shuffle=True)
test_loader = DataLoader(test_dataset, batch_size=32, shuffle=False)

# Resnet 18
import torchvision.transforms as transforms
from torchvision import models
model = models.resnet18(pretrained=True)
num_ftrs = model.fc.in_features
model.fc = torch.nn.Linear(num_ftrs, 2)
device = "cuda" if torch.cuda.is_available() else "cpu"
model = model.to(device)
sgd_optimizer = optim.SGD(model.parameters(), lr=0.01)
loss_function = torch.nn.CrossEntropyLoss()

# Loading the Model
model_save_name = 'Pomegrante.pt'
path = F"/content/drive/MyDrive/SeniorCapstone/Datasets/PomegranteDataset/{model_save_name}"
model.load_state_dict(torch.load(path))

# Training
epoch_num = 6
epoch_loss_total = []
epoch_loss_test_total=[]
for epoch in range(epoch_num):

    model.train()
    epoch_loss = []
    for batch_idx, (image, label) in enumerate(train_loader):
        image = image.to(device)
        label = label.to(device)

        sgd_optimizer.zero_grad()
        # feed forword
        output = model(image)
        # calculate loss
        loss = loss_function(output, label)
        # Calculate Partial Derivative
        loss.backward()
        # Performs a single optimization step (parameter update).
        sgd_optimizer.step()
        epoch_loss.append(loss.item())
    print('Train loss:', np.mean(epoch_loss))
    epoch_loss_total.append(np.mean(epoch_loss))

    model.eval()
    epoch_loss_test = []
    with torch.inference_mode():
      for batch_idx, (image, label) in enumerate(test_loader):
          image = image.to(device)
          label = label.to(device)
          sgd_optimizer.zero_grad()
          # feed forword
          output = model(image)
          # calculate loss
          loss = loss_function(output, label)
          epoch_loss_test.append(loss.item())
    print('Test loss:', np.mean(epoch_loss_test))
    epoch_loss_test_total.append(np.mean(epoch_loss_test))

plt.plot(epoch_loss_total)
plt.plot(epoch_loss_test_total)

from sklearn.metrics import confusion_matrix, accuracy_score

all_preds = []
all_labels = []

# Set model to evaluation mode
model.eval()

with torch.no_grad():  # Disables gradient calculation for efficiency
    for image, label in test_loader:
        image = image.to(device)
        label = label.to(device)
        outputs = model(image)
        _, predicted = torch.max(outputs, 1)

        # Store predictions and true labels
        all_preds.extend(predicted.cpu().numpy())
        all_labels.extend(label.cpu().numpy())

# Calculate accuracy
accuracy = accuracy_score(all_labels, all_preds)
print(f"Accuracy: {accuracy}")

model_save_name = 'Pomegrante.pt'
path = F"/content/drive/MyDrive/SeniorCapstone/Datasets/PomegranteDataset/{model_save_name}"
torch.save(model.state_dict(), path)