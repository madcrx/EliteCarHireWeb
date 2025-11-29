# How to Apply the Booking Approval Workflow Migration

## Step 1: Access Your Database

You need to connect to your MySQL database. Choose one of these methods:

### Option A: Using cPanel phpMyAdmin
1. Log into your cPanel
2. Click on "phpMyAdmin"
3. Select your database (elite_car_hire)
4. Click on "SQL" tab
5. Paste the SQL below and click "Go"

### Option B: Using MySQL command line
```bash
mysql -u your_username -p elite_car_hire
```
Then paste the SQL commands.

### Option C: Using a database management tool
Use tools like MySQL Workbench, DBeaver, or TablePlus and execute the SQL.

## Step 2: Run This SQL

```sql
-- Add additional_charges_reason field to bookings table
ALTER TABLE bookings
ADD COLUMN additional_charges_reason TEXT NULL AFTER additional_charges;

-- Update status ENUM to include 'awaiting_approval' status
ALTER TABLE bookings
MODIFY COLUMN status ENUM('pending', 'awaiting_approval', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';
```

## Step 3: Verify Migration Was Successful

Run this query to check the new column exists:

```sql
DESCRIBE bookings;
```

You should see:
- A column named `additional_charges_reason` of type TEXT
- The `status` column should include 'awaiting_approval' in its ENUM values

## Step 4: Test the Feature

After migration:

### Test as Owner:
1. Go to Owner Dashboard → Bookings
2. Click "Confirm Booking" on a pending booking
3. Add a value in "Additional Charges" field
4. **The "Reason" field should appear automatically**
5. Fill in the reason (required)
6. Click "Confirm Booking"
7. Check that the booking status is "awaiting_approval"

### Test as Customer:
1. Go to Customer Dashboard → My Bookings
2. Click the orange "Needs Approval" button
3. You should see the booking with a yellow/orange background
4. Click "Review & Approve"
5. Review the reason and price breakdown
6. Click "Approve & Proceed to Payment"
7. Booking status should change to "confirmed"

## Troubleshooting

If you get an error like:
- **"Column 'additional_charges_reason' not found"** → The migration hasn't been applied
- **"Invalid value for status"** → The status ENUM hasn't been updated
- **"Reason field doesn't appear"** → Check browser console for JavaScript errors

## What This Migration Does

1. **Adds `additional_charges_reason` column**: Stores the owner's explanation for extra charges
2. **Adds 'awaiting_approval' status**: New booking status for when customer approval is needed

## Files Already Updated (No Action Needed)

✅ OwnerController.php - Accepts and saves reason
✅ CustomerController.php - Approval/rejection methods
✅ Owner bookings view - Reason field in confirmation modal
✅ Customer bookings view - Approval modal
✅ Routes added to index.php

**Once you run the SQL migration, everything will work!**
