-- Domain Migration: elitecarhire.com.au → elitecarhire.au
-- Run this SQL file ONLY if you have an existing database with old domain references
-- For fresh installations, this is not needed - just deploy the updated files

-- Update site URL in settings table
UPDATE settings
SET setting_value = 'https://elitecarhire.au'
WHERE setting_key = 'site_url'
AND setting_value LIKE '%elitecarhire.com.au%';

-- Update from email in settings table (if exists)
UPDATE settings
SET setting_value = 'support@elitecarhire.au'
WHERE setting_key IN ('from_email', 'email_from_address', 'support_email')
AND setting_value LIKE '%elitecarhire.com.au%';

-- Update Terms of Service content
UPDATE cms_pages
SET content = REPLACE(content, 'support@elitecarhire.com.au', 'support@elitecarhire.au'),
    updated_at = CURRENT_TIMESTAMP
WHERE page_key = 'terms'
AND content LIKE '%elitecarhire.com.au%';

-- Update FAQ content
UPDATE cms_pages
SET content = REPLACE(content, 'support@elitecarhire.com.au', 'support@elitecarhire.au'),
    updated_at = CURRENT_TIMESTAMP
WHERE page_key = 'faq'
AND content LIKE '%elitecarhire.com.au%';

-- Update any other CMS pages that might reference the old domain
UPDATE cms_pages
SET content = REPLACE(content, 'elitecarhire.com.au', 'elitecarhire.au'),
    updated_at = CURRENT_TIMESTAMP
WHERE content LIKE '%elitecarhire.com.au%';

-- Note: Email templates are built in code (app/helpers/booking_emails.php), not stored in database
-- No database update needed for email templates

-- Verify changes
SELECT 'Settings Table' AS table_name, setting_key, setting_value
FROM settings
WHERE setting_key IN ('site_url', 'from_email', 'email_from_address', 'support_email')
UNION ALL
SELECT 'CMS Pages' AS table_name, page_key,
       CASE WHEN content LIKE '%elitecarhire.au%' THEN 'Updated to .au' ELSE 'No changes' END
FROM cms_pages
WHERE page_key IN ('terms', 'faq', 'privacy', 'about');
