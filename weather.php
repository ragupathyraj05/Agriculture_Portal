<?php
require_once 'includes/config.php';
include 'includes/header.php';

// All Tamil Nadu cities with their OpenWeatherMap city IDs
$tn_cities = [
    'Chennai'     => 'Chennai,IN',
    'Madurai'     => 'Madurai,IN',
    'Coimbatore'  => 'Coimbatore,IN',
    'Trichy'      => 'Tiruchirappalli,IN',
    'Salem'       => 'Salem,IN',
    'Tirunelveli' => 'Tirunelveli,IN',
    'Erode'       => 'Erode,IN',
    'Thanjavur'   => 'Thanjavur,IN',
    'Vellore'     => 'Vellore,IN',
    'Dindigul'    => 'Dindigul,IN',
    'Kanchipuram' => 'Kanchipuram,IN',
    'Cuddalore'   => 'Cuddalore,IN',
    'Nagapattinam'=> 'Nagapattinam,IN',
    'Villupuram'  => 'Villupuram,IN',
    'Thoothukudi' => 'Thoothukudi,IN',
    'Ramanathapuram' => 'Ramanathapuram,IN',
    'Sivakasi'    => 'Sivakasi,IN',
    'Karur'       => 'Karur,IN',
    'Namakkal'    => 'Namakkal,IN',
    'Pudukkottai' => 'Pudukkottai,IN',
    'Krishnagiri' => 'Krishnagiri,IN',
    'Dharmapuri'  => 'Dharmapuri,IN',
    'Tirupur'     => 'Tirupur,IN',
    'Ooty'        => 'Ooty,IN',
    'Hosur'       => 'Hosur,IN',
    'Kumbakonam'  => 'Kumbakonam,IN',
    'Ariyalur'    => 'Ariyalur,IN',
    'Perambalur'  => 'Perambalur,IN',
    'Virudhunagar'=> 'Virudhunagar,IN',
    'The Nilgiris'=> 'Gudalur,IN',
];

// Get selected city (GET for persistence via page reload)
$selectedCity     = isset($_GET['city']) && array_key_exists($_GET['city'], $tn_cities)
                    ? $_GET['city'] : 'Chennai';
$selectedCityParam = $tn_cities[$selectedCity];

$apiKey      = OPENWEATHER_API_KEY;
$apiUrl      = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($selectedCityParam) . "&lang=en&units=metric&cnt=40&APPID=" . $apiKey;

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data     = json_decode($response);
$forecast = ($data && isset($data->list)) ? $data->list : [];
$cityName = ($data && isset($data->city->name)) ? $data->city->name : $selectedCity;
?>

<style>
.weather-hero {
    background: linear-gradient(135deg, #0d47a1, #1565c0, #1976d2);
    border-radius: 16px;
    padding: 2rem 2rem 1.5rem;
    color: #fff;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.weather-hero h2 {
    margin: 0 0 0.3rem;
    font-size: 1.6rem;
    font-weight: 700;
}
.weather-hero p { margin: 0; opacity: 0.85; font-size: 0.92rem; }
.city-form { display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }
.city-select {
    padding: 0.55rem 1rem;
    border-radius: 8px;
    border: none;
    font-size: 0.95rem;
    min-width: 200px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    outline: none;
}
.city-select:focus { box-shadow: 0 0 0 3px rgba(255,255,255,0.4); }
.btn-fetch {
    padding: 0.55rem 1.2rem;
    border-radius: 8px;
    border: 2px solid #fff;
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: 0.2s;
    white-space: nowrap;
}
.btn-fetch:hover { background: #fff; color: #1565c0; }

.weather-table-wrap {
    background: #fff;
    border-radius: 14px;
    border: 1px solid var(--border-color, #e0e0e0);
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    overflow: hidden;
}
.weather-table-wrap table { width: 100%; border-collapse: collapse; }
.weather-table-wrap thead tr {
    background: linear-gradient(90deg, #e3f2fd, #e8f5e9);
}
.weather-table-wrap thead th {
    padding: 0.85rem 1rem;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffffff;
    font-weight: 700;
    border-bottom: 2px solid #e0e0e0;
    text-align: center;
}
.weather-table-wrap tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s;
}
.weather-table-wrap tbody tr:hover { background: #f8fbff; }
.weather-table-wrap tbody td {
    padding: 0.75rem 1rem;
    text-align: center;
    font-size: 0.9rem;
    color: #333;
}
.weather-table-wrap tbody tr:last-child { border-bottom: none; }

.date-cell  { font-weight: 600; color: #1565c0; }
.time-cell  { color: #555; font-size: 0.85rem; }
.temp-cell  { font-weight: 600; }
.temp-max   { color: #e53935; }
.temp-min   { color: #1565c0; }
.desc-cell  { text-transform: capitalize; color: #444; }
.hum-cell   { color: #0288d1; font-weight: 600; }
.wind-cell  { color: #388e3c; font-weight: 600; }
.weather-icon { width: 36px; height: 36px; vertical-align: middle; }

.no-data {
    text-align: center; padding: 3rem;
    color: #999; font-size: 1rem;
}
.no-data i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; color: #ccc; }

.date-sep {
    background: #f0f4ff;
    text-align: left !important;
    padding: 0.5rem 1rem !important;
    font-size: 0.82rem;
    font-weight: 700;
    color: #555;
    letter-spacing: 0.3px;
    border-top: 1px solid #e0e0e0;
}

@media (max-width: 600px) {
    .weather-hero { flex-direction: column; }
    .city-form { width: 100%; }
    .city-select { width: 100%; }
    .btn-fetch { width: 100%; text-align: center; }
}
</style>

<div class="container mb-2" style="max-width:1000px;">

    <!-- Hero Header + City Picker -->
    <div class="weather-hero">
        <div>
            <h2><i class="fas fa-cloud-sun-rain"></i> Weather Forecast</h2>
            <p>
                <?php if ($forecast): ?>
                    Showing 5-day forecast for <strong><?= htmlspecialchars($cityName) ?></strong>, Tamil Nadu
                <?php else: ?>
                    Select a city to view its weather forecast
                <?php endif; ?>
            </p>
        </div>
        <form method="GET" action="" class="city-form">
            <select name="city" class="city-select" id="citySelect">
                <?php foreach ($tn_cities as $label => $param): ?>
                    <option value="<?= htmlspecialchars($label) ?>"
                        <?= ($label === $selectedCity) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-fetch">
                <i class="fas fa-search"></i> Get Forecast
            </button>
        </form>
    </div>

    <!-- Forecast Table -->
    <div class="weather-table-wrap">
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-calendar-day"></i> Date</th>
                    <th><i class="fas fa-clock"></i> Time</th>
                    <th><i class="fas fa-thermometer-half"></i> Temp (Max / Min)</th>
                    <th><i class="fas fa-cloud"></i> Description</th>
                    <th><i class="fas fa-tint"></i> Humidity</th>
                    <th><i class="fas fa-wind"></i> Wind</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($forecast): ?>
                    <?php
                    $lastDate = '';
                    foreach ($forecast as $f):
                        $date = date('d M Y', strtotime($f->dt_txt));
                        $time = date('h:i A', strtotime($f->dt_txt));
                        $icon = $f->weather[0]->icon;
                    ?>

                    <?php if ($date !== $lastDate): $lastDate = $date; ?>
                    <tr>
                        <td colspan="6" class="date-sep">
                            <i class="fas fa-calendar-alt" style="color:#1565c0;margin-right:6px;"></i>
                            <?= $date ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <tr>
                        <td class="date-cell"><?= $date ?></td>
                        <td class="time-cell"><?= $time ?></td>
                        <td class="temp-cell">
                            <img src="https://openweathermap.org/img/wn/<?= htmlspecialchars($icon) ?>.png"
                                 class="weather-icon" alt="icon">
                            <span class="temp-max"><?= round($f->main->temp_max) ?>°C</span>
                            &nbsp;/&nbsp;
                            <span class="temp-min"><?= round($f->main->temp_min) ?>°C</span>
                        </td>
                        <td class="desc-cell"><?= htmlspecialchars(ucfirst($f->weather[0]->description)) ?></td>
                        <td class="hum-cell"><?= $f->main->humidity ?>%</td>
                        <td class="wind-cell"><?= round($f->wind->speed * 3.6, 1) ?> km/h</td>
                    </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <i class="fas fa-exclamation-triangle"></i>
                            Unable to fetch weather data for <strong><?= htmlspecialchars($selectedCity) ?></strong>.
                            Please check your API key or internet connection.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
