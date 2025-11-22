<div class="sidebar">
    <ul>
        <li><a href="/owner/dashboard" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/dashboard') !== false ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/owner/listings" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/listings') !== false ? 'active' : '' ?>"><i class="fas fa-car"></i> My Listings</a></li>
        <li><a href="/owner/bookings" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/bookings') !== false ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> Bookings</a></li>
        <li><a href="/owner/calendar" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/calendar') !== false ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
        <li><a href="/owner/analytics" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/analytics') !== false ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="/owner/payouts" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/payouts') !== false ? 'active' : '' ?>"><i class="fas fa-money-bill-wave"></i> Payouts</a></li>
        <li><a href="/owner/reviews" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/reviews') !== false ? 'active' : '' ?>"><i class="fas fa-star"></i> Reviews</a></li>
        <li><a href="/owner/messages" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/messages') !== false ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Messages</a></li>
        <li><a href="/owner/pending-changes" class="<?= strpos($_SERVER['REQUEST_URI'], '/owner/pending-changes') !== false ? 'active' : '' ?>"><i class="fas fa-clock"></i> Pending Changes</a></li>
    </ul>
</div>
