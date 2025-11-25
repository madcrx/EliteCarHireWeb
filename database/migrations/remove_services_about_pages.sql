-- Remove Services and About Us pages from CMS
-- Run this on your live database

DELETE FROM cms_pages WHERE page_key IN ('services', 'about');
