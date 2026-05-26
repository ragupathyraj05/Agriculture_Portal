# Rainfall Prediction Script using Statistical Average (Time-Series)
import pandas as pd
import sys
import os

# Get absolute path of this file
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
csv_path = os.path.join(BASE_DIR, "rainfall_in_india_1901-2015.csv")

# Load CSV
df = pd.read_csv(csv_path)

# Clean subdivision names
df["SUBDIVISION"] = df["SUBDIVISION"].str.strip().str.upper()

def predict_rainfall(region, month):

    region = region.strip().upper()
    month = month.strip().upper()

    # Filter rows for the selected subdivision
    state_data = df[df["SUBDIVISION"] == region]

    if state_data.empty:
        return "Invalid region"

    # Check month column exists
    if month not in df.columns:
        return "Invalid month"

    # Calculate average rainfall for that month across all years
    avg_rainfall = state_data[month].mean()

    return round(avg_rainfall, 2)


# Inputs from PHP
Jregion = sys.argv[1]
Jmonth = sys.argv[2]

result = predict_rainfall(Jregion, Jmonth)

print("Expected Rainfall:")
print(result, "mm")
