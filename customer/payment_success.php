<?php
/**
 * payment_success.php - UPI Payment Success Page
 * Shown after "I Have Paid" is confirmed.
 */
require_once '../includes/config.php';
require_once '../includes/customer_auth_check.php';

$txn    = htmlspecialchars($_GET['txn'] ?? 'N/A');
$ids    = htmlspecialchars($_GET['ids'] ?? '');

include '../includes/header.php';
?>
<style>
.success-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}
.success-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.12);
    max-width: 500px;
    width: 100%;
    overflow: hidden;
    text-align: center;
}
.success-header {
    background: linear-gradient(135deg, #1B5E20, #2e7d32);
    padding: 2.5rem 2rem 1.5rem;
    position: relative;
}
.checkmark-circle {
    width: 80px; height: 80px;
    background: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2.5rem;
    color: #2e7d32;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    animation: pop-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
@keyframes pop-in {
    0%   { transform: scale(0); opacity: 0; }
    80%  { transform: scale(1.15); }
    100% { transform: scale(1); opacity: 1; }
}
.success-header h1 { color: #fff; font-size: 1.5rem; margin: 0 0 0.3rem; }
.success-header p  { color: rgba(255,255,255,0.85); margin: 0; font-size: 0.92rem; }

.confetti-bar {
    height: 6px;
    background: linear-gradient(90deg, #f9a825, #43a047, #1e88e5, #e53935, #8e24aa);
    background-size: 200% 100%;
    animation: shimmer 2s linear infinite;
}
@keyframes shimmer { 0%{background-position:0% 0%} 100%{background-position:200% 0%} }

.success-body { padding: 2rem 2rem 1.5rem; }

.txn-box {
    background: #f0faf0;
    border: 1px dashed #a5d6a7;
    border-radius: 10px;
    padding: 1rem;
    margin: 1.2rem 0;
    font-size: 0.85rem;
    color: #555;
}
.txn-box strong { color: #2e7d32; font-family: monospace; font-size: 0.95rem; }

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.88rem;
}
.info-row:last-child { border-bottom: none; }
.info-row .label { color: #888; }
.info-row .val   { font-weight: 600; color: #333; }

.action-btns {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-top: 1.5rem;
}
.btn-success {
    padding: 0.9rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-primary-custom {
    background: linear-gradient(135deg, #1B5E20, #2e7d32);
    color: #fff;
}
.btn-primary-custom:hover { opacity: 0.9; transform: translateY(-1px); }
.btn-outline-custom {
    border: 1px solid #ddd;
    color: #666;
    background: #fff;
}
.btn-outline-custom:hover { background: #f5f5f5; }
</style>

<div class="success-wrapper">
    <div class="success-card">

        <div class="success-header">
            <div class="checkmark-circle">
                <i class="fas fa-check"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Your order has been confirmed. Thank you for supporting our farmers!</p>
        </div>
        <div class="confetti-bar"></div>

        <div class="success-body">

            <div class="txn-box">
                <div style="margin-bottom:4px;color:#777;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">Transaction Reference</div>
                <strong><?= $txn ?></strong>
            </div>

            <div style="text-align:left;">
                <div class="info-row">
                    <span class="label"><i class="fas fa-credit-card"></i> Payment Method</span>
                    <span class="val" style="color:#6a1b9a;"><i class="fas fa-qrcode"></i> UPI</span>
                </div>
                <div class="info-row">
                    <span class="label"><i class="fas fa-box"></i> Order Status</span>
                    <span class="val" style="color:#2e7d32;">Confirmed ✓</span>
                </div>
                <div class="info-row">
                    <span class="label"><i class="fas fa-calendar-alt"></i> Date &amp; Time</span>
                    <span class="val"><?= date('d M Y, h:i A') ?></span>
                </div>
            </div>

            <div class="action-btns">
                <a href="my_orders.php" class="btn-success btn-primary-custom">
                    <i class="fas fa-box-open"></i> View My Orders
                </a>
                <a href="marketplace.php" class="btn-success btn-outline-custom">
                    <i class="fas fa-store"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
