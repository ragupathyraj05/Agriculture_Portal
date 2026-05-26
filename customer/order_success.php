<?php
require_once '../includes/config.php';
require_once '../includes/customer_auth_check.php';

$method = $_GET['method'] ?? 'COD';
include '../includes/header.php';
?>
<style>
.cod-card {
    max-width: 500px; width: 100%; padding: 0; overflow: hidden;
    border-radius: 20px; box-shadow: 0 8px 40px rgba(0,0,0,0.12); text-align: center;
}
.cod-header {
    background: linear-gradient(135deg, #e65100, #ff8f00);
    padding: 2.5rem 2rem 1.5rem;
    color: #fff;
}
.cod-header .icon-circle {
    width: 80px; height: 80px; background: #fff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem; font-size: 2.4rem; color: #e65100;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    animation: pop-in 0.5s cubic-bezier(0.175,0.885,0.32,1.275) forwards;
}
@keyframes pop-in { 0% { transform:scale(0); opacity:0; } 80% { transform:scale(1.15); } 100% { transform:scale(1); opacity:1; } }
.cod-body { padding: 2rem; background: #fff; }
.cod-note {
    background: #fff8e1; border: 1px solid #ffcc80; border-radius: 10px;
    padding: 0.9rem 1rem; margin: 1rem 0 1.5rem;
    font-size: 0.88rem; color: #e65100; text-align: left;
}
.action-btns { display: flex; flex-direction: column; gap: 0.8rem; }
.btn-cod { padding: 0.9rem; border-radius: 10px; font-size: 1rem; font-weight: 600;
    text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
.btn-cod-primary { background: linear-gradient(135deg,#e65100,#ff8f00); color:#fff; }
.btn-cod-primary:hover { opacity:0.9; transform:translateY(-1px); }
.btn-cod-outline  { border:1px solid #ddd; color:#666; background:#fff; }
.btn-cod-outline:hover { background:#f5f5f5; }
</style>

<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
    <div class="cod-card">
        <div class="cod-header">
            <div class="icon-circle"><i class="fas fa-truck"></i></div>
            <h1 style="margin:0 0 0.3rem;font-size:1.5rem;">Order Placed!</h1>
            <p style="margin:0;opacity:0.88;font-size:0.92rem;">Your order has been received and is being processed.</p>
        </div>
        <div class="cod-body">
            <div class="cod-note">
                <i class="fas fa-info-circle"></i> <strong>Cash on Delivery:</strong>
                Please keep the exact amount ready at the time of delivery.
            </div>
            <div style="text-align:left;font-size:0.88rem;margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f0f0f0;">
                    <span style="color:#888;"><i class="fas fa-credit-card"></i> Payment</span>
                    <span style="font-weight:600;color:#e65100;">Cash on Delivery</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f0f0f0;">
                    <span style="color:#888;"><i class="fas fa-box"></i> Status</span>
                    <span style="font-weight:600;color:#1565c0;">Placed</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                    <span style="color:#888;"><i class="fas fa-calendar-alt"></i> Date</span>
                    <span style="font-weight:600;"><?= date('d M Y, h:i A') ?></span>
                </div>
            </div>
            <div class="action-btns">
                <a href="my_orders.php" class="btn-cod btn-cod-primary">
                    <i class="fas fa-box-open"></i> View My Orders
                </a>
                <a href="marketplace.php" class="btn-cod btn-cod-outline">
                    <i class="fas fa-store"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
