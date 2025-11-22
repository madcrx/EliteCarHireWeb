<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Calendar & Date Blocking</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Block out dates when your vehicles are unavailable for hire (maintenance, personal use, etc.)
        </p>

        <div class="card">
            <h2><i class="fas fa-calendar-plus"></i> Block Dates</h2>
            <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                Select a vehicle and date range to block it from being booked.
            </p>

            <?php if (empty($vehicles)): ?>
                <div style="background: var(--light-gray); padding: 1.5rem; border-radius: var(--border-radius); text-align: center;">
                    <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                    <p>You don't have any approved vehicles yet. Once your vehicles are approved, you can block dates here.</p>
                    <a href="/owner/listings" class="btn btn-primary" style="margin-top: 1rem;">View My Listings</a>
                </div>
            <?php else: ?>
                <form method="POST" action="/owner/calendar/block">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="form-group">
                        <label for="vehicle_id">Select Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" required>
                            <option value="">-- Choose a vehicle --</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= $vehicle['id'] ?>">
                                    <?= e($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')') ?>
                                    <?php if (!empty($vehicle['registration_number'])): ?>
                                        - <?= e($vehicle['registration_number']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason (Optional)</label>
                        <input type="text" name="reason" id="reason" placeholder="e.g., Maintenance, Personal use, etc." maxlength="255">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-ban"></i> Block Dates
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($blockedDates)): ?>
            <div class="card">
                <h2><i class="fas fa-calendar-times"></i> Blocked Dates</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Your vehicles will not be available for booking during these periods.
                </p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blockedDates as $block): ?>
                                <?php
                                    $startDate = new DateTime($block['start_date']);
                                    $endDate = new DateTime($block['end_date']);
                                    $interval = $startDate->diff($endDate);
                                    $days = $interval->days + 1;

                                    // Check if block is in the past
                                    $isPast = strtotime($block['end_date']) < strtotime('today');
                                ?>
                                <tr style="<?= $isPast ? 'opacity: 0.6;' : '' ?>">
                                    <td>
                                        <strong><?= e($block['make'] . ' ' . $block['model']) ?></strong><br>
                                        <small style="color: var(--dark-gray);"><?= e($block['registration_number']) ?></small>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($block['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($block['end_date'])) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $days ?> <?= $days === 1 ? 'day' : 'days' ?></span>
                                    </td>
                                    <td><?= !empty($block['reason']) ? e($block['reason']) : '<em style="color: var(--medium-gray);">No reason provided</em>' ?></td>
                                    <td><?= date('M d, Y', strtotime($block['created_at'])) ?></td>
                                    <td>
                                        <?php if (!$isPast): ?>
                                            <form method="POST" action="/owner/calendar/unblock" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="block_id" value="<?= $block['id'] ?>">
                                                <button type="submit" class="btn btn-warning" style="padding: 5px 15px;"
                                                        onclick="return confirm('Are you sure you want to unblock these dates?')">
                                                    <i class="fas fa-unlock"></i> Unblock
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-calendar-check" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Blocked Dates</h3>
                <p>You haven't blocked any dates yet. Your vehicles are available for booking on all dates.</p>
            </div>
        <?php endif; ?>

        <div class="card" style="background: var(--light-gray);">
            <h3><i class="fas fa-info-circle"></i> Tips for Managing Availability</h3>
            <ul>
                <li><strong>Plan Ahead:</strong> Block dates as early as possible to avoid booking conflicts</li>
                <li><strong>Maintenance Windows:</strong> Schedule regular maintenance during off-peak periods</li>
                <li><strong>Check Bookings:</strong> Review existing bookings before blocking dates to avoid conflicts</li>
                <li><strong>Update Promptly:</strong> If plans change, unblock dates immediately to maximize earnings</li>
                <li><strong>Past Blocks:</strong> Expired blocks are shown in gray and cannot be unblocked</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Auto-update end date minimum when start date changes
document.getElementById('start_date')?.addEventListener('change', function() {
    const endDateInput = document.getElementById('end_date');
    if (endDateInput) {
        endDateInput.min = this.value;
        // If end date is before start date, update it
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
