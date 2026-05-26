# Yield Prediction Script using Random Forest Regressor
import warnings
warnings.filterwarnings("ignore")

import pandas as pd
import numpy as np
import sys
import os
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import OneHotEncoder

# Absolute path to CSV
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
csv_path = os.path.join(BASE_DIR, "crop_production_karnataka.csv")

# Load dataset
df = pd.read_csv(csv_path)

# Drop unwanted column
df = df.drop(['Crop_Year'], axis=1)

# Features & target
X = df.drop(['Production'], axis=1)
y = df['Production']

# Train-test split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Categorical columns
categorical_cols = ['State_Name', 'District_Name', 'Season', 'Crop']

# One-hot encoding
ohe = OneHotEncoder(handle_unknown='ignore')
ohe.fit(X_train[categorical_cols])

# Encode training data
X_train_cat = ohe.transform(X_train[categorical_cols])
X_train_num = X_train.drop(categorical_cols, axis=1).values
X_train_final = np.hstack((X_train_cat.toarray(), X_train_num))

# Train model
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train_final, y_train)

# Inputs from PHP
Jstate    = sys.argv[1]
Jdistrict = sys.argv[2]
Jseason   = sys.argv[3]
Jcrop     = sys.argv[4]
Jarea     = float(sys.argv[5])

# Build input DataFrame (VERY important for OneHotEncoder)
user_df = pd.DataFrame([[Jstate, Jdistrict, Jseason, Jcrop, Jarea]],
                       columns=['State_Name','District_Name','Season','Crop','Area'])

# One-hot encode user input
user_cat = ohe.transform(user_df[categorical_cols])
user_num = user_df[['Area']].values
user_final = np.hstack((user_cat.toarray(), user_num))

# Predict
prediction = model.predict(user_final)[0]

# Clean output
print("Predicted Yield:")
print(round(prediction, 2), "kg")
