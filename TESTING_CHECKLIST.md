# Elite Car Hire - Testing Checklist

## Pre-Deployment Testing

### Environment Setup
- [ ] Database created and schema imported
- [ ] `.env` file configured correctly
- [ ] File permissions set (storage: 775)
- [ ] Apache mod_rewrite enabled
- [ ] PHP 7.4+ confirmed
- [ ] MySQL connection successful

### Authentication Testing
- [ ] **Register new customer account**
  - [ ] Form validation works
  - [ ] Password requirements enforced
  - [ ] Email validation works
  - [ ] Successful registration
  - [ ] Auto-approval works (if enabled)
  
- [ ] **Register new owner account**
  - [ ] Registration successful
  - [ ] Status set to "pending"
  - [ ] Cannot access dashboard until approved
  
- [ ] **Login functionality**
  - [ ] Admin can login
  - [ ] Customer can login  
  - [ ] Owner can login (after approval)
  - [ ] Invalid credentials rejected
  - [ ] Session persists correctly
  
- [ ] **Logout**
  - [ ] Logout clears session
  - [ ] Cannot access protected pages
  - [ ] Redirects to home/login

- [ ] **Password Security**
  - [ ] Default admin password changed
  - [ ] Passwords are hashed
  - [ ] Weak passwords rejected

### Admin Dashboard Testing
- [ ] **Dashboard Access**
  - [ ] Dashboard loads correctly
  - [ ] Statistics display accurately
  - [ ] Recent bookings shown
  - [ ] Pending users visible
  
- [ ] **User Management**
  - [ ] View all users
  - [ ] Filter by role
  - [ ] Filter by status
  - [ ] Approve pending users
  - [ ] Reject users
  - [ ] View user details
  
- [ ] **Vehicle Management**
  - [ ] View all vehicles
  - [ ] Filter by status
  - [ ] Approve pending vehicles
  - [ ] Reject vehicles
  - [ ] View vehicle details
  
- [ ] **Bookings**
  - [ ] View all bookings
  - [ ] Filter by status
  - [ ] View booking details
  - [ ] Commission calculated correctly
  
- [ ] **Payments**
  - [ ] View all payments
  - [ ] Transaction details accurate
  - [ ] Status tracking works
  
- [ ] **Payouts**
  - [ ] View all payouts
  - [ ] Owner earnings correct
  - [ ] Schedule payouts
  - [ ] Mark as completed
  
- [ ] **Analytics**
  - [ ] Revenue charts display
  - [ ] Booking statistics accurate
  - [ ] Top vehicles shown
  - [ ] Data exports work (if implemented)
  
- [ ] **Security**
  - [ ] Security alerts visible
  - [ ] Can mark as resolved
  - [ ] Severity levels work
  
- [ ] **Audit Logs**
  - [ ] All actions logged
  - [ ] IP addresses captured
  - [ ] Timestamps accurate
  - [ ] Searchable/filterable
  
- [ ] **CMS**
  - [ ] View all pages
  - [ ] Edit page content
  - [ ] Save changes
  - [ ] Changes reflect on site
  
- [ ] **Pending Changes**
  - [ ] Owner changes visible
  - [ ] Can approve changes
  - [ ] Can reject with reason
  - [ ] Owner notified
  
- [ ] **Contact Submissions**
  - [ ] View submissions
  - [ ] Mark as read
  - [ ] Respond to contacts
  - [ ] Archive old submissions

### Owner Dashboard Testing
- [ ] **Dashboard Access**
  - [ ] Dashboard loads correctly
  - [ ] Earnings statistics accurate
  - [ ] Recent bookings displayed
  - [ ] Quick access links work
  
- [ ] **Vehicle Listings**
  - [ ] View own vehicles
  - [ ] Add new vehicle
  - [ ] Upload images
  - [ ] Edit vehicle (creates pending change)
  - [ ] Vehicle status shown
  
- [ ] **Bookings**
  - [ ] View all bookings for owned vehicles
  - [ ] Filter by status
  - [ ] View booking details
  - [ ] Earnings displayed correctly
  
- [ ] **Calendar**
  - [ ] View calendar
  - [ ] Bookings shown
  - [ ] Availability visible
  - [ ] Events display correctly
  
- [ ] **Analytics**
  - [ ] Monthly earnings chart
  - [ ] Booking trends
  - [ ] Vehicle performance
  
- [ ] **Payouts**
  - [ ] View payout history
  - [ ] Pending payouts visible
  - [ ] Completed payouts listed
  - [ ] Amounts correct
  
- [ ] **Reviews**
  - [ ] View customer reviews
  - [ ] Ratings display correctly
  - [ ] Can respond to reviews (if implemented)
  
- [ ] **Messages**
  - [ ] View messages
  - [ ] Send messages
  - [ ] Mark as read
  
- [ ] **Pending Changes**
  - [ ] View own pending changes
  - [ ] Status updates visible
  - [ ] Rejection reasons shown

### Customer Dashboard Testing
- [ ] **Dashboard Access**
  - [ ] Dashboard loads correctly
  - [ ] Booking statistics shown
  - [ ] Upcoming bookings displayed
  
- [ ] **Bookings/Hires**
  - [ ] View all bookings
  - [ ] Past bookings shown
  - [ ] Upcoming bookings highlighted
  - [ ] Booking details accessible
  
- [ ] **Profile**
  - [ ] View profile
  - [ ] Edit profile information
  - [ ] Save changes
  - [ ] Changes reflected
  
- [ ] **Browse Vehicles**
  - [ ] Easy access to vehicle listings
  - [ ] Can make bookings

### Public Pages Testing
- [ ] **Homepage**
  - [ ] Loads correctly
  - [ ] Featured vehicles shown
  - [ ] Images display
  - [ ] Call-to-action buttons work
  - [ ] Navigation functional
  
- [ ] **Vehicle Listings**
  - [ ] All approved vehicles shown
  - [ ] Images display correctly
  - [ ] Prices shown
  - [ ] Categories visible
  - [ ] "View Details" links work
  
- [ ] **Vehicle Detail Page**
  - [ ] Vehicle information complete
  - [ ] Images gallery works
  - [ ] Booking form displays
  - [ ] Reviews shown
  - [ ] Owner details hidden (privacy)
  
- [ ] **Terms of Service**
  - [ ] Page loads
  - [ ] Full content visible
  - [ ] Proper formatting
  - [ ] Australian English
  
- [ ] **Privacy Policy**
  - [ ] Page loads
  - [ ] Full content visible
  - [ ] Australian compliance
  - [ ] Contact details correct
  
- [ ] **FAQ**
  - [ ] Page loads
  - [ ] Questions/answers visible
  - [ ] Helpful content
  
- [ ] **Contact Page**
  - [ ] Form displays
  - [ ] All fields present
  - [ ] Validation works
  - [ ] Submission successful
  - [ ] Confirmation message shown

### Booking Flow Testing
- [ ] **Create Booking**
  - [ ] Select vehicle
  - [ ] Choose date (future only)
  - [ ] Select time
  - [ ] Set duration (minimum enforced)
  - [ ] Enter pickup location
  - [ ] Select event type
  - [ ] Add special requirements
  - [ ] Submit booking
  
- [ ] **Booking Confirmation**
  - [ ] Booking reference generated
  - [ ] Status set to "pending"
  - [ ] Owner notified
  - [ ] Calendar event created
  - [ ] Customer can view booking
  
- [ ] **Payment Processing**
  - [ ] Payment form displays
  - [ ] Card validation works
  - [ ] Payment processes
  - [ ] Transaction recorded
  - [ ] Status updated to "paid"
  - [ ] Booking confirmed
  - [ ] Payout scheduled for owner
  
- [ ] **Booking Completion**
  - [ ] Status can be updated
  - [ ] Commission calculated
  - [ ] Owner payout calculated
  - [ ] Review request sent (if implemented)

### File Upload Testing
- [ ] **Vehicle Images**
  - [ ] Upload single image
  - [ ] Upload multiple images
  - [ ] File size limit enforced
  - [ ] File type validation works
  - [ ] Primary image selection
  - [ ] Images display correctly
  
- [ ] **Security**
  - [ ] Executable files rejected
  - [ ] Large files rejected
  - [ ] Invalid types rejected
  - [ ] Unique file naming

### Email Testing
- [ ] **Email Queue**
  - [ ] Emails added to queue
  - [ ] Queue processes correctly
  - [ ] Failed emails logged
  
- [ ] **Notification Emails**
  - [ ] Registration confirmation
  - [ ] Account approval
  - [ ] Booking confirmation
  - [ ] Payment receipt
  - [ ] Payout notification
  - [ ] New booking (owner)
  
- [ ] **Email Content**
  - [ ] Proper formatting
  - [ ] All placeholders filled
  - [ ] Links work correctly
  - [ ] Company branding

### Security Testing
- [ ] **SQL Injection**
  - [ ] Login form (should fail)
  - [ ] Search fields (should be safe)
  - [ ] URL parameters (should be safe)
  
- [ ] **XSS (Cross-Site Scripting)**
  - [ ] Input fields (should be escaped)
  - [ ] Comments/reviews (should be safe)
  - [ ] Profile information (should be safe)
  
- [ ] **CSRF Protection**
  - [ ] Forms have tokens
  - [ ] Invalid tokens rejected
  - [ ] Token validation works
  
- [ ] **Authentication**
  - [ ] Protected pages require login
  - [ ] Role-based access enforced
  - [ ] Cannot access other roles' pages
  
- [ ] **File Upload Security**
  - [ ] PHP files rejected
  - [ ] Scripts cannot execute
  - [ ] Proper file storage
  
- [ ] **Session Security**
  - [ ] Sessions expire appropriately
  - [ ] Logout clears session
  - [ ] Session hijacking prevented

### Performance Testing
- [ ] **Page Load Times**
  - [ ] Homepage < 2 seconds
  - [ ] Dashboard < 3 seconds
  - [ ] Vehicle listings < 3 seconds
  
- [ ] **Database Queries**
  - [ ] No N+1 queries
  - [ ] Indexes working
  - [ ] Query performance acceptable
  
- [ ] **Images**
  - [ ] Images optimised
  - [ ] Reasonable file sizes
  - [ ] Loading performance good

### Responsive Design Testing
- [ ] **Mobile (320px - 768px)**
  - [ ] Navigation collapses
  - [ ] Forms usable
  - [ ] Images scale correctly
  - [ ] Text readable
  
- [ ] **Tablet (768px - 1024px)**
  - [ ] Layout adjusts
  - [ ] Sidebars adapt
  - [ ] Tables scroll/adapt
  
- [ ] **Desktop (1024px+)**
  - [ ] Full layout displays
  - [ ] All features accessible
  - [ ] Optimal use of space

### Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

### Accessibility Testing
- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] Colour contrast adequate
- [ ] Form labels present
- [ ] Alt text on images
- [ ] ARIA labels where needed

### Australian English Verification
- [ ] Colour (not color)
- [ ] Organisation (not organization)
- [ ] Licence (not license)
- [ ] Programme (not program - in context)
- [ ] Cheque (not check - in payment context)
- [ ] Centre (not center)

### Data Validation
- [ ] Email format validation
- [ ] Phone number format
- [ ] Date validation
- [ ] Number validation
- [ ] Required fields enforced
- [ ] Maximum lengths enforced

### Error Handling
- [ ] 404 pages work
- [ ] Error messages display
- [ ] Validation errors shown
- [ ] User-friendly messages
- [ ] Errors logged properly

## Production Testing

### Post-Deployment Checks
- [ ] Site accessible via domain
- [ ] HTTPS working
- [ ] SSL certificate valid
- [ ] Database connections work
- [ ] File uploads work
- [ ] Emails send correctly
- [ ] All features functional

### Monitoring
- [ ] Error logs checked
- [ ] No PHP errors
- [ ] No database errors
- [ ] Performance acceptable
- [ ] Uptime monitoring active

## Sign-Off

**Tested By**: _________________  
**Date**: _________________  
**Environment**: _________________  
**Result**: PASS / FAIL  

**Notes**:
_____________________________________
_____________________________________
_____________________________________

© 2025 Elite Car Hire - Testing Checklist v1.0
