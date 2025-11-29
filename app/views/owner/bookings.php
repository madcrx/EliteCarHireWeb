<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-calendar-check"></i> Bookings</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Manage all bookings for your vehicles.
        </p>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 0;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                    <a href="?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                    <a href="?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                    <a href="?status=confirmed" class="btn <?= $status === 'confirmed' ? 'btn-primary' : 'btn-secondary' ?>">Confirmed</a>
                    <a href="?status=in_progress" class="btn <?= $status === 'in_progress' ? 'btn-primary' : 'btn-secondary' ?>">In Progress</a>
                    <a href="?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                    <a href="?status=cancelled" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
                </div>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Bookings Yet</h3>
                <p>You don't have any bookings at the moment. Customers will be able to book your approved vehicles.</p>
                <a href="/owner/listings" class="btn btn-primary" style="margin-top: 1rem;">View My Listings</a>
            </div>
        <?php else: ?>
            <!-- Table View - Display First -->
            <div class="card">
                <h2><i class="fas fa-list"></i> All Bookings</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Complete history of all bookings for your vehicles.
                </p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= e($booking['booking_reference']) ?></strong></td>
                                    <td>
                                        <?= e($booking['make'] . ' ' . $booking['model']) ?>
                                    </td>
                                    <td><?= e($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                    <td><?= $booking['duration_hours'] ?> hrs</td>
                                    <td style="font-weight: bold; color: var(--success);">$<?= number_format($booking['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = match($booking['status']) {
                                                'confirmed' => 'success',
                                                'in_progress' => 'warning',
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $booking['status'])) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                            $paymentClass = match($booking['payment_status']) {
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'refunded' => 'secondary',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $paymentClass ?>"><?= ucfirst($booking['payment_status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <button class="btn btn-primary" style="padding: 5px 15px; margin-bottom: 5px;"
                                                    onclick="showEditPriceModal(<?= $booking['id'] ?>, '<?= e($booking['booking_reference']) ?>', <?= $booking['base_amount'] ?>, <?= $booking['additional_charges'] ?? 0 ?>, <?= $booking['total_amount'] ?>)">
                                                <i class="fas fa-edit"></i> Edit Price
                                            </button>
                                            <form method="POST" action="/owner/bookings/confirm" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-success" style="padding: 5px 15px;"
                                                        onclick="return confirm('Confirm this booking? Customer will receive payment link for $<?= number_format($booking['total_amount'], 2) ?>')">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($booking['status'], ['pending', 'confirmed', 'in_progress'])): ?>
                                            <button class="btn btn-danger" style="padding: 5px 15px;"
                                                    onclick="showCancelModal(<?= $booking['id'] ?>, '<?= e($booking['booking_reference']) ?>')">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Calendar View - Moved to Bottom -->
            <div class="card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
                <!-- Calendar Container -->
                <div class="bookings-calendar-container">
                    <!-- Calendar Header with Navigation -->
                    <div class="bookings-calendar-header">
                        <button onclick="changeMonth(-1)" class="bookings-calendar-nav-btn">
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <h2 id="calendarMonth" class="bookings-calendar-month"></h2>
                        <button onclick="changeMonth(1)" class="bookings-calendar-nav-btn">
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>

                    <!-- Calendar Grid -->
                    <div id="calendar"></div>
                </div>

                <!-- Status Legend -->
                <div style="padding: 1.5rem; background: white; border-top: 1px solid var(--light-gray);">
                    <div style="display: flex; flex-wrap: wrap; gap: 2rem; align-items: center; justify-content: center;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div id="legend-available" style="width: 50px; height: 50px; background: white; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #a0a0a0; font-size: 1.2rem;">0</div>
                            <span style="font-weight: 700; font-size: 0.95rem; color: #333;">AVAILABLE</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div id="legend-booked" style="width: 50px; height: 50px; background: #dc3545; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #8b0000; font-size: 1.2rem;">0</div>
                            <span style="font-weight: 700; font-size: 0.95rem; color: #333;">BOOKED</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div id="legend-pending" style="width: 50px; height: 50px; background: #f39c12; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #b8860b; font-size: 1.2rem;">0</div>
                            <span style="font-weight: 700; font-size: 0.95rem; color: #333;">PENDING</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div id="legend-blocked" style="width: 50px; height: 50px; background: #e9ecef; opacity: 0.6; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #868e96; font-size: 1.2rem; text-decoration: line-through;">0</div>
                            <span style="font-weight: 700; font-size: 0.95rem; color: #333;">BLOCKED</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
                <ul>
                    <li><strong>Confirmed:</strong> Booking is confirmed and vehicle is reserved</li>
                    <li><strong>In Progress:</strong> Booking is currently active</li>
                    <li><strong>Completed:</strong> Booking has been completed successfully</li>
                    <li><strong>Cancelled:</strong> Booking was cancelled</li>
                    <li><strong>Commission:</strong> 15% platform fee applies to all bookings</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Price Modal -->
<div id="editPriceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: var(--border-radius); max-width: 500px; width: 90%;">
        <h2 style="margin-top: 0;"><i class="fas fa-edit"></i> Edit Booking Price</h2>
        <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
            Adjust the booking price for <strong id="editPriceBookingRef"></strong>
        </p>

        <form method="POST" action="/owner/bookings/update-price" id="editPriceForm">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="booking_id" id="editPriceBookingId">

            <div class="form-group">
                <label>Base Amount (Read-only)</label>
                <input type="text" id="editPriceBaseAmount" readonly style="background: #f8f9fa; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="additional_charges">Additional Charges (Extra Travel, etc.)</label>
                <input type="number" name="additional_charges" id="editPriceAdditionalCharges"
                       step="0.01" min="0" value="0"
                       oninput="updateTotalAmount()"
                       placeholder="0.00">
                <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                    Add any extra charges for excess travel or other additional services
                </small>
            </div>

            <div class="form-group">
                <label>Total Amount</label>
                <input type="text" id="editPriceTotalAmount" readonly style="background: #e7f3e7; font-weight: bold; font-size: 1.2rem; color: var(--success); cursor: not-allowed;">
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeEditPriceModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Price
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: var(--border-radius); max-width: 500px; width: 90%;">
        <h2 style="margin-top: 0;">Cancel Booking</h2>
        <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
            This will request admin approval to cancel booking <strong id="cancelBookingRef"></strong>.
        </p>

        <form method="POST" action="/owner/bookings/cancel" id="cancelForm">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="booking_id" id="cancelBookingId">

            <div class="form-group">
                <label for="cancellation_reason">Cancellation Reason *</label>
                <textarea name="cancellation_reason" id="cancellation_reason" rows="4" required
                          placeholder="Please provide a reason for cancelling this booking..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Submit Cancellation Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="bookingDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto; padding: 20px;">
    <div style="background: white; padding: 2rem; border-radius: var(--border-radius); max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">Booking Details</h2>
            <button onclick="closeBookingDetailsModal()" class="btn btn-secondary" style="padding: 5px 15px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="bookingDetailsContent"></div>
    </div>
</div>

<script>
// Bookings data for calendar (PHP to JavaScript)
const bookingsData = <?= json_encode(array_map(function($booking) {
    return [
        'id' => $booking['id'],
        'reference' => $booking['booking_reference'],
        'date' => $booking['booking_date'],
        'start_time' => date('g:i A', strtotime($booking['start_time'])),
        'end_time' => date('g:i A', strtotime($booking['end_time'])),
        'vehicle' => $booking['make'] . ' ' . $booking['model'],
        'customer' => $booking['first_name'] . ' ' . $booking['last_name'],
        'status' => $booking['status'],
        'payment_status' => $booking['payment_status'],
        'duration' => $booking['duration_hours'],
        'amount' => $booking['total_amount'],
        'pickup_location' => $booking['pickup_location'] ?? '',
        'dropoff_location' => $booking['dropoff_location'] ?? ''
    ];
}, $bookings ?? [])) ?>;

// Blocked dates data for calendar
const blockedDatesData = <?= json_encode(array_map(function($block) {
    return [
        'id' => $block['id'],
        'vehicle_id' => $block['vehicle_id'],
        'vehicle' => $block['make'] . ' ' . $block['model'],
        'start_date' => $block['start_date'],
        'end_date' => $block['end_date'],
        'reason' => $block['reason'] ?? 'Blocked'
    ];
}, $blockedDates ?? [])) ?>;

let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

function showCancelModal(bookingId, bookingRef) {
    document.getElementById('cancelBookingId').value = bookingId;
    document.getElementById('cancelBookingRef').textContent = bookingRef;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    document.getElementById('cancellation_reason').value = '';
}

function showBookingDetails(booking) {
    const statusClass = {
        'pending': 'secondary',
        'confirmed': 'success',
        'in_progress': 'warning',
        'completed': 'info',
        'cancelled': 'danger'
    }[booking.status] || 'secondary';

    const paymentClass = {
        'paid': 'success',
        'pending': 'warning',
        'refunded': 'secondary',
        'failed': 'danger'
    }[booking.payment_status] || 'secondary';

    const content = `
        <div style="border-bottom: 2px solid var(--light-gray); padding-bottom: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <strong style="font-size: 1.2rem;">${booking.reference}</strong>
                <span class="badge badge-${statusClass}">${booking.status.replace('_', ' ').toUpperCase()}</span>
            </div>
            <span class="badge badge-${paymentClass}">${booking.payment_status.toUpperCase()}</span>
        </div>

        <div style="display: grid; gap: 1rem;">
            <div>
                <strong style="color: var(--dark-gray);">Vehicle:</strong>
                <p style="margin: 0.25rem 0 0 0;">${booking.vehicle}</p>
            </div>

            <div>
                <strong style="color: var(--dark-gray);">Customer:</strong>
                <p style="margin: 0.25rem 0 0 0;">${booking.customer}</p>
            </div>

            <div>
                <strong style="color: var(--dark-gray);">Date & Time:</strong>
                <p style="margin: 0.25rem 0 0 0;">${new Date(booking.date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                <p style="margin: 0.25rem 0 0 0;">${booking.start_time} - ${booking.end_time} (${booking.duration} hrs)</p>
            </div>

            ${booking.pickup_location ? `
            <div>
                <strong style="color: var(--dark-gray);">Pickup Location:</strong>
                <p style="margin: 0.25rem 0 0 0;">${booking.pickup_location}</p>
            </div>
            ` : ''}

            ${booking.dropoff_location ? `
            <div>
                <strong style="color: var(--dark-gray);">Dropoff Location:</strong>
                <p style="margin: 0.25rem 0 0 0;">${booking.dropoff_location}</p>
            </div>
            ` : ''}

            <div style="border-top: 2px solid var(--light-gray); padding-top: 1rem;">
                <strong style="color: var(--dark-gray); font-size: 1.1rem;">Total Amount:</strong>
                <p style="margin: 0.25rem 0 0 0; font-size: 1.5rem; font-weight: bold; color: var(--primary-gold);">$${parseFloat(booking.amount).toFixed(2)}</p>
            </div>
        </div>
    `;

    document.getElementById('bookingDetailsContent').innerHTML = content;
    document.getElementById('bookingDetailsModal').style.display = 'flex';
}

function closeBookingDetailsModal() {
    document.getElementById('bookingDetailsModal').style.display = 'none';
}

function renderCalendar() {
    const calendar = document.getElementById('calendar');
    if (!calendar) return;

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];

    document.getElementById('calendarMonth').textContent = `${monthNames[currentMonth]} ${currentYear}`;

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    let calendarHTML = '<div class="calendar-grid-bookings">';

    // Day headers
    const dayNames = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
    dayNames.forEach(day => {
        calendarHTML += `<div class="calendar-day-header-bookings">${day}</div>`;
    });

    // Empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        calendarHTML += '<div class="calendar-day-bookings empty"></div>';
    }

    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayBookings = bookingsData.filter(b => b.date === dateStr);

        const isToday = new Date().toDateString() === new Date(currentYear, currentMonth, day).toDateString();

        // Check if this date is blocked
        const currentDate = new Date(currentYear, currentMonth, day);
        currentDate.setHours(0, 0, 0, 0);
        const isBlocked = blockedDatesData.some(block => {
            const blockStart = new Date(block.start_date + 'T00:00:00');
            const blockEnd = new Date(block.end_date + 'T00:00:00');
            blockStart.setHours(0, 0, 0, 0);
            blockEnd.setHours(0, 0, 0, 0);
            return currentDate >= blockStart && currentDate <= blockEnd;
        });

        // Determine the primary status for the day
        let dayStatus = 'available';
        let bookingCount = dayBookings.length;
        let hasActiveBookings = dayBookings.some(b => b.status === 'confirmed' || b.status === 'in_progress' || b.status === 'pending');

        // Check for CONFLICT: blocked date with active bookings
        if (isBlocked && hasActiveBookings) {
            dayStatus = 'conflict';
        } else if (isBlocked) {
            // Blocked dates without bookings
            dayStatus = 'blocked';
        } else if (dayBookings.length > 0) {
            // Priority: confirmed/in_progress (red) > pending (orange) > completed/cancelled (gray)
            if (dayBookings.some(b => b.status === 'confirmed' || b.status === 'in_progress')) {
                dayStatus = 'booked';
            } else if (dayBookings.some(b => b.status === 'pending')) {
                dayStatus = 'pending';
            } else {
                dayStatus = 'completed';
            }
        }

        const todayClass = isToday ? 'today' : '';
        const clickHandler = dayBookings.length > 0 ? `onclick="showDayBookings('${dateStr}')"` : '';
        let statusTitle = 'Available';
        if (dayStatus === 'conflict') {
            statusTitle = `⚠️ CONFLICT: ${bookingCount} booking(s) on blocked date!`;
        } else if (isBlocked) {
            statusTitle = 'Blocked';
        } else if (dayBookings.length > 0) {
            statusTitle = dayBookings.length + ' booking(s)';
        }

        calendarHTML += `<div class="calendar-day-bookings ${todayClass} status-${dayStatus}" ${clickHandler} title="${statusTitle}">
            <div class="day-number-bookings">${day}</div>
            ${bookingCount > 0 ? `<div class="booking-count-badge">${bookingCount}</div>` : ''}
            ${dayStatus === 'conflict' ? '<div class="conflict-indicator">⚠️</div>' : ''}
        </div>`;
    }

    calendarHTML += '</div>';
    calendar.innerHTML = calendarHTML;

    // Update legend counts for the current month
    updateLegendCounts();
}

function updateLegendCounts() {
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    let availableCount = 0;
    let bookedCount = 0;
    let pendingCount = 0;
    let blockedCount = 0;

    // Count each day in the current month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayBookings = bookingsData.filter(b => b.date === dateStr);
        const currentDate = new Date(currentYear, currentMonth, day);
        currentDate.setHours(0, 0, 0, 0);

        // Check if blocked
        const isBlocked = blockedDatesData.some(block => {
            const blockStart = new Date(block.start_date + 'T00:00:00');
            const blockEnd = new Date(block.end_date + 'T00:00:00');
            blockStart.setHours(0, 0, 0, 0);
            blockEnd.setHours(0, 0, 0, 0);
            return currentDate >= blockStart && currentDate <= blockEnd;
        });

        const hasActiveBookings = dayBookings.some(b => b.status === 'confirmed' || b.status === 'in_progress' || b.status === 'pending');

        // Check for conflict first
        if (isBlocked && hasActiveBookings) {
            // Conflicts count as booked (since they need immediate attention)
            bookedCount++;
        } else if (isBlocked) {
            blockedCount++;
        } else if (dayBookings.length > 0) {
            // Check booking status
            if (dayBookings.some(b => b.status === 'confirmed' || b.status === 'in_progress')) {
                bookedCount++;
            } else if (dayBookings.some(b => b.status === 'pending')) {
                pendingCount++;
            }
        } else {
            availableCount++;
        }
    }

    // Update legend display
    document.getElementById('legend-available').textContent = availableCount;
    document.getElementById('legend-booked').textContent = bookedCount;
    document.getElementById('legend-pending').textContent = pendingCount;
    document.getElementById('legend-blocked').textContent = blockedCount;
}

function showDayBookings(dateStr) {
    const dayBookings = bookingsData.filter(b => b.date === dateStr);

    if (dayBookings.length === 1) {
        showBookingDetails(dayBookings[0]);
    } else {
        // Show list of bookings for that day
        let listHTML = `<h3 style="margin-top: 0;">Bookings for ${new Date(dateStr).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h3>`;
        listHTML += '<div style="display: flex; flex-direction: column; gap: 1rem;">';

        dayBookings.forEach(booking => {
            const statusClass = {
                'pending': 'secondary',
                'confirmed': 'success',
                'in_progress': 'warning',
                'completed': 'info',
                'cancelled': 'danger'
            }[booking.status] || 'secondary';

            listHTML += `
                <div style="padding: 1rem; border: 2px solid var(--light-gray); border-radius: var(--border-radius); cursor: pointer; transition: all 0.2s;"
                     onclick="showBookingDetails(${JSON.stringify(booking).replace(/'/g, '&#39;')})"
                     onmouseover="this.style.borderColor='var(--primary-gold)'; this.style.background='#fffbf0';"
                     onmouseout="this.style.borderColor='var(--light-gray)'; this.style.background='white';">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <strong>${booking.vehicle}</strong>
                        <span class="badge badge-${statusClass}">${booking.status.replace('_', ' ').toUpperCase()}</span>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--dark-gray);">
                        <div>${booking.reference}</div>
                        <div>${booking.start_time} - ${booking.end_time}</div>
                        <div>${booking.customer}</div>
                    </div>
                </div>
            `;
        });

        listHTML += '</div>';

        document.getElementById('bookingDetailsContent').innerHTML = listHTML;
        document.getElementById('bookingDetailsModal').style.display = 'flex';
    }
}

function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    } else if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar();
}

// Edit Price Modal Functions
let currentBaseAmount = 0;

function showEditPriceModal(bookingId, bookingRef, baseAmount, additionalCharges, totalAmount) {
    currentBaseAmount = parseFloat(baseAmount);
    document.getElementById('editPriceBookingId').value = bookingId;
    document.getElementById('editPriceBookingRef').textContent = bookingRef;
    document.getElementById('editPriceBaseAmount').value = '$' + baseAmount.toFixed(2);
    document.getElementById('editPriceAdditionalCharges').value = parseFloat(additionalCharges).toFixed(2);
    document.getElementById('editPriceTotalAmount').value = '$' + totalAmount.toFixed(2);
    document.getElementById('editPriceModal').style.display = 'flex';
}

function closeEditPriceModal() {
    document.getElementById('editPriceModal').style.display = 'none';
    document.getElementById('editPriceForm').reset();
}

function updateTotalAmount() {
    const additionalCharges = parseFloat(document.getElementById('editPriceAdditionalCharges').value) || 0;
    const totalAmount = currentBaseAmount + additionalCharges;
    document.getElementById('editPriceTotalAmount').value = '$' + totalAmount.toFixed(2);
}

// Close modals on outside click
document.getElementById('editPriceModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditPriceModal();
    }
});

document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});

document.getElementById('bookingDetailsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeBookingDetailsModal();
    }
});

// Initialize calendar if in calendar view
if (document.getElementById('calendar')) {
    renderCalendar();
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
