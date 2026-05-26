<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/stats.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="hero-title">Empowering Agriculture<br>with Technology</h1>
        <p class="hero-subtitle">
            Connect with farmers, buy fresh crops, access AI-powered predictions, and get real-time weather insights — all in one platform.
        </p>
        <div class="hero-actions">
            <a href="<?php echo BASE_URL; ?>auth/role_select.php" class="btn btn-primary" style="padding:12px 30px;font-size:1rem;">
                <i class="fas fa-rocket"></i> Get Started
            </a>
            <a href="#features" class="btn btn-outline" style="padding:12px 30px;font-size:1rem;border-color:rgba(255,255,255,0.6);color:#fff;">
                <i class="fas fa-info-circle"></i> Learn More
            </a>
        </div>
    </div>
</section>
<!-- Rotating Quote Banner -->
        <div id="quoteBanner" style="text-align:center;margin-bottom:2rem;padding:1rem 1.5rem;background:rgba(27,94,32,0.06);border-radius:12px;border-left:4px solid #1B5E20;">
            <p id="quoteText" style="font-style:italic;color:#333;font-size:1rem;margin:0;transition:opacity 0.5s ease;">
                "Agriculture is the most healthful, most useful, and most noble employment of man."
            </p>
            <span id="quoteAuthor" style="font-weight:600;color:#1B5E20;font-size:0.85rem;margin-top:6px;display:block;">— George Washington</span>
        </div>

<!-- Feature Highlights -->
<section class="features-section" id="features">
    <div class="container">
        <h2>Smart Farming Tools</h2>

        <div class="quick-grid">
            <a href="<?php echo BASE_URL; ?>crop_prediction.php" class="quick-card">
                <i class="fas fa-seedling"></i>
                <span>Crop Prediction</span>
            </a>
            <a href="<?php echo BASE_URL; ?>crop_recommendation.php" class="quick-card">
                <i class="fas fa-lightbulb"></i>
                <span>Crop Recommendation</span>
            </a>

            <a href="<?php echo BASE_URL; ?>yield_prediction.php" class="quick-card">
                <i class="fas fa-chart-bar"></i>
                <span>Yield Prediction</span>
            </a>
            <a href="<?php echo BASE_URL; ?>rainfall_prediction.php" class="quick-card">
                <i class="fas fa-cloud-rain"></i>
                <span>Rainfall Prediction</span>
            </a>
            <a href="<?php echo BASE_URL; ?>fertilizer_recommendation.php" class="quick-card">
                <i class="fas fa-flask"></i>
                <span>Fertilizer Suggestion</span>
            </a>

        </div>
    </div>
</section>

<!-- Platform Statistics -->
<section class="stats-section">
    <div class="container">
        <div class="text-center mb-2">
            <h2 style="font-size:2.2rem;font-weight:800;color:var(--primary);">Platform Statistics</h2>
            <p class="text-muted">Real-time growth and impact of AgriPortal across the farming community.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">👨🌾</span>
                <span class="stat-number counter" data-target="<?= $farmer_count ?>">0</span>
                <span class="stat-label">Farmers Joined</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🛍</span>
                <span class="stat-number counter" data-target="<?= $completed_orders ?>">0</span>
                <span class="stat-label">Orders Delivered</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🌾</span>
                <span class="stat-number counter" data-target="<?= $crop_types ?>">0</span>
                <span class="stat-label">Crop Varieties</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🤖</span>
                <span class="stat-number counter" data-target="<?= $prediction_count ?>">0</span>
                <span class="stat-label">AI Predictions</span>
            </div>
        </div>
    </div>
</section>



<script>
const farmQuotes = [
    { text: "Agriculture is the most healthful, most useful, and most noble employment of man.", author: "George Washington" },
    { text: "The farmer is the only man in our economy who buys everything at retail, sells everything at wholesale.", author: "John F. Kennedy" },
    { text: "To forget how to dig the earth and to tend the soil is to forget ourselves.", author: "Mahatma Gandhi" },
    { text: "Agriculture is our wisest pursuit, because it will in the end contribute most to real wealth.", author: "Thomas Jefferson" },
    { text: "Farming looks mighty easy when your plow is a pencil and you're a thousand miles from the corn field.", author: "Dwight D. Eisenhower" },
    { text: "The ultimate goal of farming is not the growing of crops, but the cultivation of human beings.", author: "Masanobu Fukuoka" },
    { text: "Agriculture not only gives riches to a nation, but the only riches she can call her own.", author: "Samuel Johnson" },
    { text: "If you tickle the earth with a hoe she laughs with a harvest.", author: "Douglas Jerrold" }
];
let quoteIndex = 0;
setInterval(() => {
    quoteIndex = (quoteIndex + 1) % farmQuotes.length;
    const qt = document.getElementById('quoteText');
    const qa = document.getElementById('quoteAuthor');
    qt.style.opacity = '0';
    qa.style.opacity = '0';
    setTimeout(() => {
        qt.textContent = '"' + farmQuotes[quoteIndex].text + '"';
        qa.textContent = '— ' + farmQuotes[quoteIndex].author;
        qt.style.opacity = '1';
        qa.style.opacity = '1';
    }, 500);
}, 4000);
</script>


<!-- Why Choose AgriPortal -->
<section class="why-choose-section">
    <div class="container">
        <div class="text-center mb-2">
            <h2 style="font-size:2.2rem;font-weight:800;color:var(--primary);">Why Choose AgriPortal?</h2>
            <p class="text-muted">Empowering the next generation of agriculture with smart, digital solutions.</p>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <i class="fas fa-brain"></i>
                <h3>AI Powered</h3>
                <p>Advanced machine learning for accurate crop and weather predictions.</p>
            </div>
            <div class="why-card">
                <i class="fas fa-handshake"></i>
                <h3>Direct Trade</h3>
                <p>Eliminate middlemen and connect farmers directly with customers.</p>
            </div>
            <div class="why-card">
                <i class="fas fa-chart-line"></i>
                <h3>Real-time Insights</h3>
                <p>Get live market prices, news, and weather updates instantly.</p>
            </div>
            <div class="why-card">
                <i class="fas fa-wallet"></i>
                <h3>Cost Efficient</h3>
                <p>Maximize yields and minimize waste with data-driven recommendations.</p>
            </div>
        </div>
    </div>
</section>
<!-- AI Chat Section -->
<section class="chat-section">
    <div class="container">
        <div class="chat-container">
            <div style="margin-bottom:1.5rem;">
                <i class="fas fa-robot" style="font-size:2.5rem;color:var(--primary);margin-bottom:0.5rem;display:block;"></i>
                <h2 style="font-size:1.5rem;font-weight:700;color:var(--text-main);margin-bottom:0.5rem;">AI Agriculture Assistant</h2>
                <p style="color:var(--text-muted);font-size:0.95rem;">
                    Ask anything about farming, crops, weather, and agricultural best practices.
                </p>
            </div>
            <a href="<?php echo BASE_URL; ?>chatbot.php" class="btn btn-primary" style="padding:12px 30px;">
                <i class="fas fa-comments"></i> Start Chatting
            </a>
        </div>
    </div>
</section>

<script>
// Animated Counter Effect
const countersList = document.querySelectorAll('.counter');
const counterSpeed = 200; 

const startCounters = () => {
    countersList.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / counterSpeed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 15);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
};

// Intersection Observer to trigger when visible
const statsSection = document.querySelector('.stats-section');
const observerOptions = {
    threshold: 0.2
};

const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            startCounters();
            statsObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

if (statsSection) {
    statsObserver.observe(statsSection);
}
</script>



<?php include 'includes/footer.php'; ?>