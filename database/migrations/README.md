# Database Migrations Guide

## Migration Order

When setting up or updating your database, run the migration files in this order:

### 1. **add_timestamps_to_payments.sql** (Run FIRST if you have existing database)
   - Adds `created_at` and `updated_at` columns to the `payments` table
   - Required before running stripe_integration.sql
   - Safe to run multiple times (uses IF NOT EXISTS)

### 2. **add_created_at_to_settings.sql** (Optional, only if you get errors)
   - Adds `created_at` column to the `settings` table
   - Only needed if your settings table is missing this column
   - Safe to run multiple times

### 3. **stripe_integration.sql**
   - Adds Stripe Connect support
   - Creates webhook tracking tables
   - Requires payments table to have timestamp columns
   - Now includes safety checks - safe to run multiple times
   - Automatically skips adding columns/indexes that already exist

### 4. **configure_stripe_connect_settings.sql** (Run after stripe_integration.sql)
   - Configures Stripe Connect settings in the database
   - Sets up webhook secret, client ID, and redirect URLs
   - **IMPORTANT:** Edit this file first to add your actual Stripe credentials
   - Safe to run multiple times (uses ON DUPLICATE KEY UPDATE)

### 5. Other migrations (run as needed):
   - add_cancellation_fee_to_bookings.sql
   - update_terms_privacy.sql
   - remove_services_about_pages.sql
   - create_email_reminders_table.sql
   - create_action_tokens_table.sql

## How to Run Migrations

### Via phpMyAdmin:
1. Log into phpMyAdmin
2. Select your database
3. Click on "Import" tab
4. Choose the SQL file
5. Click "Go"

### Via MySQL Command Line:
```bash
mysql -u your_username -p your_database_name < database/migrations/add_timestamps_to_payments.sql
mysql -u your_username -p your_database_name < database/migrations/stripe_integration.sql
```

## Troubleshooting

### Error: "Unknown column 'created_at' in 'field list'"
**Possible causes:**
1. Missing `created_at` column in `payments` table → Run `add_timestamps_to_payments.sql`
2. Missing `created_at` column in `settings` table → Run `add_created_at_to_settings.sql`
3. Using old SQL that references non-existent columns → Use the updated migration files from this repository

### Error: "Duplicate column name 'stripe_account_id'"
**Solution:** The column already exists. The updated `stripe_integration.sql` now handles this automatically. Re-download and run the updated version, or simply continue - the column is already there.

### Error: "Table already exists"
**Solution:** The migration has already been run. You can skip it or drop the table first (⚠️ only if you're sure).

### Error: Foreign key constraint fails
**Solution:** Ensure all referenced tables exist before running migrations that create foreign keys.
