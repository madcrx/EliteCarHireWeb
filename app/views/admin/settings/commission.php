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
                <h2>Platform Commission Rates</h2>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1.5rem;">
                    Set commission rates charged to vehicle owners for bookings. Higher-tier vehicles typically have lower commission rates.
                </small>

                <div class="form-group">
                    <label for="default_commission_rate">Default Commission Rate (%) *</label>
                    <input type="number" name="default_commission_rate" id="default_commission_rate"
                           value="<?= e($defaultCommissionRate ?? '15') ?>" class="form-control"
                           step="0.1" min="0" max="100" required>
                    <small>Applied when no specific vehicle category rate is set</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Category-Specific Rates</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    Override the default rate for specific vehicle categories
                </small>

                <div class="form-group">
                    <label for="premium_commission_rate">Premium Vehicles (%) *</label>
                    <input type="number" name="premium_commission_rate" id="premium_commission_rate"
                           value="<?= e($premiumCommissionRate ?? '12') ?>" class="form-control"
                           step="0.1" min="0" max="100" required>
                    <small>Luxury sedans, high-end SUVs (usually lower commission to attract quality inventory)</small>
                </div>

                <div class="form-group">
                    <label for="standard_commission_rate">Standard Vehicles (%) *</label>
                    <input type="number" name="standard_commission_rate" id="standard_commission_rate"
                           value="<?= e($standardCommissionRate ?? '15') ?>" class="form-control"
                           step="0.1" min="0" max="100" required>
                    <small>Mid-range vehicles, standard sedans and SUVs</small>
                </div>

                <div class="form-group">
                    <label for="economy_commission_rate">Economy Vehicles (%) *</label>
                    <input type="number" name="economy_commission_rate" id="economy_commission_rate"
                           value="<?= e($economyCommissionRate ?? '18') ?>" class="form-control"
                           step="0.1" min="0" max="100" required>
                    <small>Budget vehicles, compact cars (higher commission for lower-value bookings)</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Commission Payment Terms</h3>

                <div class="form-group">
                    <label for="min_commission_amount">Minimum Commission Amount ($AUD) *</label>
                    <input type="number" name="min_commission_amount" id="min_commission_amount"
                           value="<?= e($minCommissionAmount ?? '50') ?>" class="form-control"
                           step="0.01" min="0" required>
                    <small>Minimum commission charged per booking (prevents very low commission on short bookings)</small>
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
                    <strong>Example Commission Calculation:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Booking Amount: $1,000</li>
                        <li>Premium Vehicle Rate: 12%</li>
                        <li>Platform Commission: $120</li>
                        <li>Owner Payout: $880</li>
                    </ul>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbf0; border-left: 4px solid #d69e2e;">
                    <strong>Best Practices:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Keep premium rates lower to attract high-value inventory</li>
                        <li>Industry standard: 10-20% for luxury vehicle platforms</li>
                        <li>Consider competitive rates in your market</li>
                        <li>Clearly communicate rates to owners during onboarding</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Commission Rates
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
