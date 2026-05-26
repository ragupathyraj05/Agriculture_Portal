<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card">
        <h2 class="text-center mb-5">Crop Recommendation</h2>
        
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="n">Nitrogen (N)</label>
                    <input type="number" step="0.01" name="n" class="form-control" placeholder="Example: 90" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="p">Phosphorus (P)</label>
                    <input type="number" step="0.01" name="p" class="form-control" placeholder="Example: 42" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="k">Potassium (K)</label>
                    <input type="number" step="0.01" name="k" class="form-control" placeholder="Example: 43" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="t">Temperature</label>
                    <input type="number" step="0.01" name="t" class="form-control" placeholder="Example: 20.8" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="h">Humidity</label>
                    <input type="number" step="0.01" name="h" class="form-control" placeholder="Example: 82" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="ph">pH Value</label>
                    <input type="number" step="0.01" name="ph" class="form-control" placeholder="Example: 6.5" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="r">Rainfall</label>
                <input type="number" step="0.01" name="r" class="form-control" placeholder="Example: 202.9" required>
            </div>
            
            <div class="text-center">
                <button type="submit" name="Crop_Recommend" class="btn-primary">Recommend Crop</button>
            </div>
        </form>

        <?php 
if(isset($_POST['Crop_Recommend'])){

    // Get form inputs
    $n  = trim($_POST['n']);
    $p  = trim($_POST['p']);
    $k  = trim($_POST['k']);
    $t  = trim($_POST['t']);
    $h  = trim($_POST['h']);
    $ph = trim($_POST['ph']);
    $r  = trim($_POST['r']);

    // Escape arguments safely
    $n_esc  = escapeshellarg($n);
    $p_esc  = escapeshellarg($p);
    $k_esc  = escapeshellarg($k);
    $t_esc  = escapeshellarg($t);
    $h_esc  = escapeshellarg($h);
    $ph_esc = escapeshellarg($ph);
    $r_esc  = escapeshellarg($r);

    // ✅ Portable Python path (recommended)
    $python = "python";

    // If python not in PATH, use full path like:
    // $python = "C:\\Users\\YourUser\\AppData\\Local\\Programs\\Python\\Python311\\python.exe";

    // Build command
    $command = "$python ML/crop_recommendation/recommend.py $n_esc $p_esc $k_esc $t_esc $h_esc $ph_esc $r_esc";

    // Execute
    $output = trim(shell_exec($command . " 2>&1"));

    // Display Result Card
    echo '<div class="result-card" id="recommendation-result">';
    echo '  <div class="result-card__header">';
    echo '    <h3 class="result-card__title"><span class="rc-icon">🌾</span> Recommendation Result</h3>';
    echo '  </div>';
    echo '  <div class="result-card__context">';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌡</span> Temp: ' . htmlspecialchars($t) . '°C</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">💧</span> Humidity: ' . htmlspecialchars($h) . '%</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🧪</span> pH: ' . htmlspecialchars($ph) . '</span>';
    echo '    <span class="result-card__tag"><span class="tag-icon">🌧</span> Rain: ' . htmlspecialchars($r) . ' mm</span>';
    echo '  </div>';
    echo '  <hr class="result-card__divider">';
    echo '  <div class="result-card__main">';
    echo '    <div class="result-card__label">✅ Recommended Crops</div><br>';
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
    echo '      <li><span class="action-icon">💧</span> Use drip or sprinkler irrigation for water efficiency</li>';
    echo '      <li><span class="action-icon">🧪</span> Apply NPK fertilizer (N:' . htmlspecialchars($n) . ', P:' . htmlspecialchars($p) . ', K:' . htmlspecialchars($k) . ') as per soil needs</li>';
    echo '      <li><span class="action-icon">🌱</span> Test soil pH regularly and amend with lime or sulfur if needed</li>';
    echo '      <li><span class="action-icon">📊</span> Monitor weather forecasts for irrigation and pest management</li>';
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
