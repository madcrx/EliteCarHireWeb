<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - Elite Car Hire</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .cancel-form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cancel-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .cancel-header h1 {
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .policy-box {
            background: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 20px;
            margin: 20px 0;
        }
        .policy-box h3 {
            margin-top: 0;
            color: #f39c12;
        }
        .booking-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .booking-info h3 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #C5A253;
        }
        .required {
            color: #e74c3c;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cancel {
            background: #e74c3c;
            color: white;
        }
        .btn-cancel:hover {
            background: #c0392b;
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .warning-box {
            background: #ffebee;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box p {
            margin: 0;
            color: #c0392b;
        }
        .info-list {
            padding-left: 20px;
            margin: 10px 0;
        }
        .info-list li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="cancel-form-container">
        <div class="cancel-header">
            <h1>🚫 Cancel Booking</h1>
            <p>We're sorry to see you need to cancel your booking. Please review our cancellation policy below.</p>
        </div>

        <?php if (!empty($booking)): ?>
            <div class="booking-info">
                <h3>Booking Details</h3>
                <p><strong>Booking Reference:</strong> <?= e($booking['booking_reference']) ?></p>
                <p><strong>Vehicle:</strong> <?= e($booking['year']) ?> <?= e($booking['make']) ?> <?= e($booking['model']) ?></p>
                <p><strong>Date:</strong> <?= e($booking['booking_date']) ?></p>
                <p><strong>Time:</strong> <?= e($booking['start_time']) ?> - <?= e($booking['end_time']) ?></p>
                <p><strong>Duration:</strong> <?= e($booking['duration_hours']) ?> hours</p>
                <p><strong>Total Amount:</strong> $<?= number_format($booking['total_amount'], 2) ?> AUD</p>
                <p><strong>Payment Status:</strong> <span style="text-transform: uppercase;"><?= e($booking['payment_status']) ?></span></p>
            </div>

            <div class="policy-box">
                <h3>📋 Cancellation Policy</h3>
                <p><strong>A 50% cancellation fee applies to all booking cancellations, regardless of when the cancellation is made.</strong></p>

                <?php if ($booking['payment_status'] === 'paid'): ?>
                    <?php
                        $cancellationFee = $booking['total_amount'] * 0.5;
                        $refundAmount = $booking['total_amount'] * 0.5;
                    ?>
                    <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 4px;">
                        <p><strong>If you proceed with this cancellation:</strong></p>
                        <ul class="info-list">
                            <li>Original Booking Amount: <strong>$<?= number_format($booking['total_amount'], 2) ?> AUD</strong></li>
                            <li>Cancellation Fee (50%): <strong>$<?= number_format($cancellationFee, 2) ?> AUD</strong></li>
                            <li>Refund Amount (50%): <strong style="color: #4caf50;">$<?= number_format($refundAmount, 2) ?> AUD</strong></li>
                        </ul>
                        <p style="margin: 10px 0 0 0; font-size: 14px; font-style: italic;">The refund will be processed to your original payment method within 5-7 business days.</p>
                    </div>
                <?php else: ?>
                    <p style="margin-top: 10px;"><em>Since this booking has not been paid, no refund will be applicable.</em></p>
                <?php endif; ?>
            </div>

            <div class="warning-box">
                <p><strong>⚠️ Important:</strong> Once submitted, your cancellation request will be reviewed by our admin team. This action cannot be undone. Please ensure you want to proceed before submitting.</p>
            </div>

            <form method="POST" action="/customer/bookings/<?= $booking['id'] ?>/cancel" id="cancelForm">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                <div class="form-group">
                    <label for="reason">Reason for Cancellation <span class="required">*</span></label>
                    <select name="reason" id="reason" required>
                        <option value="">-- Select a reason --</option>
                        <option value="Change of plans">Change of plans</option>
                        <option value="Found alternative transportation">Found alternative transportation</option>
                        <option value="Vehicle no longer needed">Vehicle no longer needed</option>
                        <option value="Event cancelled or postponed">Event cancelled or postponed</option>
                        <option value="Financial reasons">Financial reasons</option>
                        <option value="Booking made by mistake">Booking made by mistake</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="additional_details">Additional Details <span class="required">*</span></label>
                    <textarea name="additional_details" id="additional_details" placeholder="Please provide any additional information about your cancellation..." required minlength="10"></textarea>
                    <small style="color: #666; font-size: 13px;">Minimum 10 characters required</small>
                </div>

                <div class="form-group">
                    <label for="contact_phone">Contact Phone Number</label>
                    <input type="tel" name="contact_phone" id="contact_phone" placeholder="0400 000 000" value="<?= e($user['phone'] ?? '') ?>">
                    <small style="color: #666; font-size: 13px;">Optional - In case we need to contact you about this cancellation</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="acknowledge_policy" id="acknowledge_policy" required>
                        I acknowledge that I have read and understand the cancellation policy, and I agree to the 50% cancellation fee. <span class="required">*</span>
                    </label>
                </div>

                <div class="btn-group">
                    <a href="/customer/bookings" class="btn btn-back">← Back to My Bookings</a>
                    <button type="submit" class="btn btn-cancel" id="submitBtn">Submit Cancellation Request</button>
                </div>
            </form>

        <?php else: ?>
            <div class="warning-box">
                <p><strong>Booking not found or you don't have permission to cancel this booking.</strong></p>
            </div>
            <div class="btn-group">
                <a href="/customer/bookings" class="btn btn-back">← Back to My Bookings</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        document.getElementById('cancelForm')?.addEventListener('submit', function(e) {
            const acknowledged = document.getElementById('acknowledge_policy').checked;

            if (!acknowledged) {
                e.preventDefault();
                alert('You must acknowledge the cancellation policy before proceeding.');
                return false;
            }

            const confirmed = confirm('Are you sure you want to cancel this booking? This action will submit your cancellation request for admin review and cannot be undone.');

            if (!confirmed) {
                e.preventDefault();
                return false;
            }

            // Disable submit button to prevent double submission
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Submitting...';
        });
    </script>
</body>
</html>
