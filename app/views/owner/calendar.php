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

.vehicle-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
}

.vehicle-checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 2px solid var(--medium-gray);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
}

.vehicle-checkbox-item:hover {
    border-color: var(--primary-gold);
    background: #fffbf0;
}

.vehicle-checkbox-item.selected {
    border-color: var(--primary-gold);
    background: var(--primary-gold);
    color: white;
}

.vehicle-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
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

.day-selector {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.day-checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    background: var(--light-gray);
    border: 2px solid var(--medium-gray);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
}

.day-checkbox-item:hover {
    border-color: var(--primary-gold);
}

.day-checkbox-item.checked {
    background: var(--primary-gold);
    border-color: var(--primary-gold);
    color: white;
}

.calendar-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: white;
    border-bottom: 1px solid var(--medium-gray);
}
</style>

<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-calendar-alt"></i> Calendar & Date Blocking</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Select a vehicle below, then click any date on the calendar to block or unblock it.
        </p>

        <?php if (empty($vehicles)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <p>You don't have any approved vehicles yet. Once your vehicles are approved, you can manage availability here.</p>
                <a href="/owner/listings" class="btn btn-primary" style="margin-top: 1rem;">View My Listings</a>
            </div>
        <?php else: ?>
            <!-- Vehicle Selection -->
            <?php
                // Define colors array at template scope so it's accessible to JavaScript
                $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'];
            ?>
            <div class="card" style="padding: 0;">
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--medium-gray); background: var(--light-gray);">
                    <strong style="display: block; margin-bottom: 0.5rem;"><i class="fas fa-car"></i> Select Vehicles to Block Dates:</strong>
                    <p style="margin: 0; font-size: 0.9rem; color: var(--dark-gray);">Click one or more vehicles to select them, then click dates on the calendar to block/unblock. Click selected vehicles again to deselect.</p>
                </div>
                <div class="vehicle-selector">
                    <?php
                        foreach ($vehicles as $index => $vehicle):
                            $vehicleColor = $colors[$index % count($colors)];
                    ?>
                        <div class="vehicle-checkbox-item" data-vehicle-id="<?= $vehicle['id'] ?>" data-color="<?= $vehicleColor ?>" onclick="selectVehicle(<?= $vehicle['id'] ?>, '<?= $vehicleColor ?>')">
                            <input type="checkbox" class="vehicle-checkbox" id="vehicle_<?= $vehicle['id'] ?>" value="<?= $vehicle['id'] ?>">
                            <div class="legend-color" style="background: <?= $vehicleColor ?>"></div>
                            <label for="vehicle_<?= $vehicle['id'] ?>" style="cursor: pointer; margin: 0;">
                                <?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Visual Calendar -->
            <div class="card" style="padding: 0;">
                <div class="calendar-container">
                    <!-- Filter Bar -->
                    <div class="calendar-filter-bar">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <label for="vehicleFilter" style="font-weight: 600; margin: 0;">
                                <i class="fas fa-filter"></i> View:
                            </label>
                            <select id="vehicleFilter" onchange="filterCalendar()" style="padding: 0.5rem; border: 1px solid var(--medium-gray); border-radius: var(--border-radius);">
                                <option value="all">All Vehicles</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?= $vehicle['id'] ?>">
                                        <?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="selectedVehicleIndicator" style="color: var(--dark-gray); font-style: italic;">
                            No vehicles selected for blocking
                        </div>
                    </div>

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

            <!-- Quick Block Form -->
            <div class="card">
                <h2><i class="fas fa-calendar-plus"></i> Quick Block Dates</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Block multiple dates at once with advanced frequency options.
                </p>

                <form method="POST" action="/owner/calendar/block" id="blockForm">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="quick_vehicle_id">Vehicle *</label>
                            <select name="vehicle_id" id="quick_vehicle_id" required>
                                <option value="">-- Select Vehicle --</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?= $vehicle['id'] ?>">
                                        <?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="start_date">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="end_date">End Date *</label>
                            <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="frequency">Frequency *</label>
                        <select name="frequency" id="frequency" required onchange="toggleDaySelector()">
                            <option value="daily">Daily (Every day in range)</option>
                            <option value="weekdays">Weekdays Only (Mon-Fri)</option>
                            <option value="weekends">Weekends Only (Sat-Sun)</option>
                            <option value="custom">Custom Days (Select below)</option>
                        </select>
                        <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                            Choose which days within the date range should be blocked
                        </small>
                    </div>

                    <div class="form-group" id="daySelectorGroup" style="display: none;">
                        <label>Select Days of Week to Block *</label>
                        <div class="day-selector">
                            <div class="day-checkbox-item" onclick="toggleDay(this, 1)">
                                <input type="checkbox" name="days[]" value="1" id="day_mon">
                                <label for="day_mon" style="cursor: pointer; margin: 0;">Mo</label>
                            </div>
                            <div class="day-checkbox-item" onclick="toggleDay(this, 2)">
                                <input type="checkbox" name="days[]" value="2" id="day_tue">
                                <label for="day_tue" style="cursor: pointer; margin: 0;">Tu</label>
                            </div>
                            <div class="day-checkbox-item" onclick="toggleDay(this, 3)">
                                <input type="checkbox" name="days[]" value="3" id="day_wed">
                                <label for="day_wed" style="cursor: pointer; margin: 0;">We</label>
                            </div>
                            <div class="day-checkbox-item" onclick="toggleDay(this, 4)">
                                <input type="checkbox" name="days[]" value="4" id="day_thu">
                                <label for="day_thu" style="cursor: pointer; margin: 0;">Th</label>
                            </div>
                            <div class="day-checkbox-item" onclick="toggleDay(this, 5)">
                                <input type="checkbox" name="days[]" value="5" id="day_fri">
                                <label for="day_fri" style="cursor: pointer; margin: 0;">Fr</label>
                            </div>
                            <div class="day-checkbox-item" onclick="toggleDay(this, 6)">
                                <input type="checkbox" name="days[]" value="6" id="day_sat">
                                <label for="day_sat" style="cursor: pointer; margin: 0;">Sa</label>
                            </div>
                            <div class="day-checkbox-item" onclick="toggleDay(this, 0)">
                                <input type="checkbox" name="days[]" value="0" id="day_sun">
                                <label for="day_sun" style="cursor: pointer; margin: 0;">Su</label>
                            </div>
                        </div>
                        <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                            Only the checked days within the date range will be blocked
                        </small>
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

                <!-- Filter and Actions Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" id="blockedDatesFilter" placeholder="Search by vehicle, date, or reason..."
                               style="width: 100%; padding: 0.5rem; border: 1px solid var(--medium-gray); border-radius: var(--border-radius);"
                               onkeyup="filterBlockedDates()">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span id="selectedBlocksCount" style="color: var(--dark-gray); font-size: 0.9rem;"></span>
                        <button id="bulkUnblockBtn" class="btn btn-warning" style="display: none;" onclick="bulkUnblockDates()">
                            <i class="fas fa-unlock"></i> Unblock Selected
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table id="blockedDatesTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAllBlocks" onchange="toggleSelectAllBlocks()"
                                           title="Select all visible blocks">
                                </th>
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
                                <tr class="blocked-date-row"
                                    data-vehicle="<?= e($block['make'] . ' ' . $block['model']) ?>"
                                    data-start="<?= date('M d, Y', strtotime($block['start_date'])) ?>"
                                    data-end="<?= date('M d, Y', strtotime($block['end_date'])) ?>"
                                    data-reason="<?= e($block['reason'] ?? '') ?>"
                                    data-block-id="<?= $block['id'] ?>"
                                    data-is-past="<?= $isPast ? 'true' : 'false' ?>"
                                    style="<?= $isPast ? 'opacity: 0.6;' : '' ?>">
                                    <td>
                                        <?php if (!$isPast): ?>
                                            <input type="checkbox" class="block-checkbox" value="<?= $block['id'] ?>"
                                                   onchange="updateBulkUnblockButton()">
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= e($block['make'] . ' ' . $block['model']) ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($block['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($block['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $days ?> days</span></td>
                                    <td><?= !empty($block['reason']) ? e($block['reason']) : '<em style="color: var(--medium-gray);">No reason</em>' ?></td>
                                    <td>
                                        <?php if (!$isPast): ?>
                                            <button class="btn btn-warning" style="padding: 5px 15px;"
                                                    onclick="if(confirm('Unblock these dates?')) { unblockDate(<?= $block['id'] ?>); }">
                                                <i class="fas fa-unlock"></i> Unblock
                                            </button>
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

<script>
// Data from PHP
const vehicles = <?= json_encode($vehicles) ?>;
const blockedDates = <?= json_encode($blockedDates) ?>;
const vehicleColors = <?= json_encode($colors) ?>;

let currentDate = new Date();
let selectedVehicleIds = []; // Changed to array for multiple selection
let calendarFilterVehicleId = 'all';

// Helper function to format date without timezone issues
function formatDateLocal(year, month, day) {
    const y = year.toString().padStart(4, '0');
    const m = (month + 1).toString().padStart(2, '0');
    const d = day.toString().padStart(2, '0');
    return `${y}-${m}-${d}`;
}

// Helper function to parse date string to local date
function parseLocalDate(dateStr) {
    const [year, month, day] = dateStr.split('-').map(Number);
    return new Date(year, month - 1, day);
}

function selectVehicle(vehicleId, color) {
    const checkbox = document.getElementById('vehicle_' + vehicleId);
    const item = checkbox.closest('.vehicle-checkbox-item');

    const index = selectedVehicleIds.indexOf(vehicleId);

    if (index > -1) {
        // Deselect vehicle
        selectedVehicleIds.splice(index, 1);
        checkbox.checked = false;
        item.classList.remove('selected');
    } else {
        // Select vehicle
        selectedVehicleIds.push(vehicleId);
        checkbox.checked = true;
        item.classList.add('selected');
    }

    // Update indicator
    updateSelectedVehicleIndicator();
}

function updateSelectedVehicleIndicator() {
    const indicator = document.getElementById('selectedVehicleIndicator');

    if (selectedVehicleIds.length === 0) {
        indicator.textContent = 'No vehicles selected for blocking';
    } else if (selectedVehicleIds.length === 1) {
        const vehicleName = vehicles.find(v => v.id == selectedVehicleIds[0]);
        indicator.innerHTML = '<i class="fas fa-car"></i> Click dates to block: <strong>' +
            vehicleName.make + ' ' + vehicleName.model + '</strong>';
    } else {
        indicator.innerHTML = '<i class="fas fa-car"></i> Click dates to block: <strong>' +
            selectedVehicleIds.length + ' vehicles selected</strong>';
    }
}

function filterCalendar() {
    calendarFilterVehicleId = document.getElementById('vehicleFilter').value;
    renderCalendar();
}

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
    const dateStr = formatDateLocal(year, month, day); // Fixed: use local date formatting

    // Check if today
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const compareDate = new Date(year, month, day);
    compareDate.setHours(0, 0, 0, 0);

    if (compareDate.getTime() === today.getTime()) {
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

    // Find blocks for this date (filtered by selected vehicle if applicable)
    blockedDates.forEach((block, index) => {
        const blockStart = parseLocalDate(block.start_date);
        const blockEnd = parseLocalDate(block.end_date);

        // Check filter
        if (calendarFilterVehicleId !== 'all' && block.vehicle_id != calendarFilterVehicleId) {
            return;
        }

        if (compareDate >= blockStart && compareDate <= blockEnd) {
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
    if (!isOtherMonth && compareDate >= today) {
        dayDiv.onclick = () => handleDayClick(dateStr);
    }

    return dayDiv;
}

function handleDayClick(dateStr) {
    if (selectedVehicleIds.length === 0) {
        alert('Please select at least one vehicle first by clicking on it above the calendar.');
        return;
    }

    const clickedDate = parseLocalDate(dateStr);
    const blocksToAdd = [];
    const blocksToRemove = [];

    // Process each selected vehicle
    selectedVehicleIds.forEach(vehicleId => {
        // Check if this date is already blocked for this vehicle
        const existingBlock = blockedDates.find(block => {
            const blockStart = parseLocalDate(block.start_date);
            const blockEnd = parseLocalDate(block.end_date);
            return block.vehicle_id == vehicleId &&
                   clickedDate >= blockStart && clickedDate <= blockEnd;
        });

        if (existingBlock) {
            blocksToRemove.push(existingBlock);
        } else {
            blocksToAdd.push({ vehicleId, dateStr });
        }
    });

    // Handle unblocking
    if (blocksToRemove.length > 0) {
        const vehicleNames = blocksToRemove.map(b => `${b.make} ${b.model}`).join(', ');
        if (confirm(`This date is already blocked for: ${vehicleNames}. Do you want to unblock?`)) {
            Promise.all(blocksToRemove.map(block => unblockDateAsync(block.id)))
                .then(() => {
                    saveStateAndReload();
                });
        }
    }

    // Handle blocking
    if (blocksToAdd.length > 0) {
        Promise.all(blocksToAdd.map(item => blockSingleDayAsync(item.dateStr, item.vehicleId)))
            .then(() => {
                saveStateAndReload();
            });
    }
}

// Helper function to save state and reload
function saveStateAndReload() {
    sessionStorage.setItem('calendarScrollPos', window.scrollY.toString());
    sessionStorage.setItem('calendarYear', currentDate.getFullYear().toString());
    sessionStorage.setItem('calendarMonth', currentDate.getMonth().toString());
    sessionStorage.setItem('selectedVehicleIds', JSON.stringify(selectedVehicleIds));
    window.location.reload();
}

// Async version that doesn't reload automatically
async function blockSingleDayAsync(dateStr, vehicleId) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= csrfToken() ?>');
    formData.append('vehicle_id', vehicleId);
    formData.append('start_date', dateStr);
    formData.append('end_date', dateStr);
    formData.append('frequency', 'daily');

    const response = await fetch('/owner/calendar/block', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        throw new Error('Failed to block date');
    }
    return response;
}

// Async version that doesn't reload automatically
async function unblockDateAsync(blockId) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= csrfToken() ?>');
    formData.append('block_id', blockId);

    const response = await fetch('/owner/calendar/unblock', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        throw new Error('Failed to unblock date');
    }
    return response;
}

// Keep these for the Blocked Dates table unblock buttons
async function unblockDate(blockId) {
    try {
        await unblockDateAsync(blockId);
        saveStateAndReload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error unblocking date. Please try again.');
    }
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

function goToToday() {
    currentDate = new Date();
    renderCalendar();
}

function toggleDaySelector() {
    const frequency = document.getElementById('frequency').value;
    const daySelectorGroup = document.getElementById('daySelectorGroup');

    if (frequency === 'custom') {
        daySelectorGroup.style.display = 'block';
    } else {
        daySelectorGroup.style.display = 'none';
    }
}

function toggleDay(element, dayValue) {
    const checkbox = element.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;

    if (checkbox.checked) {
        element.classList.add('checked');
    } else {
        element.classList.remove('checked');
    }
}

// Initialize calendar on page load
document.addEventListener('DOMContentLoaded', () => {
    // Restore calendar month if returning from a block/unblock action
    const savedYear = sessionStorage.getItem('calendarYear');
    const savedMonth = sessionStorage.getItem('calendarMonth');

    // Check for !== null instead of truthy check (month 0 = January is falsy!)
    if (savedYear !== null && savedMonth !== null) {
        currentDate = new Date(parseInt(savedYear), parseInt(savedMonth), 1);
        sessionStorage.removeItem('calendarYear');
        sessionStorage.removeItem('calendarMonth');
    }

    // Restore selected vehicles
    const savedVehicleIds = sessionStorage.getItem('selectedVehicleIds');
    if (savedVehicleIds !== null) {
        try {
            selectedVehicleIds = JSON.parse(savedVehicleIds);
            sessionStorage.removeItem('selectedVehicleIds');

            // Update UI to show selected vehicles
            selectedVehicleIds.forEach(vehicleId => {
                const checkbox = document.getElementById('vehicle_' + vehicleId);
                const item = checkbox?.closest('.vehicle-checkbox-item');
                if (checkbox && item) {
                    checkbox.checked = true;
                    item.classList.add('selected');
                }
            });

            // Update indicator
            updateSelectedVehicleIndicator();
        } catch (e) {
            console.error('Error restoring selected vehicles:', e);
            selectedVehicleIds = [];
        }
    }

    // Render the calendar
    renderCalendar();

    // Restore scroll position after calendar is rendered
    const savedScrollPos = sessionStorage.getItem('calendarScrollPos');
    if (savedScrollPos !== null) {
        // Use setTimeout to ensure DOM is fully rendered
        setTimeout(() => {
            window.scrollTo({
                top: parseInt(savedScrollPos),
                behavior: 'instant'
            });
            sessionStorage.removeItem('calendarScrollPos');
        }, 100);
    }
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

// Prevent label click from toggling checkbox (we handle it in toggleDay)
document.querySelectorAll('.day-checkbox-item label').forEach(label => {
    label.addEventListener('click', (e) => e.preventDefault());
});

// ===== Blocked Dates Table Functions =====

// Filter blocked dates table
function filterBlockedDates() {
    const filterValue = document.getElementById('blockedDatesFilter')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('.blocked-date-row');

    rows.forEach(row => {
        const vehicle = row.dataset.vehicle.toLowerCase();
        const start = row.dataset.start.toLowerCase();
        const end = row.dataset.end.toLowerCase();
        const reason = row.dataset.reason.toLowerCase();

        const matches = vehicle.includes(filterValue) ||
                       start.includes(filterValue) ||
                       end.includes(filterValue) ||
                       reason.includes(filterValue);

        row.style.display = matches ? '' : 'none';
    });

    // Update select all checkbox state
    updateSelectAllCheckbox();
    // Update bulk unblock button
    updateBulkUnblockButton();
}

// Toggle select all visible checkboxes
function toggleSelectAllBlocks() {
    const selectAllCheckbox = document.getElementById('selectAllBlocks');
    const visibleCheckboxes = Array.from(document.querySelectorAll('.block-checkbox'))
        .filter(cb => cb.closest('tr').style.display !== 'none');

    visibleCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateBulkUnblockButton();
}

// Update the select all checkbox state based on current selection
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAllBlocks');
    const visibleCheckboxes = Array.from(document.querySelectorAll('.block-checkbox'))
        .filter(cb => cb.closest('tr').style.display !== 'none');

    if (visibleCheckboxes.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        return;
    }

    const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;

    if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCount === visibleCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
}

// Update bulk unblock button visibility and count
function updateBulkUnblockButton() {
    const checkedBoxes = document.querySelectorAll('.block-checkbox:checked');
    const count = checkedBoxes.length;
    const button = document.getElementById('bulkUnblockBtn');
    const countSpan = document.getElementById('selectedBlocksCount');

    if (count > 0) {
        button.style.display = 'block';
        countSpan.textContent = `${count} selected`;
    } else {
        button.style.display = 'none';
        countSpan.textContent = '';
    }

    updateSelectAllCheckbox();
}

// Bulk unblock selected dates
async function bulkUnblockDates() {
    const checkedBoxes = document.querySelectorAll('.block-checkbox:checked');
    const blockIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (blockIds.length === 0) {
        alert('No dates selected for unblocking.');
        return;
    }

    const message = `Are you sure you want to unblock ${blockIds.length} blocked date${blockIds.length > 1 ? 's' : ''}?`;

    if (!confirm(message)) {
        return;
    }

    try {
        // Unblock all selected dates
        await Promise.all(blockIds.map(id => unblockDateAsync(id)));
        saveStateAndReload();
    } catch (error) {
        console.error('Error unblocking dates:', error);
        alert('Error unblocking some dates. Please try again.');
    }
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
