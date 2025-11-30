# Terms of Service & FAQ Pages - Deployment Guide

## Overview

This guide provides instructions for deploying comprehensive Terms of Service and FAQ pages to your Elite Car Hire website.

## What's Included

### Terms of Service Page
Comprehensive legal terms covering:
- Platform overview and role as peer-to-peer marketplace
- User account requirements (Customer & Owner)
- Complete booking workflow and approval process
- Pricing structure and 15% commission
- Stripe payment processing
- 50% cancellation policy
- Owner and Customer responsibilities
- Insurance requirements
- Driver requirements (age 25+, full license)
- Damage and liability procedures
- Dispute resolution
- Account suspension and termination
- Limitation of liability
- Privacy and data protection
- Intellectual property
- Australian Consumer Law compliance
- Contact information

### FAQ Page
Detailed answers organized by user type:

**For Customers:**
- Booking process step-by-step
- Payment methods and timing
- Minimum/maximum booking durations (4 hours - 30 days)
- Advance booking window (90 days)
- Additional charges approval workflow
- Vehicle categories and types
- Cancellation policy (50% fee)
- Driver requirements (age 25+, full license)
- Insurance coverage
- Accident/damage procedures
- Booking modifications

**For Vehicle Owners:**
- Listing creation process
- Vehicle and insurance requirements
- Pricing guidance ($200-$3,000+ per hour)
- Booking management
- Payment schedule and 15% commission breakdown
- Availability calendar management
- State-specific licensing requirements:
  - NSW: T-Code requirements
  - VIC: Driver Accreditation & CPV registration
  - QLD: DA & BHSL requirements
  - SA/WA/TAS/NT: Overview
  - ACT: H-condition license & WWVP registration
- Club registration restrictions
- Insurance requirements per state

**General Information:**
- Australia-wide coverage (all states/territories)
- Support contact details
- Incident and dispute reporting
- Vehicle recommendations for different events
- Photoshoot/film production bookings
- Payment security (Stripe PCI-DSS Level 1)
- Privacy and data usage
- Account deletion process

## Key Features

### Original Content
- **Zero plagiarism** - All content written specifically for Elite Car Hire
- Tailored to your platform's unique workflows
- References actual system features (Stripe, booking approval, etc.)

### Compliance
- Australian Consumer Law compliant
- Privacy Act 1988 compliant
- State-specific driver licensing information
- Clear liability limitations

### User Experience
- Clean, professional design with CSS styling
- FAQ search functionality with JavaScript
- Accordion-style FAQ items for easy navigation
- Mobile-responsive layout
- Clear section organization by user type
- Internal linking to contact form and privacy policy

## Files to Deploy

### Database Migration
```
database/insert_terms_and_faq_content.sql
```

This single SQL file contains INSERT statements for both pages in the `cms_pages` table.

## Deployment Instructions

### Step 1: Backup Database

**CRITICAL: Create a backup before running any SQL migrations**

1. Log into **cPanel** → **phpMyAdmin**
2. Select your database
3. Click **"Export"** tab
4. Choose **"Quick"** export method
5. Click **"Go"** and save the `.sql` file

### Step 2: Run the SQL Migration

**Option A: Via cPanel phpMyAdmin (Recommended)**

1. Log into **cPanel** → **phpMyAdmin**
2. Select your Elite Car Hire database
3. Click **"SQL"** tab at the top
4. Open the file: `database/insert_terms_and_faq_content.sql`
5. Copy the ENTIRE contents
6. Paste into the SQL query box
7. Click **"Go"** to execute

**Option B: Via MySQL Command Line**

```bash
mysql -u your_username -p your_database < database/insert_terms_and_faq_content.sql
```

### Step 3: Verify Pages Were Created

In phpMyAdmin, run this query:

```sql
SELECT page_key, title, status FROM cms_pages WHERE page_key IN ('terms', 'faq');
```

You should see two rows:
- `terms` | Terms of Service | published
- `faq` | Frequently Asked Questions | published

### Step 4: Test the Pages

Visit these URLs on your website:

- **Terms of Service:** `https://yoursite.com/terms`
- **FAQ:** `https://yoursite.com/faq`

Both pages should display with full content and styling.

### Step 5: Link from Footer/Navigation

Add links to these pages in your website footer or main navigation:

```html
<a href="/terms">Terms of Service</a>
<a href="/faq">FAQ</a>
```

These routes are already configured in `public/index.php` via the `PublicController`:
- `/terms` → `PublicController@terms`
- `/faq` → `PublicController@faq`

## Content Updates

To update content in the future:

### Option 1: Via Admin CMS (Recommended)
If you have a CMS admin interface for managing pages, edit the content there.

### Option 2: Direct Database Edit

1. Log into phpMyAdmin
2. Browse `cms_pages` table
3. Find the row where `page_key = 'terms'` or `page_key = 'faq'`
4. Click **"Edit"**
5. Modify the `content` field (HTML)
6. Click **"Go"** to save

**Note:** The `content` field contains HTML with inline CSS. Maintain the structure when editing.

## Customization Tips

### Updating Contact Information

Search for and replace these placeholders in the SQL file before deployment:

- **Email:** `support@elitecarhire.com.au` → Your actual support email
- **Domain:** Update any references to `yoursite.com` with your actual domain

### Adjusting Policies

If your policies differ from the defaults:

- **Commission Rate:** Currently 15% - search and replace if different
- **Cancellation Fee:** Currently 50% - search and replace if different
- **Minimum Booking:** Currently 4 hours - search and replace if different
- **Maximum Booking:** Currently 30 days - search and replace if different
- **Advance Booking:** Currently 90 days - search and replace if different

### Adding Your Branding

The CSS uses CSS variables:
- `var(--primary-gold, #D4AF37)` - Your primary brand color
- `var(--dark-charcoal, #2C3E50)` - Dark text color
- `var(--dark-gray, #555)` - Secondary text color

These should match your existing site's CSS variables. If not, update the fallback hex colors.

## SEO Considerations

Both pages include:
- **Meta Descriptions:** Optimized for search engines
- **Semantic HTML:** Proper heading hierarchy (H1, H2, H3, H4)
- **Keywords:** Naturally integrated throughout content
- **Internal Links:** Cross-linking to other pages (/contact, /privacy)

Consider:
- Adding these pages to your XML sitemap
- Linking to Terms from booking confirmation pages
- Linking to FAQ from customer/owner dashboards
- Including FAQ schema markup for enhanced search results

## Legal Review

**IMPORTANT:** While these terms were written based on industry best practices and Australian law:

1. **Consult a Lawyer:** Have an Australian lawyer review the Terms of Service before going live
2. **Insurance Compliance:** Verify insurance requirements match your state regulations
3. **State Licensing:** Confirm state-specific driver licensing information is current
4. **Platform Changes:** Update terms when you add new features or change policies
5. **Version Control:** Consider adding a version number and "Last Updated" date

## Maintenance Schedule

**Quarterly Review:**
- Check for changes in Australian Consumer Law
- Verify state licensing requirements haven't changed
- Update any outdated pricing examples
- Review competitor terms for industry changes

**Annual Review:**
- Full legal review with lawyer
- Update insurance requirements if regulations change
- Refresh FAQ based on common support tickets
- Add new FAQs for new platform features

## Support Resources

### Australian Legal Resources
- **Australian Consumer Law:** https://www.accc.gov.au/consumers/consumer-rights-guarantees/consumer-guarantees
- **Privacy Act 1988:** https://www.oaic.gov.au/privacy/the-privacy-act
- **Australian Contract Law:** https://www.ag.gov.au/

### State Transport Authorities
- **NSW:** https://www.service.nsw.gov.au/
- **VIC:** https://www.cpv.vic.gov.au/
- **QLD:** https://www.tmr.qld.gov.au/
- **SA:** https://www.sa.gov.au/
- **WA:** https://www.wa.gov.au/
- **TAS:** https://www.transport.tas.gov.au/
- **NT:** https://nt.gov.au/driving/commercial
- **ACT:** https://www.accesscanberra.act.gov.au/

## Troubleshooting

### Issue: SQL Import Fails

**Error: "Unknown column 'description'"**
- The cms_pages table doesn't have a description column
- This is already addressed in the migration - should not occur

**Error: "Duplicate entry 'terms'"**
- Pages already exist in database
- Use `ON DUPLICATE KEY UPDATE` (already in the SQL)
- Or delete existing rows first: `DELETE FROM cms_pages WHERE page_key IN ('terms', 'faq');`

### Issue: Pages Display with Broken Styling

**Check:**
1. CSS is included in the content field
2. Your theme doesn't override the styles
3. CSS variables are defined in your main stylesheet
4. Browser cache - force refresh with Ctrl+F5

### Issue: FAQ Search Not Working

**Check:**
1. JavaScript is enabled in browser
2. No JavaScript errors in console (F12 → Console)
3. jQuery conflicts (this uses vanilla JavaScript, should be compatible)

### Issue: FAQ Accordion Not Expanding

**Check:**
1. JavaScript loaded correctly
2. No conflicts with other scripts
3. CSS for `.open` class is present
4. Try clicking question text, not answer area

## Post-Deployment Checklist

After deploying:

- [ ] Verified both pages load correctly
- [ ] Tested FAQ search functionality
- [ ] Tested FAQ accordion expand/collapse
- [ ] Checked mobile responsiveness
- [ ] Added links in footer/navigation
- [ ] Updated sitemap.xml
- [ ] Tested internal links (contact, privacy)
- [ ] Reviewed content for typos
- [ ] Confirmed contact email is correct
- [ ] Checked all state-specific licensing information
- [ ] Scheduled legal review
- [ ] Informed team of new pages
- [ ] Added to customer onboarding/welcome emails

## Contact for Support

If you encounter issues during deployment:

1. Check PHP error logs: `/storage/logs/error.log`
2. Check database query logs in phpMyAdmin
3. Review this guide's Troubleshooting section
4. Contact your development team or system administrator

---

**Last Updated:** November 2025
**Version:** 1.0

These comprehensive Terms of Service and FAQ pages provide legal protection for your platform while giving users clear, detailed information about how Elite Car Hire works. They reflect your actual booking workflows, payment processes, and Australian regulatory requirements.
