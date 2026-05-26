<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card">
        <h2 class="text-center mb-5">Crop Prediction</h2>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="state">State</label>
                <select onchange="print_city('state', this.selectedIndex);" id="sts" name="stt" class="form-control" required>
                    <option value="">Select State</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="district">District</label>
                <select id="state" name="district" class="form-control" required>
                    <option value="">Select District</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="season">Season</label>
                <select name="Season" class="form-control" required>
                    <option value="">Select Season</option>
                    <option value="Kharif">Kharif</option>
                    <option value="Whole Year">Whole Year</option>
                    <option value="Autumn">Autumn</option>
                    <option value="Rabi">Rabi</option>
                    <option value="Summer">Summer</option>
                    <option value="Winter">Winter</option>
                </select>
            </div>
            
            <div class="text-center">
                <button type="submit" name="Crop_Predict" class="btn-primary">Predict Crop</button>
            </div>
        </form>

        <?php 
        if(isset($_POST['Crop_Predict'])){
            $state = trim($_POST['stt']);
            $district = trim($_POST['district']);
            $season = trim($_POST['Season']);

            $JsonState = json_encode($state);
            $JsonDistrict = json_encode($district);
            $JsonSeason = json_encode($season);
            
            // Escape input
$state_esc = escapeshellarg($state);
$district_esc = escapeshellarg($district);
$season_esc = escapeshellarg($season);

// Full Python path
$python = "python";
//$python = "python";
// Build command
$command = "\"$python\" ML/crop_prediction/ZDecision_Tree_Model_Call.py $state_esc $district_esc $season_esc";

// Execute
$output = trim(shell_exec($command . " 2>&1"));

// Display Result Card
echo '<div class="result-card" id="prediction-result">';
echo '  <div class="result-card__header">';
echo '    <h3 class="result-card__title"><span class="rc-icon">🌾</span> Prediction Result</h3>';
echo '  </div>';
echo '  <div class="result-card__context">';
echo '    <span class="result-card__tag"><span class="tag-icon">📍</span> ' . htmlspecialchars($district) . '</span>';
echo '    <span class="result-card__tag"><span class="tag-icon">🌤</span> ' . htmlspecialchars($season) . ' Season</span>';
echo '    <span class="result-card__tag"><span class="tag-icon">🏛</span> ' . htmlspecialchars($state) . '</span>';
echo '  </div>';
echo '  <hr class="result-card__divider">';
echo '  <div class="result-card__main">';
echo '    <div class="result-card__label">✅ Predicted Crops</div><br>';
// Split output into individual crops and display as a list
$crops = preg_split('/\s{2,}|\n|,/', $output);
$crops = array_map('trim', $crops);
$crops = array_filter($crops);
if (count($crops) > 1) {
    echo '    <div class="result-card__value"><ul class="result-crop-list">';
    foreach ($crops as $crop) {
        echo '<li>' . htmlspecialchars($crop) . '</li>';
    }
    echo '</ul></div>';
} else {
    echo '    <div class="result-card__value">' . htmlspecialchars($output) . '</div>';
}
echo '  </div>';
echo '  <div class="result-card__actions">';
echo '    <div class="result-card__actions-title"><span>💡</span> Suggested Actions</div>';
echo '    <ul class="result-card__actions-list">';
echo '      <li><span class="action-icon">💧</span> Ensure proper irrigation for optimal crop growth</li>';
echo '      <li><span class="action-icon">🧪</span> Apply recommended nitrogen-based fertilizer</li>';
echo '      <li><span class="action-icon">🌱</span> Prepare soil with organic manure before sowing</li>';
echo '      <li><span class="action-icon">📅</span> Follow the seasonal calendar for best planting window</li>';
echo '    </ul>';
echo '  </div>';
echo '  <div class="result-card__footer">';
echo '    <span>🕒</span> Generated on ' . date('d M Y, h:i A');
echo '  </div>';
echo '</div>';

        }
        ?>

    </div>
</div>

<script src="assets/js/cities.js"></script> <!-- Need to copy this or create it -->
<script language="javascript">print_state("sts");</script>

<?php include 'includes/footer.php'; ?>

<?php
if(isset($_POST['Crop_Predict'])){
    echo "<pre>";
    echo shell_exec("python --version 2>&1");
    echo "</pre>";
    exit;
}
?>

