<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card">
        <h2 class="text-center mb-5">Fertilizer Recommendation</h2>
        
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="t">Temperature</label>
                    <input type="number" step="0.01" name="t" class="form-control" placeholder="Example: 26" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="h">Humidity</label>
                    <input type="number" step="0.01" name="h" class="form-control" placeholder="Example: 52" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="sm">Soil Moisture</label>
                    <input type="number" step="0.01" name="sm" class="form-control" placeholder="Example: 38" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="sf">Soil Type</label>
                    <select name="sf" class="form-control" required>
                        <option value="">Select Soil Type</option>
                        <option value="Sandy">Sandy</option>
                        <option value="Loamy">Loamy</option>
                        <option value="Black">Black</option>
                        <option value="Red">Red</option>
                        <option value="Clayey">Clayey</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="cf">Crop Type</label>
                    <select name="cf" class="form-control" required>
                        <option value="">Select Crop Type</option>
                        <option value="Maize">Maize</option>
                        <option value="Sugarcane">Sugarcane</option>
                        <option value="Cotton">Cotton</option>
                        <option value="Tobacco">Tobacco</option>
                        <option value="Paddy">Paddy</option>
                        <option value="Barley">Barley</option>
                        <option value="Wheat">Wheat</option>
                        <option value="Millets">Millets</option>
                        <option value="Oil seeds">Oil seeds</option>
                        <option value="Pulses">Pulses</option>
                        <option value="Ground Nuts">Ground Nuts</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="n">Nitrogen</label>
                    <input type="number" step="0.01" name="n" class="form-control" placeholder="Example: 37" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="p">Phosphorous</label>
                    <input type="number" step="0.01" name="p" class="form-control" placeholder="Example: 0" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="k">Potassium</label>
                    <input type="number" step="0.01" name="k" class="form-control" placeholder="Example: 0" required>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" name="Fert_Recommend" class="btn-primary">Recommend Fertilizer</button>
            </div>
        </form>

<?php 
if(isset($_POST['Fert_Recommend'])){

    // Get form inputs
    $n  = trim($_POST['n']);
    $p  = trim($_POST['p']);
    $k  = trim($_POST['k']);
    $t  = trim($_POST['t']);
    $h  = trim($_POST['h']);
    $sm = trim($_POST['sm']);
    $sf = trim($_POST['sf']);
    $cf = trim($_POST['cf']);

    // Escape all values
    $n_esc  = escapeshellarg($n);
    $p_esc  = escapeshellarg($p);
    $k_esc  = escapeshellarg($k);
    $t_esc  = escapeshellarg($t);
    $h_esc  = escapeshellarg($h);
    $sm_esc = escapeshellarg($sm);
    $sf_esc = escapeshellarg($sf);
    $cf_esc = escapeshellarg($cf);

    // Full Python path
    $python = "python";
    //$python = "python";
    // Build command
    $command = "\"$python\" ML/fertilizer_recommendation/fertilizer_recommendation.py $n_esc $p_esc $k_esc $t_esc $h_esc $sm_esc $sf_esc $cf_esc";

    // Execute ML
    $output = trim(shell_exec($command . " 2>&1"));

    // Display Result Card
    echo '<div class="result-card" id="fertilizer-result">';
    echo '  <div class="result-card__header">';
    echo '    <h3 class="result-card__title"><span class="rc-icon">🌾</span> Recommendation Result</h3>';
    echo '  </div>';
    echo '  <div class="result-card__context">';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌱</span> ' . htmlspecialchars($cf) . '</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🏔</span> ' . htmlspecialchars($sf) . ' Soil</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌡</span> ' . htmlspecialchars($t) . '°C</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">💧</span> Moisture: ' . htmlspecialchars($sm) . '%</span>';
    echo '  </div>';
    echo '  <hr class="result-card__divider">';
    echo '  <div class="result-card__main">';
    echo '    <div class="result-card__label">✅ Recommended Fertilizer</div><br>';
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
    echo '    <div class="result-card__actions-title"><span>💡</span> Usage Guidance</div>';
    echo '    <ul class="result-card__actions-list">';
    echo '      <li><span class="action-icon">📏</span> Apply the recommended dosage as per soil test report</li>';
    echo '      <li><span class="action-icon">⏰</span> Best applied during early morning or late evening</li>';
    echo '      <li><span class="action-icon">💧</span> Water the field after fertilizer application for better absorption</li>';
    echo '      <li><span class="action-icon">🔄</span> Rotate fertilizer types across seasons to avoid soil degradation</li>';
    echo '      <li><span class="action-icon">⚠️</span> Avoid over-application — excess fertilizer can harm crops and groundwater</li>';
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

<?php include 'includes/footer.php'; ?>
