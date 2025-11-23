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
                    <a href="?status=all&view=<?= $view ?? 'table' ?>" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                    <a href="?status=pending&view=<?= $view ?? 'table' ?>" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                    <a href="?status=confirmed&view=<?= $view ?? 'table' ?>" class="btn <?= $status === 'confirmed' ? 'btn-primary' : 'btn-secondary' ?>">Confirmed</a>
                    <a href="?status=in_progress&view=<?= $view ?? 'table' ?>" class="btn <?= $status === 'in_progress' ? 'btn-primary' : 'btn-secondary' ?>">In Progress</a>
                    <a href="?status=completed&view=<?= $view ?? 'table' ?>" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                    <a href="?status=cancelled&view=<?= $view ?? 'table' ?>" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">View:</label>
                    <a href="?status=<?= $status ?>&view=table" class="btn <?= ($view ?? 'table') === 'table' ? 'btn-primary' : 'btn-secondary' ?>">
                        <i class="fas fa-list"></i> Table
                    </a>
                    <a href="?status=<?= $status ?>&view=calendar" class="btn <?= ($view ?? 'table') === 'calendar' ? 'btn-primary' : 'btn-secondary' ?>">
                        <i class="fas fa-calendar"></i> Calendar
                    </a>
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
            <?php if (($view ?? 'table') === 'table'): ?>
            <!-- Table View -->
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
                                            <form method="POST" action="/owner/bookings/confirm" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-success" style="padding: 5px 15px;"
                                                        onclick="return confirm('Confirm this booking?')">
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
            <?php else: ?>
            <!-- Calendar View -->
            <div class="card">
                <h2><i class="fas fa-calendar"></i> Calendar View</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Visual calendar showing all your bookings with status indicators.
                </p>

                <!-- Calendar Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                    <button onclick="changeMonth(-1)" class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <h3 id="calendarMonth" style="margin: 0; color: var(--dark-gray);"></h3>
                    <button onclick="changeMonth(1)" class="btn btn-secondary">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Calendar Grid -->
                <div id="calendar" class="booking-calendar"></div>

                <!-- Status Legend -->
                <div style="margin-top: 1.5rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                    <h4 style="margin-top: 0; margin-bottom: 1rem;">Status Legend:</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="status-indicator status-pending"></span>
                            <span>Pending</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="status-indicator status-confirmed"></span>
                            <span>Confirmed</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="status-indicator status-in_progress"></span>
                            <span>In Progress</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="status-indicator status-completed"></span>
                            <span>Completed</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="status-indicator status-cancelled"></span>
                            <span>Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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

    let calendarHTML = '<div class="calendar-grid">';

    // Day headers
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayNames.forEach(day => {
        calendarHTML += `<div class="calendar-day-header">${day}</div>`;
    });

    // Empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        calendarHTML += '<div class="calendar-day empty"></div>';
    }

    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayBookings = bookingsData.filter(b => b.date === dateStr);

        const isToday = new Date().toDateString() === new Date(currentYear, currentMonth, day).toDateString();

        calendarHTML += `<div class="calendar-day ${isToday ? 'today' : ''}">
            <div class="day-number">${day}</div>`;

        if (dayBookings.length > 0) {
            calendarHTML += '<div class="bookings-container">';
            dayBookings.forEach(booking => {
                const statusClass = booking.status.replace('_', '-');
                calendarHTML += `
                    <div class="booking-item status-${statusClass}"
                         onclick='showBookingDetails(${JSON.stringify(booking).replace(/'/g, "&#39;")})'
                         title="${booking.reference} - ${booking.vehicle}">
                        <div class="booking-time">${booking.start_time}</div>
                        <div class="booking-info">${booking.vehicle}</div>
                    </div>`;
            });
            calendarHTML += '</div>';
        }

        calendarHTML += '</div>';
    }

    calendarHTML += '</div>';
    calendar.innerHTML = calendarHTML;
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

// Close modals on outside click
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
