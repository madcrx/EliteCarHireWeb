<?php ob_start(); ?>
<style>
.calendar-container {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: var(--primary-gold);
    color: white;
}

.calendar-nav {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.calendar-nav button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
}

.calendar-nav button:hover {
    background: rgba(255,255,255,0.3);
}

.calendar-month {
    font-size: 1.5rem;
    font-weight: bold;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--medium-gray);
}

.calendar-day-header {
    background: var(--light-gray);
    padding: 1rem;
    text-align: center;
    font-weight: bold;
    color: var(--dark-gray);
}

.calendar-day {
    background: white;
    min-height: 100px;
    padding: 0.5rem;
    position: relative;
    cursor: pointer;
    transition: background 0.2s;
}

.calendar-day:hover {
    background: var(--light-gray);
}

.calendar-day.other-month {
    opacity: 0.3;
}

.calendar-day.today {
    background: #fffbf0;
}

.calendar-day-number {
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.calendar-day-blocks {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.calendar-block {
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.75rem;
    color: white;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.vehicle-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 3px;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius);
    max-width: 500px;
    width: 90%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--dark-gray);
}
</style>

<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-calendar-alt"></i> Calendar & Date Blocking</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Visual calendar showing vehicle availability. Click on any date to block or unblock it for your vehicles.
        </p>

        <?php if (empty($vehicles)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <p>You don't have any approved vehicles yet. Once your vehicles are approved, you can manage availability here.</p>
                <a href="/owner/listings" class="btn btn-primary" style="margin-top: 1rem;">View My Listings</a>
            </div>
        <?php else: ?>
            <!-- Vehicle Legend -->
            <div class="card" style="padding: 0;">
                <div class="vehicle-legend">
                    <strong style="width: 100%; margin-bottom: 0.5rem;"><i class="fas fa-car"></i> Your Vehicles:</strong>
                    <?php
                        $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'];
                        foreach ($vehicles as $index => $vehicle):
                            $vehicleColor = $colors[$index % count($colors)];
                    ?>
                        <div class="legend-item">
                            <div class="legend-color" style="background: <?= $vehicleColor ?>"></div>
                            <span><?= e($vehicle['make'] . ' ' . $vehicle['model']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Visual Calendar -->
            <div class="card" style="padding: 0;">
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="calendar-nav">
                            <button onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i> Prev</button>
                            <h2 class="calendar-month" id="calendarMonth"></h2>
                            <button onclick="changeMonth(1)">Next <i class="fas fa-chevron-right"></i></button>
                        </div>
                        <button class="btn" style="background: white; color: var(--primary-gold);" onclick="goToToday()">
                            <i class="fas fa-calendar-day"></i> Today
                        </button>
                    </div>

                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Calendar will be generated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Quick Block Form (Traditional Form as Backup) -->
            <div class="card">
                <h2><i class="fas fa-calendar-plus"></i> Quick Block Dates</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Select a vehicle and date range to block availability.
                </p>

                <form method="POST" action="/owner/calendar/block" id="blockForm">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="vehicle_id">Vehicle</label>
                            <select name="vehicle_id" id="vehicle_id" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?= $vehicle['id'] ?>">
                                        <?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason (Optional)</label>
                        <input type="text" name="reason" id="reason" placeholder="e.g., Maintenance, Personal use">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-ban"></i> Block Dates
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Blocked Dates List -->
        <?php if (!empty($blockedDates)): ?>
            <div class="card">
                <h2><i class="fas fa-calendar-times"></i> Blocked Dates</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Reason</th>
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
                                    $isPast = strtotime($block['end_date']) < strtotime('today');
                                ?>
                                <tr style="<?= $isPast ? 'opacity: 0.6;' : '' ?>">
                                    <td><strong><?= e($block['make'] . ' ' . $block['model']) ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($block['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($block['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $days ?> days</span></td>
                                    <td><?= !empty($block['reason']) ? e($block['reason']) : '<em style="color: var(--medium-gray);">No reason</em>' ?></td>
                                    <td>
                                        <?php if (!$isPast): ?>
                                            <form method="POST" action="/owner/calendar/unblock" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="block_id" value="<?= $block['id'] ?>">
                                                <button type="submit" class="btn btn-warning" style="padding: 5px 15px;"
                                                        onclick="return confirm('Unblock these dates?')">
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
        <?php endif; ?>
    </div>
</div>

<!-- Modal for Date Click -->
<div class="modal" id="dateModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-calendar"></i> Block Date</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="/owner/calendar/block">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="start_date" id="modalStartDate">
            <input type="hidden" name="end_date" id="modalEndDate">

            <div class="form-group">
                <label for="modalVehicle">Select Vehicle</label>
                <select name="vehicle_id" id="modalVehicle" required>
                    <option value="">-- Select Vehicle --</option>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <option value="<?= $vehicle['id'] ?>">
                            <?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modalReason">Reason (Optional)</label>
                <input type="text" name="reason" id="modalReason" placeholder="e.g., Maintenance">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-ban"></i> Block Date
                </button>
                <button type="button" class="btn" style="background: var(--medium-gray);" onclick="closeModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Data from PHP
const vehicles = <?= json_encode($vehicles) ?>;
const blockedDates = <?= json_encode($blockedDates) ?>;
const vehicleColors = <?= json_encode($colors) ?>;

let currentDate = new Date();

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Update month display
    document.getElementById('calendarMonth').textContent =
        new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    // Create calendar grid
    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    // Day headers
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    days.forEach(day => {
        const header = document.createElement('div');
        header.className = 'calendar-day-header';
        header.textContent = day;
        grid.appendChild(header);
    });

    // Get first day of month and total days
    const firstDay = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();
    const prevLastDate = new Date(year, month, 0).getDate();

    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];

    // Previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
        const dayDiv = createDayCell(prevLastDate - i, year, month - 1, true);
        grid.appendChild(dayDiv);
    }

    // Current month days
    for (let day = 1; day <= lastDate; day++) {
        const dayDiv = createDayCell(day, year, month, false);
        grid.appendChild(dayDiv);
    }

    // Next month days
    const totalCells = grid.children.length - 7; // Subtract headers
    const remainingCells = (Math.ceil(totalCells / 7) * 7) - totalCells;
    for (let day = 1; day <= remainingCells; day++) {
        const dayDiv = createDayCell(day, year, month + 1, true);
        grid.appendChild(dayDiv);
    }
}

function createDayCell(day, year, month, isOtherMonth) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day';
    if (isOtherMonth) dayDiv.classList.add('other-month');

    const date = new Date(year, month, day);
    const dateStr = date.toISOString().split('T')[0];

    // Check if today
    const today = new Date();
    if (date.toDateString() === today.toDateString()) {
        dayDiv.classList.add('today');
    }

    // Day number
    const numberDiv = document.createElement('div');
    numberDiv.className = 'calendar-day-number';
    numberDiv.textContent = day;
    dayDiv.appendChild(numberDiv);

    // Blocks container
    const blocksDiv = document.createElement('div');
    blocksDiv.className = 'calendar-day-blocks';

    // Find blocks for this date
    blockedDates.forEach((block, index) => {
        const blockStart = new Date(block.start_date);
        const blockEnd = new Date(block.end_date);

        if (date >= blockStart && date <= blockEnd) {
            const vehicleIndex = vehicles.findIndex(v => v.id == block.vehicle_id);
            const color = vehicleColors[vehicleIndex % vehicleColors.length];

            const blockDiv = document.createElement('div');
            blockDiv.className = 'calendar-block';
            blockDiv.style.background = color;
            blockDiv.textContent = block.make + ' ' + block.model;
            blockDiv.title = block.reason || 'Blocked';
            blocksDiv.appendChild(blockDiv);
        }
    });

    dayDiv.appendChild(blocksDiv);

    // Click handler (only for current/future dates)
    if (!isOtherMonth && date >= new Date(today.setHours(0,0,0,0))) {
        dayDiv.onclick = () => openModal(dateStr);
    }

    return dayDiv;
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

function goToToday() {
    currentDate = new Date();
    renderCalendar();
}

function openModal(date) {
    document.getElementById('modalStartDate').value = date;
    document.getElementById('modalEndDate').value = date;
    document.getElementById('dateModal').classList.add('active');
}

function closeModal() {
    document.getElementById('dateModal').classList.remove('active');
}

// Initialize calendar on page load
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
});

// Auto-update end date minimum
document.getElementById('start_date')?.addEventListener('change', function() {
    const endDateInput = document.getElementById('end_date');
    if (endDateInput) {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
