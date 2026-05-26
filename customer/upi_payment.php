<?php
/**
 * upi_payment.php - UPI QR Code Payment Page
 * Displays a simulated UPI QR code with a timer and "I Have Paid" button.
 */
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

$order_ids = $_SESSION['upi_order_ids'] ?? [];
$upi_total = $_SESSION['upi_total'] ?? 0;

if (empty($order_ids)) {
    header('Location: ' . BASE_URL . 'customer/marketplace.php');
    exit();
}

// Calculate actual total from orders
$ids_str = implode(',', array_map('intval', $order_ids));
$total_res = mysqli_query($conn, "SELECT SUM(total_amount) t, GROUP_CONCAT(crop_id) crops FROM orders WHERE order_id IN ($ids_str)");
$total_row = mysqli_fetch_assoc($total_res);
$total_amount = (float)$total_row['t'];

// UPI details (prototype - not real)
$upi_id   = 'agriportal@upi';
$upi_name = 'AgriPortal Payments';
$upi_amount = number_format($total_amount, 2, '.', '');

// UPI deep link (opens apps if on mobile)
$upi_link = "upi://pay?pa={$upi_id}&pn=" . urlencode($upi_name) . "&am={$upi_amount}&cu=INR&tn=AgriPortal+Order";

// QR code via Google Charts API
$qr_data  = urlencode($upi_link);
$qr_url   = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={$qr_data}&margin=10&color=1B5E20&bgcolor=ffffff";

include '../includes/header.php';
?>
<style>
:root { --upi-purple: #6a1b9a; --upi-blue: #1565c0; }

.upi-page-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}
.upi-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.12);
    max-width: 480px;
    width: 100%;
    overflow: hidden;
}
.upi-card-header {
    background: linear-gradient(135deg, var(--upi-purple), var(--upi-blue));
    padding: 1.5rem 2rem;
    color: #fff;
    text-align: center;
}
.upi-card-header h2 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
}
.upi-card-header p { margin: 0.3rem 0 0; opacity: 0.85; font-size: 0.9rem; }
.upi-card-body { padding: 2rem; }

.amount-badge {
    text-align: center;
    background: linear-gradient(135deg, #e8f5e9, #e3f2fd);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.amount-badge .label { font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
.amount-badge .value { font-size: 2.2rem; font-weight: 800; color: var(--upi-purple); }

.qr-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 1.2rem;
}
.qr-box {
    border: 3px solid #e8e8e8;
    border-radius: 16px;
    padding: 16px;
    background: #fff;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.qr-box::before, .qr-box::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    border-color: var(--upi-purple);
    border-style: solid;
}
.qr-box::before { top: 6px; left: 6px; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
.qr-box::after  { bottom: 6px; right: 6px; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
.qr-box img { display: block; border-radius: 8px; }

.upi-apps { display: flex; justify-content: center; gap: 1.2rem; margin-bottom: 1.2rem; }
.upi-app-pill {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1px solid #ddd;
    cursor: pointer;
    transition: 0.2s;
}
.upi-app-pill:hover { background: #f5f5f5; border-color: #aaa; }
.upi-app-pill.gpay  { color: #4285F4; }
.upi-app-pill.phone { color: #5f259f; }
.upi-app-pill.paytm { color: #00BAF2; }

.upi-id-row {
    text-align: center;
    background: #f8f8f8;
    border-radius: 8px;
    padding: 0.7rem 1rem;
    margin-bottom: 1.2rem;
    font-size: 0.88rem;
    color: #555;
}
.upi-id-row strong { color: #222; }

.timer-box {
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 0.88rem;
    color: #888;
}
.timer-box span#countdown {
    font-size: 1.1rem;
    font-weight: 700;
    color: #e65100;
}

.btn-pay {
    width: 100%;
    padding: 1rem;
    font-size: 1.1rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--upi-purple), var(--upi-blue));
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: 0.2s;
    position: relative;
    overflow: hidden;
}
.btn-pay:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(106,27,154,0.3); }
.btn-pay:active { transform: scale(0.98); }

.cancel-link {
    display: block;
    text-align: center;
    margin-top: 1rem;
    color: #999;
    font-size: 0.85rem;
    text-decoration: none;
}
.cancel-link:hover { color: #E53935; }

.security-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 1.2rem;
    font-size: 0.78rem;
    color: #aaa;
}

@keyframes pulse-ring {
    0%   { transform: scale(0.8); opacity: 0.8; }
    100% { transform: scale(1.4); opacity: 0; }
}
.qr-pulse {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    width: 100%; height: 100%;
    border-radius: 16px;
    border: 2px solid var(--upi-purple);
    animation: pulse-ring 1.8s ease-out infinite;
    pointer-events: none;
}
</style>

<div class="upi-page-wrapper">
    <div class="upi-card">
        <div class="upi-card-header">
            <h2><i class="fas fa-qrcode"></i> UPI Payment</h2>
            <p>Scan the QR code with any UPI app to complete payment</p>
        </div>
        <div class="upi-card-body">

            <!-- Amount -->
            <div class="amount-badge">
                <div class="label">Amount to Pay</div>
                <div class="value">₹<?= number_format($total_amount, 2) ?></div>
            </div>

            <!-- QR Code -->
            <div class="qr-wrapper">
                <div class="qr-box" style="position:relative;">
                    <div class="qr-pulse"></div>
                    <img src="<?= htmlspecialchars($qr_url) ?>"
                         alt="UPI QR Code"
                         width="220" height="220"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22220%22 height=%22220%22%3E%3Crect width=%22220%22 height=%22220%22 fill=%22%23f5f5f5%22/%3E%3Ctext x=%22110%22 y=%22110%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2214%22%3EQR Code%3C/text%3E%3C/svg%3E'">
                </div>
            </div>

            <!-- UPI Apps -->
            <div class="upi-apps">
                <a href="<?= htmlspecialchars($upi_link) ?>" class="upi-app-pill gpay">
                    <i class="fab fa-google"></i> GPay
                </a>
                <a href="<?= htmlspecialchars($upi_link) ?>" class="upi-app-pill phone">
                    <i class="fas fa-mobile-alt"></i> PhonePe
                </a>
                <a href="<?= htmlspecialchars($upi_link) ?>" class="upi-app-pill paytm">
                    <i class="fas fa-wallet"></i> Paytm
                </a>
            </div>

            <!-- UPI ID -->
            <div class="upi-id-row">
                UPI ID: <strong><?= htmlspecialchars($upi_id) ?></strong>
                <button onclick="navigator.clipboard.writeText('<?= $upi_id ?>');this.textContent='Copied!';"
                        style="background:none;border:1px solid #ddd;border-radius:4px;padding:2px 8px;font-size:0.78rem;cursor:pointer;margin-left:6px;">
                    Copy
                </button>
            </div>

            <!-- Timer -->
            <div class="timer-box">
                <i class="fas fa-clock"></i> Session expires in <span id="countdown">10:00</span>
            </div>

            <!-- Confirm Payment Form -->
            <form method="POST" action="confirm_upi_payment.php" id="confirmForm">
                <button type="submit" class="btn-pay" id="confirmBtn">
                    <i class="fas fa-check-circle"></i>&nbsp; I Have Paid
                </button>
            </form>

            <a href="marketplace.php" class="cancel-link">
                <i class="fas fa-times-circle"></i> Cancel Payment
            </a>

            <div class="security-note">
                <i class="fas fa-shield-alt" style="color:#2e7d32;"></i>
                This is a secure, simulated UPI payment for the AgriPortal prototype.
            </div>
        </div>
    </div>
</div>

<script>
// Countdown timer - 10 minutes
let secs = 600;
const el = document.getElementById('countdown');
const timer = setInterval(() => {
    secs--;
    const m = String(Math.floor(secs / 60)).padStart(2, '0');
    const s = String(secs % 60).padStart(2, '0');
    el.textContent = m + ':' + s;
    if (secs <= 60) el.style.color = '#E53935';
    if (secs <= 0) {
        clearInterval(timer);
        el.textContent = 'Expired';
        document.getElementById('confirmBtn').disabled = true;
        document.getElementById('confirmBtn').style.opacity = '0.5';
    }
}, 1000);

// Prevent double submit
document.getElementById('confirmForm').addEventListener('submit', function() {
    const btn = document.getElementById('confirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>&nbsp; Confirming...';
});
</script>

<?php include '../includes/footer.php'; ?>
