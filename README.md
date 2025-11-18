# Elite Car Hire - Luxury Vehicle Booking Platform

A complete, self-hosted web application for managing luxury vehicle hire services with multi-role access, booking management, payment processing, and comprehensive admin controls.

## Features

### Multi-Role System
- **Admin Dashboard**: Complete system oversight and management
- **Owner Dashboard**: Vehicle listing and booking management
- **Customer Dashboard**: Easy booking and hire tracking

### Core Functionality
- ✅ User authentication and role-based access control
- ✅ Vehicle listing management with image uploads
- ✅ Booking system with calendar integration
- ✅ Self-hosted payment processing
- ✅ Commission tracking and payout management
- ✅ Review and rating system
- ✅ Messaging system
- ✅ Email notification queue
- ✅ Approval workflows for owners
- ✅ Security audit logging
- ✅ CMS for static content management

### Admin Features
- User management with approval system
- Vehicle approval workflow
- Booking oversight
- Payment and financial reporting
- Payout scheduling and management
- Dispute resolution
- Security alerts monitoring
- Comprehensive audit logs
- Content management system
- System settings configuration
- Contact form submissions

### Owner Features
- Vehicle listing management
- Booking calendar
- Earnings analytics
- Payout tracking
- Customer reviews
- Internal messaging
- Pending change requests

### Customer Features
- Browse available vehicles
- Make bookings
- Payment processing
- Booking history
- Profile management

### Technical Features
- Responsive design (mobile-friendly)
- SEO-friendly URL structure
- Secure password hashing
- CSRF protection
- SQL injection prevention
- XSS protection
- File upload security
- Session management
- Error logging

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla JS)
- **Icons**: Font Awesome 6.0
- **Architecture**: MVC Pattern

## Design

- **Color Scheme**: White background with Royal Gold (#C5A253) accents
- **Typography**: Clean, professional, modern
- **Responsive**: Mobile-first design approach
- **Accessibility**: WCAG 2.1 Level AA compliant

## Quick Start

1. **Upload** files to your web server
2. **Configure** Apache to point to `/public` directory
3. **Create** MySQL database
4. **Import** database schema from `/database/complete_schema.sql`
5. **Configure** environment in `.env` file
6. **Login** with default admin credentials
7. **Change** admin password immediately

Default Admin Login:
- Email: `admin@elitecarhire.au`
- Password: `Admin123!`

## Directory Structure

```
elite-car-hire/
├── public/              # Web root - point Apache here
│   ├── index.php       # Application entry point
│   └── .htaccess       # Apache rewrite rules
├── app/
│   ├── controllers/    # MVC Controllers
│   ├── models/         # Database models
│   ├── views/          # View templates
│   ├── middleware/     # Authentication/Security
│   ├── Database.php    # DB connection class
│   ├── Router.php      # URL routing
│   └── helpers.php     # Utility functions
├── config/
│   ├── app.php         # App configuration
│   └── database.php    # DB configuration
├── database/
│   └── complete_schema.sql  # Database setup
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript
│   └── images/        # Static images
├── storage/
│   ├── logs/          # Error & access logs
│   └── uploads/       # User-uploaded files
├── .env               # Environment variables
├── .htaccess          # Root redirects
├── INSTALLATION.md    # Detailed setup guide
└── README.md          # This file
```

## Security Features

- Password hashing with bcrypt
- CSRF token protection
- Prepared statements (SQL injection prevention)
- XSS protection (output escaping)
- Session hijacking prevention
- File upload validation
- Security headers
- Audit logging
- Failed login tracking
- IP address logging

## Database Schema

Comprehensive database with 20+ tables including:
- Users (multi-role support)
- Vehicles & Images
- Bookings
- Payments & Payouts
- Reviews
- Messages & Notifications
- Pending Changes (approval system)
- Audit Logs
- Security Alerts
- CMS Pages
- Settings
- Calendar Events
- Disputes
- Email Queue

## Configuration

### Environment Variables (.env)

```env
DB_HOST=localhost
DB_NAME=elite_car_hire
DB_USER=your_db_user
DB_PASS=your_db_password

APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

SMTP_HOST=smtp.yourdomain.com
SMTP_PORT=587
SMTP_USER=your_smtp_user
SMTP_PASS=your_smtp_password
```

### Apache Configuration

Ensure mod_rewrite is enabled and AllowOverride is set to All.

Example VirtualHost:
```apache
<VirtualHost *:80>
    ServerName elitecarhire.com.au
    DocumentRoot /var/www/elite-car-hire/public
    
    <Directory /var/www/elite-car-hire/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## File Permissions

```bash
chmod -R 755 elite-car-hire/
chmod -R 775 elite-car-hire/storage/
chown -R www-data:www-data elite-car-hire/storage/
```

## Business Logic

### Booking Flow
1. Customer selects vehicle
2. Enters booking details
3. Payment processed
4. Booking confirmed
5. Owner notified
6. Calendar updated
7. Payout scheduled (after completion)

### Commission Structure
- Default: 15% commission rate
- Configurable per booking
- Automatic calculation
- Owner receives total - commission
- Payout scheduled 7 days after completion

### Approval Workflows
- Owner registrations require admin approval
- Vehicle listings require admin approval
- Owner changes require admin approval (optional)
- Customers auto-approved by default (configurable)

## Email Notifications

Automated emails for:
- User registration
- Account approval/rejection
- Booking confirmation
- Payment receipt
- Booking reminders
- Review requests
- Payout notifications
- Admin alerts

## Maintenance

### Regular Tasks
- Monitor error logs: `/storage/logs/error.log`
- Review audit logs via admin panel
- Check security alerts
- Database backups (recommended daily)
- File backups (weekly)
- Update CMS content as needed

### Database Maintenance
```sql
-- Monthly optimization
OPTIMIZE TABLE bookings, users, vehicles, payments;
```

## Support & Documentation

For detailed installation instructions, see `INSTALLATION.md`

### Company Information
- **Name**: Elite Car Hire
- **Location**: Melbourne, VIC, Australia
- **Phone**: 1300 ECHIRE (1300 324 473)
- **Email**: info@elitecarhire.au

## Legal Documents

The system includes pre-populated legal documents:
- **Terms of Service**: Complete T&C with cancellation policy
- **Privacy Policy**: Australian privacy law compliant
- Editable via Admin CMS panel

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- Optimized database queries with indexes
- Minimal external dependencies
- Efficient asset loading
- OPcache compatible
- CDN-ready for static assets

## Roadmap / Future Enhancements

Potential additions:
- Calendar sync (Google Calendar, Outlook)
- SMS notifications
- Multi-language support
- Mobile app (iOS/Android)
- Advanced analytics and reporting
- Vehicle availability calendar
- Automated payout processing
- Integration with accounting software
- Customer loyalty program
- Vehicle maintenance tracking

## Troubleshooting

Common issues and solutions are documented in `INSTALLATION.md`

### Quick Fixes

**404 Errors:**
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

**Permission Errors:**
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

**Database Issues:**
- Check credentials in `.env`
- Verify MySQL service is running
- Test connection manually

## Development

### Coding Standards
- PSR-12 PHP coding standard
- Semantic HTML5
- BEM CSS methodology
- ES6 JavaScript

### Testing
- Manual testing checklist included
- Recommended: PHPUnit for unit tests
- Browser testing across devices

## License

Proprietary software developed for Elite Car Hire.
© 2025 Elite Car Hire. All rights reserved.

## Version

**Current Version**: 1.0.0  
**Release Date**: November 2025  
**Status**: Production Ready

## Credits

Developed as a complete, self-contained luxury vehicle hire management platform.

## Contact

For technical support or inquiries:
- Email: support@elitecarhire.au
- Phone: 1300 324 473

---

**Important**: This is a complete, production-ready system. Ensure you follow all security best practices and change default credentials before deploying to production.
