# Database Migrations Guide

## Migration Order

When setting up or updating your database, run the migration files in this order:

### 1. **add_timestamps_to_payments.sql** (Run FIRST if you have existing database)
   - Adds `created_at` and `updated_at` columns to the `payments` table
   - Required before running stripe_integration.sql
   - Safe to run multiple times (uses IF NOT EXISTS)

### 2. **stripe_integration.sql**
   - Adds Stripe Connect support
   - Creates webhook tracking tables
   - Requires payments table to have timestamp columns

### 3. Other migrations (run as needed):
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
**Solution:** Run `add_timestamps_to_payments.sql` first, then retry the failed migration.

### Error: "Table already exists"
**Solution:** The migration has already been run. You can skip it or drop the table first (⚠️ only if you're sure).

### Error: Foreign key constraint fails
**Solution:** Ensure all referenced tables exist before running migrations that create foreign keys.
