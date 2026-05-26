<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width:800px;margin:3rem auto;padding:0 1rem;">
    <div class="text-center" style="margin-bottom:2rem;">
        <h1 style="color:var(--text-main);"><i class="fas fa-envelope" style="color:var(--primary);"></i> Contact Us</h1>
        <p style="color:var(--text-muted);">Have questions? We'd love to hear from you.</p>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;">
            <i class="fas fa-check-circle"></i> Thank you for contacting us! We'll get back to you shortly.
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="alert alert-error" style="margin-bottom:1.5rem;">
            <i class="fas fa-exclamation-circle"></i> Error sending message. Please try again.
        </div>
    <?php endif; ?>

    <div class="card" style="padding:2rem;">
        <form action="contact-script.php" method="POST" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name <span style="color:red">*</span></label>
                    <input type="text" name="user_name" placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="tel" name="user_mobile" placeholder="10-digit number" maxlength="10"
                           pattern="^[6-9]{1}[0-9]{9}$" title="Enter valid 10-digit mobile number">
                </div>
            </div>

            <div class="form-group">
                <label>Email Address <span style="color:red">*</span></label>
                <input type="email" name="user_email" placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label>City / Pincode</label>
                <input type="text" name="user_address" placeholder="Your city and pincode">
            </div>

            <div class="form-group">
                <label>Message <span style="color:red">*</span></label>
                <textarea name="user_message" rows="5" placeholder="Describe your issue or question in detail..." required></textarea>
            </div>

            <button type="submit" name="submit" value="Submit" class="btn btn-primary" style="width:100%;padding:12px;margin-top:0.5rem;">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>

    <div class="text-center" style="margin-top:2rem;">
        <a href="<?php echo BASE_URL; ?>index.php" style="color:var(--text-muted);font-size:0.9rem;">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>