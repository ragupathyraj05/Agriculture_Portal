# Fertilizer Recommendation System using Decision Tree Classifier
import warnings
warnings.filterwarnings("ignore")

import pandas as pd
import sys
import os
from sklearn.preprocessing import LabelEncoder
from sklearn.tree import DecisionTreeClassifier

# Absolute CSV path
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
csv_path = os.path.join(BASE_DIR, "fertilizer_recommendation.csv")

# Load dataset
data = pd.read_csv(csv_path)

# Label encoding for categorical features
le_soil = LabelEncoder()
data['Soil Type'] = le_soil.fit_transform(data['Soil Type'])

le_crop = LabelEncoder()
data['Crop Type'] = le_crop.fit_transform(data['Crop Type'])

# Features and target
X = data.iloc[:, :8]
y = data.iloc[:, -1]

# Train model
dtc = DecisionTreeClassifier(random_state=0)
dtc.fit(X, y)

# Inputs from PHP
n     = float(sys.argv[1])
p     = float(sys.argv[2])
k     = float(sys.argv[3])
t     = float(sys.argv[4])
h     = float(sys.argv[5])
sm    = float(sys.argv[6])
soil  = sys.argv[7]
crop  = sys.argv[8]

# Encode categorical inputs
soil_enc = le_soil.transform([soil])[0]
crop_enc = le_crop.transform([crop])[0]

# Build input array (order must match training data)
user_input = [[t, h, sm, soil_enc, crop_enc, n, k, p]]

# Predict
fertilizer = dtc.predict(user_input)[0]

# Clean output
print("Recommended Fertilizer:")
print(fertilizer)
