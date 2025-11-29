<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-percentage"></i> Commission Rates</h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/commission/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>Platform Commission Rate</h2>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1.5rem;">
                    Set the commission rate charged to vehicle owners for bookings
                </small>

                <div class="form-group">
                    <label for="commission_rate">Commission Rate (%) *</label>
                    <input type="number" name="commission_rate" id="commission_rate"
                           value="<?= e($commissionRate ?? '15') ?>" class="form-control"
                           step="0.1" min="0" max="100" required>
                    <small>Applied uniformly to all vehicles and bookings</small>
                </div>

                <div class="form-group">
                    <label for="commission_payment_cycle">Payment Cycle *</label>
                    <select name="commission_payment_cycle" id="commission_payment_cycle" class="form-control" required>
                        <option value="weekly" <?= ($commissionPaymentCycle ?? 'monthly') === 'weekly' ? 'selected' : '' ?>>Weekly (Every Monday)</option>
                        <option value="biweekly" <?= ($commissionPaymentCycle ?? 'monthly') === 'biweekly' ? 'selected' : '' ?>>Bi-weekly (Every 2 weeks)</option>
                        <option value="monthly" <?= ($commissionPaymentCycle ?? 'monthly') === 'monthly' ? 'selected' : '' ?>>Monthly (1st of each month)</option>
                        <option value="on_completion" <?= ($commissionPaymentCycle ?? 'monthly') === 'on_completion' ? 'selected' : '' ?>>On Booking Completion</option>
                    </select>
                    <small>When owners receive their payout (after platform commission is deducted)</small>
                </div>

                <hr>

                <div style="margin-top: 2rem; padding: 1rem; background: #f7fafc; border-left: 4px solid var(--primary-gold);">
                    <strong>Commission Calculation Example:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Booking Amount: $1,000</li>
                        <li>Commission Rate: 15%</li>
                        <li>Platform Commission: $150</li>
                        <li>Owner Payout: $850</li>
                    </ul>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #e7f3ff; border-left: 4px solid #0066cc;">
                    <strong>Uniform Commission Policy:</strong>
                    <p style="margin: 0.5rem 0 0 0;">
                        All vehicles are charged the same commission rate regardless of vehicle type or category.
                        This ensures fair and transparent pricing for all owners on the platform.
                    </p>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbf0; border-left: 4px solid #d69e2e;">
                    <strong>Best Practices:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Industry standard for luxury vehicle platforms: 10-20%</li>
                        <li>15% balances platform revenue with competitive owner payouts</li>
                        <li>Consider competitive rates in your market</li>
                        <li>Clearly communicate rates to owners during onboarding</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Commission Rate
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
