# Email Queue SQL Queries

Use these SQL queries in phpMyAdmin to monitor and troubleshoot the email queue.

## Monitoring Queries

### 1. Check All Pending Emails

```sql
SELECT
    id,
    to_email,
    subject,
    status,
    attempts,
    error_message,
    created_at,
    last_attempt
FROM email_queue
WHERE status = 'pending'
ORDER BY created_at DESC;
```

### 2. Check Recent Email Activity (All Statuses)

```sql
SELECT
    id,
    to_email,
    subject,
    status,
    attempts,
    error_message,
    created_at,
    sent_at
FROM email_queue
ORDER BY created_at DESC
LIMIT 20;
```

### 3. Count Emails by Status

```sql
SELECT
    status,
    COUNT(*) as count
FROM email_queue
GROUP BY status;
```

### 4. Check Failed Emails (with error messages)

```sql
SELECT
    id,
    to_email,
    subject,
    attempts,
    error_message,
    created_at,
    last_attempt
FROM email_queue
WHERE status = 'failed'
ORDER BY created_at DESC;
```

### 5. Check Emails Stuck in Pending (older than 10 minutes)

```sql
SELECT
    id,
    to_email,
    subject,
    attempts,
    error_message,
    created_at,
    TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_old
FROM email_queue
WHERE status = 'pending'
  AND created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
ORDER BY created_at ASC;
```

This query shows emails that should have been sent by now if the cron job is working.

### 6. Check Recently Sent Emails (Last Hour)

```sql
SELECT
    id,
    to_email,
    subject,
    sent_at,
    TIMESTAMPDIFF(SECOND, created_at, sent_at) as seconds_to_send
FROM email_queue
WHERE status = 'sent'
  AND sent_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY sent_at DESC;
```

## Diagnostic Queries

### 7. Check Retry Attempts for Pending Emails

```sql
SELECT
    attempts,
    COUNT(*) as count
FROM email_queue
WHERE status = 'pending'
GROUP BY attempts
ORDER BY attempts;
```

If you see emails with 2+ attempts but still pending, they're failing repeatedly.

### 8. Find Emails Never Attempted

```sql
SELECT
    id,
    to_email,
    subject,
    created_at,
    TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_waiting
FROM email_queue
WHERE status = 'pending'
  AND attempts = 0
ORDER BY created_at ASC;
```

If emails have been waiting for > 5 minutes with 0 attempts, the cron job is NOT running.

### 9. Check Most Recent Email Queue Activity

```sql
SELECT
    id,
    to_email,
    subject,
    status,
    created_at,
    last_attempt,
    sent_at
FROM email_queue
ORDER BY
    COALESCE(sent_at, last_attempt, created_at) DESC
LIMIT 10;
```

Shows the most recent activity across all emails (created, attempted, or sent).

### 10. Check Email Processing Rate

```sql
SELECT
    DATE_FORMAT(sent_at, '%Y-%m-%d %H:%i') as minute,
    COUNT(*) as emails_sent
FROM email_queue
WHERE status = 'sent'
  AND sent_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY DATE_FORMAT(sent_at, '%Y-%m-%d %H:%i')
ORDER BY minute DESC;
```

Shows how many emails are being sent per minute.

## Cleanup Queries

### 11. Delete Old Sent Emails (older than 30 days)

```sql
DELETE FROM email_queue
WHERE status = 'sent'
  AND sent_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### 12. Delete Old Failed Emails (older than 30 days)

```sql
DELETE FROM email_queue
WHERE status = 'failed'
  AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## Testing Queries

### 13. Create a Test Email

```sql
INSERT INTO email_queue (
    to_email,
    to_name,
    subject,
    body_html,
    status,
    attempts,
    created_at
) VALUES (
    'admin@elitecarhire.au',
    'Admin',
    'Test Email',
    '<p>This is a test email to verify the queue processor is working.</p>',
    'pending',
    0,
    NOW()
);
```

After inserting, wait 1-2 minutes and check if status changes to 'sent'.

### 14. Reset a Failed Email to Retry

```sql
UPDATE email_queue
SET
    status = 'pending',
    attempts = 0,
    error_message = NULL,
    last_attempt = NULL
WHERE id = ?;  -- Replace ? with the email ID
```

Replace `?` with the actual email ID you want to retry.

## What to Look For

### ✅ Good Signs (System Working):

- **Pending emails** have status changed to 'sent' within 1-2 minutes
- **Attempts** increase over time (0 → 1 → 2)
- **Last attempt** timestamp updates every minute
- **Recently sent emails** appear in the sent list
- **Emails per minute** shows consistent processing

### ❌ Bad Signs (System NOT Working):

- **Pending emails** older than 5 minutes with 0 attempts = Cron NOT running
- **Attempts** stay at 0 for old emails = Cron NOT executing script
- **All attempts** at 3 with status still 'pending' = Should be 'failed'
- **No sent emails** in last hour = System completely broken
- **Last attempt** timestamp doesn't update = Cron NOT running

## Real-Time Monitoring

Run this query every 30 seconds to watch emails being processed:

```sql
SELECT
    NOW() as current_time,
    (SELECT COUNT(*) FROM email_queue WHERE status = 'pending') as pending,
    (SELECT COUNT(*) FROM email_queue WHERE status = 'sent' AND sent_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as sent_last_5_min,
    (SELECT COUNT(*) FROM email_queue WHERE status = 'failed') as failed,
    (SELECT MAX(sent_at) FROM email_queue WHERE status = 'sent') as last_email_sent,
    (SELECT MAX(last_attempt) FROM email_queue WHERE status = 'pending') as last_attempt;
```

If `last_email_sent` or `last_attempt` updates every minute, the cron job IS running.

## Export Results

To export results to CSV:

1. Run your query in phpMyAdmin
2. Click "Export" at the bottom
3. Choose "CSV" format
4. Save the file

This is useful for analyzing trends or sharing with support.

---

## Quick Diagnostic Workflow

1. **Check for pending emails** (Query #1)
   - If none: System is working
   - If any: Continue to step 2

2. **Check stuck pending emails** (Query #5)
   - If emails > 10 minutes old: Cron NOT running
   - If emails < 5 minutes old: Wait and re-check

3. **Check attempt counts** (Query #7)
   - If attempts = 0 for old emails: Cron NOT running
   - If attempts = 1-2: Script running but emails failing

4. **Check error messages** (Query #4)
   - Read error messages to identify the problem
   - Common errors: SMTP connection failed, PHPMailer not found

5. **Check sent emails** (Query #6)
   - If recent sent emails exist: System WAS working
   - If no sent emails: System never worked or stopped

**Based on results, refer to:**
- CRON_QUICK_FIX.md - If cron not running
- EMAIL_DIAGNOSIS.md - If SMTP/PHPMailer issues
- CRON_TROUBLESHOOTING.md - For detailed debugging
