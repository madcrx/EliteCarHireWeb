# Booking Approval Workflow Implementation

## Overview
This update implements a new booking confirmation workflow where owners can add extra charges (with a reason), which must be approved by the customer before proceeding to payment.

## Database Migration Required

**IMPORTANT:** You must run the SQL migration before using this feature.

### How to Apply Migration

1. Connect to your MySQL database
2. Run the SQL file: `database/add_booking_approval_workflow.sql`

Or execute these commands directly:

```sql
-- Add additional_charges_reason field to bookings table
ALTER TABLE bookings
ADD COLUMN additional_charges_reason TEXT NULL AFTER additional_charges;

-- Update status ENUM to include 'awaiting_approval' status
ALTER TABLE bookings
MODIFY COLUMN status ENUM('pending', 'awaiting_approval', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';
```

## New Workflow

### For Owners:

1. **Navigate to Owner Dashboard → Bookings**
2. **Click "Confirm Booking" for a pending booking**
3. **In the confirmation modal:**
   - View base booking amount
   - (Optional) Add additional charges for excess travel
   - **If adding charges:** Must provide a reason (required field)
   - Review final total amount
4. **Click "Confirm Booking & Notify Customer"**

**What happens:**
- **No additional charges:** Booking status changes to "confirmed" → Customer proceeds to payment
- **With additional charges:** Booking status changes to "awaiting_approval" → Customer must approve first

### For Customers:

1. **Navigate to Customer Dashboard → My Bookings**
2. **Filter by "Needs Approval"** (orange button) to see bookings awaiting approval
3. **Click "Review & Approve"** on the booking
4. **In the approval modal, review:**
   - Original booking amount
   - Additional charges added
   - Reason for additional charges
   - New total amount
5. **Choose action:**
   - **Approve & Proceed to Payment:** Booking status → "confirmed" → Redirected to payment
   - **Reject Changes:** Booking is cancelled

## Visual Indicators

### Customer Bookings List:
- **Awaiting Approval bookings** are highlighted with a yellow/orange background
- The "Needs Approval" filter button is orange for quick identification
- Amount column shows base + extra charges breakdown for pending approvals

## Features Implemented

### Files Created:
- `database/add_booking_approval_workflow.sql` - Database migration

### Files Modified:
1. **app/views/owner/bookings.php**
   - Updated confirmation modal to include reason field
   - Added JavaScript validation
   - Dynamic workflow steps based on charges

2. **app/controllers/OwnerController.php**
   - Modified `confirmBooking()` method to:
     - Accept and validate `additional_charges_reason`
     - Set status to 'awaiting_approval' if charges > 0
     - Set status to 'confirmed' if no additional charges
     - Send appropriate notifications to customer

3. **app/views/customer/bookings.php**
   - Added "Needs Approval" filter button
   - Updated table to show awaiting_approval status with special styling
   - Added approval modal with:
     - Price breakdown
     - Reason display
     - Approve/Reject actions
   - JavaScript functions for modal management

4. **app/controllers/CustomerController.php**
   - Added `approveBooking()` method
   - Added `rejectBooking()` method
   - Both methods include:
     - Security validation
     - Status verification
     - Database updates
     - Audit logging
     - Notifications to owner

5. **public/index.php**
   - Added route: `POST /customer/bookings/approve`
   - Added route: `POST /customer/bookings/reject`

## Security Features

- CSRF token validation on all forms
- User authentication and authorization checks
- Booking ownership verification
- Status validation before approval/rejection
- Audit logging for all actions

## Notifications

### To Customer:
- **With additional charges:** "Booking Updated - Approval Required" with full details
- **No additional charges:** "Booking Confirmed - Payment Required"

### To Owner:
- **Customer approves:** "Customer Approved Booking Changes"
- **Customer rejects:** "Customer Rejected Booking Changes"

## Status Flow

```
Pending
   ↓ (Owner confirms)
   ├→ No extra charges → Confirmed → Payment → In Progress → Completed
   │
   └→ Has extra charges → Awaiting Approval
                               ↓
                        Customer Reviews
                               ↓
                ┌──────────────┴──────────────┐
                ↓                             ↓
            Approved                      Rejected
                ↓                             ↓
            Confirmed                    Cancelled
                ↓
            Payment
                ↓
          In Progress
                ↓
           Completed
```

## Testing Checklist

- [ ] Database migration applied successfully
- [ ] Owner can confirm booking without additional charges (direct to "confirmed")
- [ ] Owner can add additional charges with reason
- [ ] Reason field validation works (required when charges > 0)
- [ ] Customer sees "Needs Approval" filter and bookings
- [ ] Customer can open approval modal
- [ ] Customer can approve booking → status changes to "confirmed"
- [ ] Customer can reject booking → status changes to "cancelled"
- [ ] Notifications sent to both parties
- [ ] Audit logs recorded for all actions
