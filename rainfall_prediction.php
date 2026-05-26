<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card">
        <h2 class="text-center mb-5">Rainfall Prediction</h2>
        
<form method="post" action="">

    <div class="form-group">
        <label>State</label>
        <select onchange="print_city('district', this.selectedIndex);" id="state" name="state" class="form-control" required>
            <option value="">Select State</option>
        </select>
    </div>

    <div class="form-group">
        <label>District</label>
        <select id="district" name="district" class="form-control" required>
            <option value="">Select District</option>
        </select>
    </div>

    <div class="form-group">
        <label>Month</label>
       <select id="month" name="month" class="form-control" required>
    <option value="">SELECT MONTH</option>
    <option value="JAN">JAN</option>
    <option value="FEB">FEB</option>
    <option value="MAR">MAR</option>
    <option value="APR">APR</option>
    <option value="MAY">MAY</option>
    <option value="JUN">JUN</option>
    <option value="JUL">JUL</option>
    <option value="AUG">AUG</option>
    <option value="SEP">SEP</option>
    <option value="OCT">OCT</option>
    <option value="NOV">NOV</option>
    <option value="DEC">DEC</option>
</select>

    </div>

    <button type="submit" name="Rainfall_Predict" class="btn-primary">
        Predict Rainfall
    </button>
</form>

        <?php 
        if(isset($_POST['Rainfall_Predict'])){

            $state    = trim($_POST['state']);
            $district = trim($_POST['district']);
            $month    = trim($_POST['month']);

            // District → Subdivision mapping for ML dataset
            $subdivision_map = [
                "Madurai" => "Tamil Nadu",
                "Chennai" => "Tamil Nadu",
                "Coimbatore" => "Tamil Nadu",
                "Trichy" => "Tamil Nadu",
                "Salem" => "Tamil Nadu",
                "Tirunelveli" => "Tamil Nadu",
                "Erode" => "Tamil Nadu",
                "Dindigul" => "Tamil Nadu",
                "Virudhunagar" => "Tamil Nadu"
            ];

            $region = $subdivision_map[$district] ?? "Tamil Nadu";

            // Escape values
            $region_esc = escapeshellarg($region);
            $month_esc  = escapeshellarg($month);

            // Full Python path
            $python = "python";
            //$python = "python";
            // Run ML
            $command = "\"$python\" ML/rainfall_prediction/rainfall_prediction.py $region_esc $month_esc";
            $output = trim(shell_exec($command . " 2>&1"));

            // Display Result Card
            echo '<div class="result-card" id="rainfall-result">';
            echo '  <div class="result-card__header">';
            echo '    <h3 class="result-card__title"><span class="rc-icon">🌾</span> Prediction Result</h3>';
            echo '  </div>';
            echo '  <div class="result-card__context">';
            echo '    <span class="result-card__tag"><span class="tag-icon">📍</span> ' . htmlspecialchars($district) . '</span>';
            echo '    <span class="result-card__tag"><span class="tag-icon">🏛</span> ' . htmlspecialchars($state) . '</span>';
            echo '    <span class="result-card__tag"><span class="tag-icon">📅</span> ' . htmlspecialchars($month) . '</span>';
            echo '  </div>';
            echo '  <hr class="result-card__divider">';
            echo '  <div class="result-card__main">';
    echo '    <div class="result-card__label">🌧 Predicted Rainfall</div><br>';
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
            echo '    <div class="result-card__actions-title"><span>💡</span> Weather Advisory</div>';
            echo '    <ul class="result-card__actions-list">';
            echo '      <li><span class="action-icon">🌊</span> Plan drainage systems for heavy rainfall periods</li>';
            echo '      <li><span class="action-icon">☀️</span> Prepare drought contingency plans if low rainfall is predicted</li>';
            echo '      <li><span class="action-icon">💧</span> Adjust irrigation schedules based on predicted rainfall</li>';
            echo '      <li><span class="action-icon">🌾</span> Choose crop varieties suited to the expected precipitation</li>';
            echo '      <li><span class="action-icon">📱</span> Subscribe to local weather alerts for real-time updates</li>';
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
<script>
    print_state("state");
    print_months("month");
</script>

<?php include 'includes/footer.php'; ?>
