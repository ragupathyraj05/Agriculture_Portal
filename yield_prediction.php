
<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card">
        <h2 class="text-center mb-5">Yield Prediction</h2>
        
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="state">State</label>
                    <select onchange="print_city('state', this.selectedIndex);" id="sts" name="stt" class="form-control" required>
                        <option value="">Select State</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="district">District</label>
                    <select id="state" name="district" class="form-control" required>
                        <option value="">Select District</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
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
                <div class="col-md-6 form-group">
                    <label for="crop">Crop</label>
                    <select name="crop" class="form-control" required>
                        <option value="">Select Crop</option>
                        <option value="Rice">Rice</option>
                        <option value="Maize">Maize</option>
                        <option value="Cotton">Cotton</option>
                        <option value="Sugarcane">Sugarcane</option>
                        <option value="Wheat">Wheat</option>
                        <option value="Millets">Millets</option>
                        <option value="Pulses">Pulses</option>
                        <option value="Ground Nuts">Ground Nuts</option>
                        <option value="Oil seeds">Oil seeds</option>
                        <option value="Barley">Barley</option>
                        <option value="Tobacco">Tobacco</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="area">Area (in Hectares)</label>
                    <input type="number" step="0.01" name="area" class="form-control" placeholder="Example: 1254" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="temp">Temperature</label>
                    <input type="number" step="0.01" name="temp" class="form-control" placeholder="Example: 26" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="rain">Rainfall</label>
                    <input type="number" step="0.01" name="rain" class="form-control" placeholder="Example: 202.9" required>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" name="Yield_Predict" class="btn-primary">Predict Yield</button>
            </div>
        </form>

        <?php 
if(isset($_POST['Yield_Predict'])){

    // Get form values
    $state    = trim($_POST['stt']);
    $district = trim($_POST['district']);
    $season   = trim($_POST['Season']);
    $crop     = trim($_POST['crop']);
    $area     = trim($_POST['area']);
    $temp     = trim($_POST['temp']);
    $rain     = trim($_POST['rain']);

    // Escape values safely for command line
    $state_esc    = escapeshellarg($state);
    $district_esc = escapeshellarg($district);
    $season_esc   = escapeshellarg($season);
    $crop_esc     = escapeshellarg($crop);
    $area_esc     = escapeshellarg($area);
    $temp_esc     = escapeshellarg($temp);
    $rain_esc     = escapeshellarg($rain);

    // Full Python path (important for XAMPP)
    $python = "python";
    //$python = "python";
    // Build command
    $command = "\"$python\" ML/yield_prediction/yield_prediction.py $state_esc $district_esc $season_esc $crop_esc $area_esc $temp_esc $rain_esc";

    // Execute Python ML
    $output = trim(shell_exec($command . " 2>&1"));

    // Display Result Card
    echo '<div class="result-card" id="yield-result">';
    echo '  <div class="result-card__header">';
    echo '    <h3 class="result-card__title"><span class="rc-icon">🌾</span> Prediction Result</h3>';
    echo '  </div>';
    echo '  <div class="result-card__context">';
    echo '    <span class="result-card__tag"><span class="tag-icon">📍</span> ' . htmlspecialchars($district) . '</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌤</span> ' . htmlspecialchars($season) . ' Season</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌱</span> ' . htmlspecialchars($crop) . '</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">📐</span> ' . htmlspecialchars($area) . ' Ha</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌡</span> ' . htmlspecialchars($temp) . '°C</span>';
    echo '  </div>';
    echo '  <hr class="result-card__divider">';
    echo '  <div class="result-card__main">';
    echo '    <div class="result-card__label">📊 Predicted Yield</div><br>';
    // Split output into individual items and display as a list if multiple
    $items = preg_split('/\s{2,}|\n|,/', $output);
    $items = array_map('trim', $items);
    $items = array_filter($items);
    if (count($items) > 1) {
        echo '    <div class="result-card__value"><ul class="result-crop-list">';
        foreach ($items as $item) {
            echo '<li>' . htmlspecialchars($item) . '</li>';
        }
        echo '</ul></div>';
    } else {
        echo '    <div class="result-card__value">' . htmlspecialchars($output) . '</div>';
    }
    echo '  </div>';
    echo '  <div class="result-card__actions">';
    echo '    <div class="result-card__actions-title"><span>💡</span> Improvement Tips</div>';
    echo '    <ul class="result-card__actions-list">';
    echo '      <li><span class="action-icon">🧪</span> Use high-quality seeds and balanced fertilizers to boost production</li>';
    echo '      <li><span class="action-icon">💧</span> Optimize irrigation schedule based on rainfall pattern (' . htmlspecialchars($rain) . ' mm)</li>';
    echo '      <li><span class="action-icon">🐛</span> Implement Integrated Pest Management (IPM) strategies</li>';
    echo '      <li><span class="action-icon">🔄</span> Practice crop rotation to maintain soil fertility</li>';
    echo '      <li><span class="action-icon">📈</span> Monitor crop health regularly with field inspections</li>';
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

<script src="assets/js/cities.js"></script>
<script language="javascript">print_state("sts");</script>

<?php include 'includes/footer.php'; ?>
