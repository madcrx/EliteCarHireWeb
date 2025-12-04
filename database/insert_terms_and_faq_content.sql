-- Elite Car Hire - Terms of Service and FAQ Pages
-- This migration adds comprehensive Terms and FAQ content to the cms_pages table

-- Insert Terms of Service
INSERT INTO cms_pages (page_key, title, content, meta_description, status)
VALUES ('terms', 'Terms of Service', '
<div class="terms-container">
    <h1>Terms of Service</h1>
    <p class="last-updated"><strong>Last Updated:</strong> November 2025</p>

    <p>Welcome to Elite Car Hire. These Terms of Service ("Terms") govern your use of our peer-to-peer luxury and classic vehicle rental platform. By accessing or using our services, you agree to be bound by these Terms.</p>

    <h2>1. Platform Overview</h2>
    <p>Elite Car Hire operates as a peer-to-peer marketplace connecting vehicle owners ("Owners") with customers ("Customers" or "Renters") seeking to hire luxury, exotic, classic muscle cars, and premium vehicles for special occasions, events, and experiences.</p>

    <p><strong>1.1 Our Role:</strong> Elite Car Hire acts as an intermediary platform facilitating bookings between Owners and Customers. We do not own the vehicles listed on our platform and are not a party to the rental agreement between Owners and Customers.</p>

    <p><strong>1.2 Service Coverage:</strong> Our services are available throughout Australia, with vehicles listed across NSW, VIC, QLD, SA, WA, TAS, NT, and ACT.</p>

    <h2>2. User Accounts and Registration</h2>

    <h3>2.1 Account Creation</h3>
    <p>To use Elite Car Hire services, you must:</p>
    <ul>
        <li>Be at least 25 years of age</li>
        <li>Hold a valid Australian driver\'s license (full license, not provisional)</li>
        <li>Provide accurate and complete registration information</li>
        <li>Maintain the security of your account credentials</li>
        <li>Immediately notify us of any unauthorized account use</li>
    </ul>

    <h3>2.2 Account Types</h3>
    <p><strong>Customer Accounts:</strong> Individuals seeking to rent vehicles for personal use.</p>
    <p><strong>Owner Accounts:</strong> Vehicle owners listing their luxury, exotic, or classic vehicles for hire. Owners must provide:</p>
    <ul>
        <li>Valid vehicle registration documentation</li>
        <li>Comprehensive insurance policy details</li>
        <li>Current insurance expiry date</li>
        <li>Vehicle specifications and condition details</li>
    </ul>

    <p><strong>2.3 Account Approval:</strong> All Owner accounts and vehicle listings are subject to admin approval before becoming active on the platform. Elite Car Hire reserves the right to reject any account or listing at our discretion.</p>

    <h2>3. Booking Process and Workflow</h2>

    <h3>3.1 Making a Booking</h3>
    <p>Customers can browse available vehicles and submit booking requests including:</p>
    <ul>
        <li>Desired booking date and time</li>
        <li>Rental duration (minimum 4 hours, maximum 30 days)</li>
        <li>Pickup and drop-off locations</li>
        <li>Up to three destination addresses</li>
        <li>Event type (wedding, corporate, private hire, etc.)</li>
        <li>Special requirements or requests</li>
    </ul>

    <p><strong>Advance Booking Window:</strong> Bookings must be made at least 24 hours in advance and no more than 90 days ahead of the desired date.</p>

    <h3>3.2 Booking Confirmation Workflow</h3>
    <p>The booking process follows these steps:</p>
    <ol>
        <li><strong>Booking Submission:</strong> Customer submits booking request with initial quote based on hourly rate × duration</li>
        <li><strong>Owner Review:</strong> Owner reviews booking and has the option to:
            <ul>
                <li>Confirm booking as-is (status changes to "Confirmed")</li>
                <li>Add additional charges (e.g., for excess travel distance beyond standard coverage) with detailed justification</li>
            </ul>
        </li>
        <li><strong>Customer Approval (if additional charges apply):</strong> If Owner adds extra charges:
            <ul>
                <li>Booking status becomes "Awaiting Approval"</li>
                <li>Customer receives notification with breakdown of original amount + additional charges + reason</li>
                <li>Customer must either approve the updated total or reject the changes</li>
                <li>Rejection results in booking cancellation</li>
            </ul>
        </li>
        <li><strong>Payment:</strong> Once confirmed (either directly or after customer approval), customer proceeds to secure payment</li>
        <li><strong>Booking Completion:</strong> After successful payment, booking status moves to "In Progress" then "Completed" after the rental period ends</li>
    </ol>

    <h3>3.3 Booking Reference</h3>
    <p>Each booking receives a unique reference number for tracking and communication purposes.</p>

    <h2>4. Pricing and Payments</h2>

    <h3>4.1 Rental Pricing</h3>
    <p>Vehicle rental rates are set by individual Owners and displayed as hourly rates. The base booking amount is calculated as:</p>
    <p><strong>Base Amount = Hourly Rate × Duration (in hours)</strong></p>

    <h3>4.2 Additional Charges</h3>
    <p>Owners may add additional charges for:</p>
    <ul>
        <li>Excess travel distance beyond standard coverage area</li>
        <li>Extended rental periods beyond initially quoted duration</li>
        <li>Special requests or services</li>
    </ul>
    <p><em>Important:</em> Any additional charges must be clearly justified and approved by the Customer before payment is processed.</p>

    <h3>4.3 Payment Processing</h3>
    <ul>
        <li><strong>Payment Gateway:</strong> All payments are processed securely through Stripe</li>
        <li><strong>Payment Timing:</strong> Full payment is required at the time of booking confirmation</li>
        <li><strong>Accepted Payment Methods:</strong> Visa, MasterCard, American Express, and other cards supported by Stripe</li>
        <li><strong>Currency:</strong> All transactions are processed in Australian Dollars (AUD)</li>
        <li><strong>Payment Security:</strong> We never store your complete card details - all payment information is handled securely by Stripe</li>
    </ul>

    <h3>4.4 Platform Commission</h3>
    <p>Elite Car Hire charges a 15% commission on all completed bookings. This commission is:</p>
    <ul>
        <li>Deducted from the Owner\'s payout</li>
        <li>Applied uniformly to all vehicle types and categories</li>
        <li>Used to maintain and improve platform services, payment processing, and customer support</li>
    </ul>

    <p><strong>Commission Example:</strong></p>
    <ul>
        <li>Booking Total: $1,000</li>
        <li>Platform Commission (15%): $150</li>
        <li>Owner Payout: $850</li>
    </ul>

    <h3>4.5 Owner Payouts</h3>
    <p>Owners receive their payout (booking amount minus platform commission) according to the payment cycle configured by platform administrators, typically:</p>
    <ul>
        <li>On booking completion, or</li>
        <li>Weekly on Mondays, or</li>
        <li>Monthly on the 1st of each month</li>
    </ul>

    <h2>5. Cancellation and Refund Policy</h2>

    <h3>5.1 Cancellation by Customer</h3>
    <p><strong>Cancellation Fee:</strong> All cancellations are subject to a <strong>50% cancellation fee</strong> of the total booking amount, regardless of how far in advance the cancellation is made.</p>

    <p><strong>Cancellation Process:</strong></p>
    <ol>
        <li>Customer initiates cancellation through their booking dashboard</li>
        <li>50% of the booking amount is retained as cancellation fee</li>
        <li>Remaining 50% is refunded to the original payment method within 5-10 business days</li>
        <li>Owner and Customer both receive cancellation confirmation notifications</li>
    </ol>

    <h3>5.2 Cancellation by Owner</h3>
    <p>If an Owner must cancel a confirmed booking:</p>
    <ul>
        <li>Customer receives a <strong>full refund (100%)</strong> of the booking amount</li>
        <li>Owner may face account penalties or suspension for repeated cancellations</li>
        <li>Customer receives priority rebooking assistance</li>
    </ul>

    <h3>5.3 Cancellation Due to Force Majeure</h3>
    <p>In cases of extreme weather, natural disasters, government restrictions, or other events beyond reasonable control:</p>
    <ul>
        <li>Either party may cancel without penalty</li>
        <li>Full refund issued to Customer</li>
        <li>No negative impact on Owner account standing</li>
    </ul>

    <h2>6. Vehicle Owner Responsibilities</h2>

    <h3>6.1 Insurance Requirements</h3>
    <p>Owners must maintain:</p>
    <ul>
        <li>Comprehensive vehicle insurance valid throughout Australia</li>
        <li>Coverage that explicitly permits commercial hire or peer-to-peer rental</li>
        <li>Policy details and expiry dates kept current in the platform system</li>
        <li>Minimum public liability coverage of $20 million</li>
    </ul>

    <h3>6.2 Vehicle Condition and Maintenance</h3>
    <p>Owners warrant that listed vehicles:</p>
    <ul>
        <li>Are roadworthy and comply with all Australian vehicle standards</li>
        <li>Have current registration valid in their listed state/territory</li>
        <li>Are maintained in excellent mechanical and aesthetic condition</li>
        <li>Match the descriptions, specifications, and photos provided in listings</li>
        <li>Are clean and presentable at the time of handover to Customers</li>
    </ul>

    <h3>6.3 Accurate Listing Information</h3>
    <p>Owners must provide truthful and accurate information about:</p>
    <ul>
        <li>Vehicle make, model, year, and color</li>
        <li>Features, amenities, and specifications</li>
        <li>Maximum passenger capacity</li>
        <li>Any limitations, restrictions, or conditions of use</li>
        <li>Pricing and availability</li>
    </ul>

    <h3>6.4 Availability Management</h3>
    <p>Owners are responsible for:</p>
    <ul>
        <li>Keeping their availability calendar accurate and up-to-date</li>
        <li>Blocking dates when vehicles are unavailable</li>
        <li>Responding to booking requests within 48 hours</li>
        <li>Honoring all confirmed bookings</li>
    </ul>

    <h2>7. Customer Responsibilities</h2>

    <p><strong>Important Note:</strong> Elite Car Hire operates as a chauffeur service. Owners are the sole authorized drivers of all vehicles. Customers are passengers only.</p>

    <h3>7.1 Passenger Requirements</h3>
    <p>Customers must:</p>
    <ul>
        <li>Be at least 18 years of age for booking purposes</li>
        <li>Provide accurate pickup and destination information</li>
        <li>Be present at the agreed pickup time and location</li>
        <li>Provide valid identification when requested</li>
        <li>Have the booking confirmation or reference number available</li>
    </ul>

    <h3>7.2 Passenger Conduct</h3>
    <p>Customers agree to:</p>
    <ul>
        <li>Conduct themselves in a respectful and lawful manner</li>
        <li>Follow all reasonable directions given by the Owner/Driver</li>
        <li>Not engage in illegal activities while in the vehicle</li>
        <li>Not smoke in the vehicle unless explicitly permitted by the Owner</li>
        <li>Wear seatbelts at all times as required by law</li>
        <li>Keep the vehicle clean and free from damage</li>
        <li>Report any concerns or issues to the Owner/Driver immediately</li>
    </ul>

    <h3>7.3 Prohibited Conduct</h3>
    <p>Customers must NOT:</p>
    <ul>
        <li>Operate or attempt to operate the vehicle (Owners are the sole drivers)</li>
        <li>Consume alcohol or drugs in an excessive or disruptive manner</li>
        <li>Exceed passenger capacity limits</li>
        <li>Transport hazardous or illegal materials</li>
        <li>Damage, modify, or alter the vehicle in any way</li>
        <li>Behave in an aggressive, abusive, or threatening manner toward the Owner/Driver</li>
        <li>Interfere with the Owner/Driver\'s operation of the vehicle</li>
    </ul>

    <h3>7.4 Service Modifications</h3>
    <p>Customers understand that:</p>
    <ul>
        <li>Route and itinerary changes may be requested but are subject to Owner approval</li>
        <li>The Owner/Driver has final discretion on routes, stops, and driving decisions for safety</li>
        <li>Extended time or additional destinations may incur extra charges as agreed with the Owner</li>
        <li>The Owner/Driver may refuse service or terminate the journey if the Customer violates these terms</li>
    </ul>

    <h2>8. Damage, Loss, and Liability</h2>

    <h3>8.1 Customer Liability for Damage</h3>
    <p>As passengers, Customers are liable ONLY for damage they cause through:</p>
    <ul>
        <li>Failure to follow the Owner/Driver\'s reasonable directions</li>
        <li>Intentional or reckless damage to the vehicle interior or exterior</li>
        <li>Interior damage including stains, burns, tears, or odors caused by the Customer</li>
        <li>Damage to accessories or equipment caused by misuse or negligence</li>
        <li>Loss or theft of items left in the vehicle due to Customer negligence</li>
    </ul>

    <p><strong>Important:</strong> Customers are NOT liable for damage caused by the Owner/Driver\'s operation of the vehicle, normal wear and tear, or mechanical failures not caused by Customer actions.</p>

    <h3>8.2 Accident and Incident Reporting</h3>
    <p>In the event of an accident, incident, or damage, Customers must:</p>
    <ol>
        <li>Remain calm and follow the Owner/Driver\'s instructions</li>
        <li>Contact emergency services if required (000)</li>
        <li>Provide accurate information to authorities if requested</li>
        <li>Notify Elite Car Hire within 24 hours if the incident involved Customer conduct</li>
        <li>Complete a detailed incident report if requested</li>
        <li>Cooperate fully with any investigation or insurance claims process</li>
        <li>Provide honest statements about the incident</li>
    </ol>

    <h3>8.3 Insurance Coverage</h3>
    <p>Vehicle insurance is maintained by the Owner for their operation of the vehicle. Customers should:</p>
    <ul>
        <li>Understand that the Owner\'s insurance covers vehicle operation and accidents</li>
        <li>Consider obtaining personal travel or accident insurance for their own protection</li>
        <li>Be aware they may be liable for interior damage they directly cause through misconduct</li>
    </ul>

    <p><em>Note:</em> Elite Car Hire is not responsible for insurance coverage. Owners maintain appropriate insurance for their chauffeur services. Customers are responsible for their own personal insurance needs.</p>

    <h2>9. Dispute Resolution</h2>

    <h3>9.1 Dispute Reporting</h3>
    <p>If disputes arise between Owners and Customers:</p>
    <ul>
        <li>Both parties should first attempt to resolve the matter directly</li>
        <li>If resolution is not possible, disputes can be formally raised through the platform</li>
        <li>Elite Car Hire will review evidence from both parties</li>
        <li>Platform administrators will work toward a fair resolution</li>
    </ul>

    <h3>9.2 Platform Decision</h3>
    <p>Elite Car Hire reserves the right to make final determinations on disputes based on:</p>
    <ul>
        <li>Evidence provided (photos, documentation, communications)</li>
        <li>Platform terms and policies</li>
        <li>Australian consumer law</li>
        <li>Fairness and reasonableness</li>
    </ul>

    <h2>10. Prohibited Conduct</h2>

    <p>Users must not:</p>
    <ul>
        <li>Provide false or misleading information</li>
        <li>Impersonate another person or entity</li>
        <li>Engage in fraudulent transactions</li>
        <li>Harass, abuse, or threaten other users</li>
        <li>Attempt to circumvent the platform to avoid fees</li>
        <li>Use the platform for any illegal purpose</li>
        <li>Interfere with platform operations or security</li>
        <li>Scrape, data mine, or collect user information</li>
    </ul>

    <h2>11. Account Suspension and Termination</h2>

    <h3>11.1 Suspension</h3>
    <p>Elite Car Hire may suspend accounts for:</p>
    <ul>
        <li>Violation of these Terms of Service</li>
        <li>Suspicious or fraudulent activity</li>
        <li>Multiple customer complaints</li>
        <li>Non-payment or payment disputes</li>
        <li>Pending investigation of incidents</li>
    </ul>

    <h3>11.2 Termination</h3>
    <p>We reserve the right to terminate accounts for:</p>
    <ul>
        <li>Serious or repeated violations of Terms</li>
        <li>Illegal activity</li>
        <li>Fraud or deception</li>
        <li>Unsafe vehicle operation</li>
        <li>Abuse of platform or other users</li>
    </ul>

    <p>Terminated users:</p>
    <ul>
        <li>Lose access to the platform</li>
        <li>Forfeit any pending payouts (if violations involve fraud)</li>
        <li>May be reported to authorities if illegal activity is involved</li>
    </ul>

    <h2>12. Limitation of Liability</h2>

    <h3>12.1 Platform Liability</h3>
    <p>Elite Car Hire operates as a marketplace connecting Owners and Customers. To the fullest extent permitted by law:</p>
    <ul>
        <li>We are not liable for the condition, safety, or legality of listed vehicles</li>
        <li>We are not responsible for the conduct or actions of Owners or Customers</li>
        <li>We do not guarantee the accuracy of vehicle listings or availability</li>
        <li>We are not party to disputes between Owners and Customers</li>
    </ul>

    <h3>12.2 Service Availability</h3>
    <p>We strive to maintain platform availability but do not guarantee:</p>
    <ul>
        <li>Uninterrupted access to services</li>
        <li>Error-free operation</li>
        <li>Freedom from viruses or malicious code</li>
        <li>Specific results from platform use</li>
    </ul>

    <h3>12.3 Maximum Liability</h3>
    <p>Our maximum aggregate liability for any claims arising from platform use is limited to the lesser of:</p>
    <ul>
        <li>The total fees paid by you in the 12 months preceding the claim, or</li>
        <li>$1,000 AUD</li>
    </ul>

    <h3>12.4 Australian Consumer Law</h3>
    <p>Nothing in these Terms excludes, restricts, or modifies any consumer rights under the Australian Consumer Law or other applicable laws that cannot be excluded, restricted, or modified by agreement.</p>

    <h2>13. Privacy and Data Protection</h2>

    <p>Your privacy is important to us. Our collection, use, and disclosure of personal information is governed by our Privacy Policy. By using Elite Car Hire, you consent to:</p>
    <ul>
        <li>Collection of personal information necessary for platform operation</li>
        <li>Sharing of relevant information between Owners and Customers for bookings</li>
        <li>Use of information for payment processing, notifications, and platform improvements</li>
        <li>Compliance with legal obligations and requests from authorities</li>
    </ul>

    <p>We implement industry-standard security measures to protect your data but cannot guarantee absolute security.</p>

    <h2>14. Intellectual Property</h2>

    <p>All platform content, including but not limited to:</p>
    <ul>
        <li>Website design, layout, and graphics</li>
        <li>Software, code, and functionality</li>
        <li>Logos, branding, and trademarks</li>
        <li>Text content created by Elite Car Hire</li>
    </ul>

    <p>...is owned by or licensed to Elite Car Hire and protected by copyright, trademark, and other intellectual property laws.</p>

    <p><strong>User Content:</strong> By uploading photos, descriptions, or other content, you grant Elite Car Hire a worldwide, non-exclusive, royalty-free license to use, display, and distribute that content for platform operation and marketing purposes.</p>

    <h2>15. Modifications to Terms</h2>

    <p>Elite Car Hire reserves the right to modify these Terms at any time. When changes are made:</p>
    <ul>
        <li>Updated Terms will be posted on the website</li>
        <li>The "Last Updated" date will be revised</li>
        <li>Continued use of the platform after changes constitutes acceptance</li>
        <li>Significant changes may be communicated via email</li>
    </ul>

    <h2>16. Governing Law and Jurisdiction</h2>

    <p>These Terms are governed by the laws of Australia. Any disputes arising from or relating to these Terms or platform use shall be subject to the exclusive jurisdiction of the courts of Australia.</p>

    <h2>17. Contact Information</h2>

    <p>For questions, concerns, or support regarding these Terms of Service:</p>
    <ul>
        <li><strong>Email:</strong> support@elitecarhire.au</li>
        <li><strong>Contact Form:</strong> Available through the platform</li>
        <li><strong>Response Time:</strong> We aim to respond within 48 hours</li>
    </ul>

    <h2>18. Severability</h2>

    <p>If any provision of these Terms is found to be invalid, illegal, or unenforceable, the remaining provisions shall continue in full force and effect.</p>

    <h2>19. Entire Agreement</h2>

    <p>These Terms, together with our Privacy Policy and any other policies referenced herein, constitute the entire agreement between you and Elite Car Hire regarding platform use and supersede all prior agreements and understandings.</p>

    <hr>

    <p class="acceptance-notice"><strong>By creating an account and using Elite Car Hire services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</strong></p>
</div>

<style>
.terms-container { max-width: 900px; margin: 0 auto; padding: 2rem; line-height: 1.6; }
.terms-container h1 { color: var(--primary-gold, #D4AF37); border-bottom: 3px solid var(--primary-gold, #D4AF37); padding-bottom: 0.5rem; margin-bottom: 1rem; }
.terms-container h2 { color: var(--dark-charcoal, #2C3E50); margin-top: 2rem; border-left: 4px solid var(--primary-gold, #D4AF37); padding-left: 1rem; }
.terms-container h3 { color: var(--dark-gray, #555); margin-top: 1.5rem; }
.terms-container ul, .terms-container ol { margin: 1rem 0; padding-left: 2rem; }
.terms-container li { margin: 0.5rem 0; }
.last-updated { background: #f7fafc; padding: 0.75rem; border-left: 4px solid #0066cc; margin-bottom: 1.5rem; }
.acceptance-notice { background: #fffbf0; border: 2px solid var(--primary-gold, #D4AF37); padding: 1.5rem; margin-top: 2rem; font-size: 1.1em; text-align: center; }
</style>
',
'Comprehensive Terms of Service for Elite Car Hire - peer-to-peer luxury and classic vehicle rental platform covering bookings, payments, cancellations, and user responsibilities',
'published')
ON DUPLICATE KEY UPDATE
    content = VALUES(content),
    meta_description = VALUES(meta_description),
    status = VALUES(status),
    updated_at = CURRENT_TIMESTAMP;
-- Elite Car Hire - Comprehensive FAQ Page
-- This migration adds the FAQ content to the cms_pages table

INSERT INTO cms_pages (page_key, title, content, meta_description, status)
VALUES ('faq', 'Frequently Asked Questions', '
<div class="faq-container">
    <h1>Frequently Asked Questions</h1>
    <p class="faq-intro">Find answers to common questions about Elite Car Hire. If you can\'t find what you\'re looking for, please <a href="/contact">contact our support team</a>.</p>

    <div class="faq-search-container">
        <input type="text" id="faq-search" placeholder="Search questions..." />
    </div>

    <!-- CUSTOMER SECTION -->
    <div class="faq-section">
        <h2 class="section-title">For Customers</h2>

        <div class="faq-category">
            <h3 class="category-title">Booking & Payments</h3>

            <div class="faq-item">
                <h4 class="faq-question">How do I book a vehicle on Elite Car Hire?</h4>
                <div class="faq-answer">
                    <p>Booking is simple:</p>
                    <ol>
                        <li><strong>Browse Vehicles:</strong> Search our collection of luxury, exotic, and classic vehicles by state, category, or availability dates</li>
                        <li><strong>Select Your Dream Ride:</strong> View detailed specifications, photos, and hourly rates</li>
                        <li><strong>Submit Booking Request:</strong> Choose your date, time, duration (minimum 4 hours), pickup location, destinations, and event type</li>
                        <li><strong>Owner Confirmation:</strong> The vehicle owner reviews your request and either confirms or adds any necessary additional charges (e.g., for extended travel distance)</li>
                        <li><strong>Approve & Pay:</strong> If additional charges were added, review and approve them. Then complete secure payment via Stripe</li>
                        <li><strong>Confirmation:</strong> Receive booking confirmation with all details and owner contact information</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What is the minimum and maximum booking duration?</h4>
                <div class="faq-answer">
                    <p><strong>Minimum:</strong> 4 hours - This ensures a quality experience and makes the booking worthwhile for both you and the owner.</p>
                    <p><strong>Maximum:</strong> 30 days - Perfect for extended rentals, special occasions, or luxury getaways.</p>
                    <p>Most bookings are for special events like weddings (4-8 hours), corporate functions (4-6 hours), or weekend experiences (2-3 days).</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">How far in advance can I book?</h4>
                <div class="faq-answer">
                    <p>You can book vehicles up to <strong>90 days in advance</strong>. We recommend booking as early as possible for popular dates like:</p>
                    <ul>
                        <li>Wedding season (September - April)</li>
                        <li>School formals (November - December)</li>
                        <li>New Year\'s Eve</li>
                        <li>Valentine\'s Day</li>
                        <li>Public holidays and long weekends</li>
                    </ul>
                    <p>Bookings must be made at least <strong>24 hours in advance</strong> to allow proper coordination.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What payment methods do you accept?</h4>
                <div class="faq-answer">
                    <p>We use <strong>Stripe</strong> as our secure payment gateway, accepting:</p>
                    <ul>
                        <li>Visa</li>
                        <li>MasterCard</li>
                        <li>American Express</li>
                        <li>All major debit cards</li>
                    </ul>
                    <p>All payments are processed in Australian Dollars (AUD). Your card information is never stored on our servers - it\'s handled securely by Stripe\'s PCI-compliant payment processing.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">When do I need to pay for my booking?</h4>
                <div class="faq-answer">
                    <p><strong>Full payment is required at the time of booking confirmation.</strong></p>
                    <p>Once the owner confirms your booking (and you approve any additional charges if applicable), you\'ll be directed to our secure payment page. Your booking is not confirmed until payment is successfully processed.</p>
                    <p>This policy ensures:</p>
                    <ul>
                        <li>Your booking is guaranteed and locked in</li>
                        <li>Owners can confidently block out their calendars</li>
                        <li>No payment surprises on the day of your event</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Why was I asked to approve additional charges?</h4>
                <div class="faq-answer">
                    <p>When you submit a booking, the initial quote is based on the vehicle\'s hourly rate multiplied by your rental duration. However, owners may add additional charges for:</p>
                    <ul>
                        <li><strong>Excess Travel Distance:</strong> If your pickup location or destinations are significantly outside the owner\'s standard service area</li>
                        <li><strong>Special Requests:</strong> Custom decorations, extended waiting times, or specific services</li>
                        <li><strong>Peak Period Surcharges:</strong> High-demand dates like New Year\'s Eve or Valentine\'s Day</li>
                    </ul>
                    <p>Owners must provide a clear, detailed reason for any additional charges. You have full transparency - you can see the breakdown of base amount + additional charges + reason before deciding to approve or reject.</p>
                    <p>If you reject the additional charges, your booking will be cancelled with no fee.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What types of vehicles can I book?</h4>
                <div class="faq-answer">
                    <p>Elite Car Hire specializes in exceptional vehicles across several categories:</p>
                    <ul>
                        <li><strong>Classic Muscle Cars:</strong> Iconic Australian and American muscle - Holdens, Fords, Chevrolets, and more</li>
                        <li><strong>Luxury Exotic Vehicles:</strong> High-end sports cars, supercars, and exotic imports - Ferrari, Lamborghini, Porsche, McLaren</li>
                        <li><strong>Premium Vehicles:</strong> Luxury sedans, SUVs, and prestige brands - Mercedes-Benz, BMW, Audi, Rolls-Royce</li>
                        <li><strong>Other Unique Vehicles:</strong> Vintage classics, rare collectibles, and one-of-a-kind rides</li>
                    </ul>
                    <p>Perfect for weddings, corporate events, photoshoots, film productions, milestone birthdays, anniversaries, and unforgettable experiences.</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h3 class="category-title">Cancellations & Changes</h3>

            <div class="faq-item">
                <h4 class="faq-question">What is your cancellation policy?</h4>
                <div class="faq-answer">
                    <p><strong>50% Cancellation Fee Applies</strong></p>
                    <p>If you need to cancel a confirmed and paid booking:</p>
                    <ul>
                        <li>A <strong>50% cancellation fee</strong> is charged, regardless of when you cancel</li>
                        <li>The remaining 50% is refunded to your original payment method</li>
                        <li>Refunds are processed within 5-10 business days</li>
                        <li>Both you and the owner receive cancellation confirmation</li>
                    </ul>
                    <p><strong>Why this policy?</strong> Owners often decline other bookings and commit their time to your reservation. The cancellation fee compensates them for lost opportunities and ensures serious bookings.</p>
                    <p><strong>Tip:</strong> Consider purchasing event insurance if you\'re concerned about unexpected changes due to weather or unforeseen circumstances.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Can I modify my booking after it\'s confirmed?</h4>
                <div class="faq-answer">
                    <p>Modifications depend on availability and the owner\'s approval:</p>
                    <p><strong>Date/Time Changes:</strong> Contact the owner directly through the platform messaging system. If the new date/time is available, the owner may approve the change. Additional charges may apply if the change extends duration or adds travel.</p>
                    <p><strong>Duration Extensions:</strong> If you need more hours, contact the owner. Extensions are subject to availability and will be charged at the vehicle\'s hourly rate.</p>
                    <p><strong>Location Changes:</strong> Pickup or destination changes may affect pricing if they add significant travel distance. Discuss with the owner and they\'ll update the booking if necessary.</p>
                    <p>For significant changes, it may be easier to cancel and create a new booking.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What happens if the owner cancels my booking?</h4>
                <div class="faq-answer">
                    <p>In the rare event an owner must cancel your confirmed booking:</p>
                    <ul>
                        <li>You receive a <strong>full refund (100%)</strong> of your payment</li>
                        <li>Refund is processed immediately to your original payment method</li>
                        <li>You\'ll be notified via email and SMS</li>
                        <li>Our support team can help you find an alternative vehicle</li>
                    </ul>
                    <p>Owner cancellations are tracked and repeated cancellations may result in account penalties or suspension.</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h3 class="category-title">Driver Requirements & Safety</h3>

            <div class="faq-item">
                <h4 class="faq-question">What are the driver requirements?</h4>
                <div class="faq-answer">
                    <p>To rent a vehicle on Elite Car Hire, you must:</p>
                    <ul>
                        <li><strong>Age:</strong> Be at least 25 years old</li>
                        <li><strong>License:</strong> Hold a valid, full Australian driver\'s license (not provisional or learner)</li>
                        <li><strong>Experience:</strong> Have a clean driving record (owners may ask to see your license)</li>
                        <li><strong>Sobriety:</strong> Not operate the vehicle under the influence of alcohol or drugs</li>
                    </ul>
                    <p>You must present your driver\'s license to the owner at the time of vehicle pickup for verification.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Can someone else drive the vehicle?</h4>
                <div class="faq-answer">
                    <p><strong>Only with prior owner approval.</strong></p>
                    <p>The booking is made under your name and you are the authorized driver. If you need an additional driver:</p>
                    <ol>
                        <li>Contact the owner before your booking</li>
                        <li>Provide the additional driver\'s details and license information</li>
                        <li>Get written approval from the owner</li>
                        <li>Additional driver must meet all driver requirements (age 25+, full license)</li>
                    </ol>
                    <p>Allowing unauthorized drivers may void insurance and result in liability for any damages.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What if there\'s an accident or damage during my rental?</h4>
                <div class="faq-answer">
                    <p><strong>Immediate Steps:</strong></p>
                    <ol>
                        <li><strong>Safety First:</strong> Check for injuries and call 000 if needed</li>
                        <li><strong>Secure the Scene:</strong> Turn on hazard lights, set up warning triangles if safe to do so</li>
                        <li><strong>Contact Owner:</strong> Notify the vehicle owner immediately</li>
                        <li><strong>Document Everything:</strong> Take photos of damage, get witness contact details, exchange information with other parties</li>
                        <li><strong>Police Report:</strong> Obtain a police report for any accident involving injury or significant damage</li>
                        <li><strong>Report to Platform:</strong> Notify Elite Car Hire within 24 hours</li>
                    </ol>
                    <p><strong>Liability:</strong> You are responsible for damage to the vehicle during your rental period. The owner\'s insurance will handle the claim, but you may be liable for insurance excess amounts. Review insurance coverage with the owner before your rental.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Is insurance included?</h4>
                <div class="faq-answer">
                    <p>All vehicles on Elite Car Hire must be covered by the owner\'s comprehensive insurance policy. This insurance:</p>
                    <ul>
                        <li>Must explicitly permit commercial hire or peer-to-peer rental</li>
                        <li>Covers damage to the vehicle and third-party property</li>
                        <li>Includes minimum $20 million public liability coverage</li>
                    </ul>
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>Insurance is held by the vehicle owner, not Elite Car Hire</li>
                        <li>You may be liable for insurance excess amounts (typically $1,000-$5,000 depending on vehicle value)</li>
                        <li>Discuss coverage details, excess amounts, and exclusions with the owner before booking</li>
                        <li>Consider obtaining additional personal insurance if desired</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- OWNER SECTION -->
    <div class="faq-section">
        <h2 class="section-title">For Vehicle Owners</h2>

        <div class="faq-category">
            <h3 class="category-title">Getting Started</h3>

            <div class="faq-item">
                <h4 class="faq-question">How do I list my vehicle on Elite Car Hire?</h4>
                <div class="faq-answer">
                    <p>Listing your luxury, exotic, or classic vehicle is straightforward:</p>
                    <ol>
                        <li><strong>Create Owner Account:</strong> Register with your details and contact information</li>
                        <li><strong>Add Vehicle Listing:</strong> Provide:
                            <ul>
                                <li>Make, model, year, color, and category</li>
                                <li>Detailed description highlighting unique features</li>
                                <li>High-quality photos (minimum 5, professional recommended)</li>
                                <li>Hourly rate and minimum booking hours</li>
                                <li>Service area and state</li>
                            </ul>
                        </li>
                        <li><strong>Upload Documentation:</strong>
                            <ul>
                                <li>Vehicle registration certificate</li>
                                <li>Comprehensive insurance certificate showing commercial hire coverage</li>
                                <li>Insurance expiry date</li>
                            </ul>
                        </li>
                        <li><strong>Submit for Approval:</strong> Our admin team reviews your listing (usually within 24-48 hours)</li>
                        <li><strong>Go Live:</strong> Once approved, your vehicle appears in search results and you can start accepting bookings!</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What are the requirements for listing my vehicle?</h4>
                <div class="faq-answer">
                    <p><strong>Vehicle Requirements:</strong></p>
                    <ul>
                        <li>Must be a luxury, exotic, classic muscle, or premium vehicle</li>
                        <li>Current registration valid in your state/territory</li>
                        <li>Roadworthy and well-maintained condition</li>
                        <li>Clean, presentable, and matches listing description</li>
                        <li>No major mechanical issues or safety concerns</li>
                    </ul>

                    <p><strong>Insurance Requirements:</strong></p>
                    <ul>
                        <li><strong>Comprehensive insurance</strong> covering vehicle damage</li>
                        <li>Policy must explicitly permit <strong>commercial hire</strong> or peer-to-peer rental</li>
                        <li>Minimum <strong>$20 million public liability</strong> coverage</li>
                        <li>Policy and expiry date kept current in your profile</li>
                    </ul>

                    <p><strong>Owner Requirements:</strong></p>
                    <ul>
                        <li>Australian resident</li>
                        <li>Trustworthy and professional communication</li>
                        <li>Ability to meet customers for vehicle handover</li>
                        <li>Responsive to booking requests (within 48 hours)</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Do I need special insurance for peer-to-peer car hire?</h4>
                <div class="faq-answer">
                    <p><strong>Yes, absolutely.</strong></p>
                    <p>Standard personal vehicle insurance typically does NOT cover commercial hire or peer-to-peer rental activities. You must:</p>
                    <ol>
                        <li><strong>Contact your insurance provider:</strong> Inform them you plan to hire your vehicle through a peer-to-peer platform</li>
                        <li><strong>Upgrade to commercial coverage:</strong> Many insurers offer commercial hire extensions or specialized policies. In most cases, your premium won\'t change significantly</li>
                        <li><strong>Confirm coverage includes:</strong>
                            <ul>
                                <li>Peer-to-peer or commercial hire use</li>
                                <li>Damage to your vehicle</li>
                                <li>Third-party property damage (minimum $5 million, preferably $20 million)</li>
                                <li>Public liability coverage</li>
                            </ul>
                        </li>
                        <li><strong>Upload documentation:</strong> Provide your insurance certificate and expiry date when creating your listing</li>
                    </ol>
                    <p><strong>Recommended Providers:</strong> Consult Australian specialty vehicle insurers who understand classic and luxury car hire arrangements.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">How should I price my vehicle?</h4>
                <div class="faq-answer">
                    <p>Pricing is flexible and set by you based on your vehicle\'s value, rarity, and market demand. Consider:</p>

                    <p><strong>Industry Benchmarks:</strong></p>
                    <ul>
                        <li><strong>Classic Muscle Cars:</strong> $200-$400 per hour</li>
                        <li><strong>Luxury/Premium Sedans:</strong> $250-$500 per hour</li>
                        <li><strong>Exotic Sports Cars:</strong> $500-$1,500+ per hour</li>
                        <li><strong>Ultra-Rare/Collectibles:</strong> $1,500-$3,000+ per hour</li>
                    </ul>

                    <p><strong>Pricing Factors:</strong></p>
                    <ul>
                        <li>Vehicle value and replacement cost</li>
                        <li>Rarity and uniqueness</li>
                        <li>Demand in your area</li>
                        <li>Event type (weddings often command premium rates)</li>
                        <li>Included services (decorations, chauffeur service, etc.)</li>
                        <li>Your competition on the platform</li>
                    </ul>

                    <p><strong>Tip:</strong> Start slightly higher and adjust based on booking demand. You can also offer package deals for longer bookings (e.g., 10% discount for 8+ hours).</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h3 class="category-title">Managing Bookings</h3>

            <div class="faq-item">
                <h4 class="faq-question">How do I receive and manage bookings?</h4>
                <div class="faq-answer">
                    <p>When a customer requests your vehicle:</p>
                    <ol>
                        <li><strong>Notification:</strong> You\'ll receive email and SMS alerts with booking details (date, time, duration, pickup location, event type)</li>
                        <li><strong>Review Request:</strong> Log into your Owner Dashboard to view full details and customer information</li>
                        <li><strong>Decide on Additional Charges:</strong> If the customer\'s locations require significant extra travel, you can add additional charges with a detailed reason</li>
                        <li><strong>Confirm or Decline:</strong>
                            <ul>
                                <li><strong>Confirm without charges:</strong> Booking moves directly to payment</li>
                                <li><strong>Confirm with additional charges:</strong> Customer must review and approve before payment</li>
                                <li><strong>Decline:</strong> If unavailable or unsuitable, decline with a polite reason</li>
                            </ul>
                        </li>
                        <li><strong>Payment Processing:</strong> Customer pays via Stripe - you don\'t handle any money directly</li>
                        <li><strong>Coordinate Handover:</strong> Contact customer to arrange exact pickup location and any special instructions</li>
                    </ol>
                    <p><strong>Response Time:</strong> Aim to respond within 24 hours. Quick responses improve your booking rate and customer satisfaction.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">When and how do I get paid?</h4>
                <div class="faq-answer">
                    <p><strong>Payment Structure:</strong></p>
                    <p>Your payout = <strong>Booking Total - 15% Platform Commission</strong></p>

                    <p><strong>Example:</strong></p>
                    <ul>
                        <li>Customer pays: $1,000</li>
                        <li>Platform commission (15%): $150</li>
                        <li>Your payout: $850</li>
                    </ul>

                    <p><strong>Payout Schedule:</strong></p>
                    <p>Payouts are processed according to the platform\'s payment cycle (configured by admin):</p>
                    <ul>
                        <li><strong>On Completion:</strong> Paid soon after booking is completed</li>
                        <li><strong>Weekly:</strong> Every Monday</li>
                        <li><strong>Bi-weekly:</strong> Every two weeks</li>
                        <li><strong>Monthly:</strong> On the 1st of each month</li>
                    </ul>

                    <p><strong>Payment Method:</strong> Direct bank transfer to your registered account</p>

                    <p><strong>Track Your Earnings:</strong> View all payouts, pending payments, and commission breakdowns in your Owner Dashboard under "Payouts".</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What is the 15% commission and what does it cover?</h4>
                <div class="faq-answer">
                    <p>Elite Car Hire charges a <strong>15% commission on all completed bookings</strong>. This uniform rate applies to all vehicle types and owners.</p>

                    <p><strong>What the commission covers:</strong></p>
                    <ul>
                        <li><strong>Payment Processing:</strong> Secure Stripe payment handling (normally 1.75% + 30¢ per transaction)</li>
                        <li><strong>Platform Hosting & Maintenance:</strong> Website infrastructure, servers, and ongoing improvements</li>
                        <li><strong>Marketing & Advertising:</strong> SEO, social media, and campaigns that bring customers to the platform</li>
                        <li><strong>Customer Support:</strong> Dispute resolution, incident handling, and support for both owners and customers</li>
                        <li><strong>Security & Compliance:</strong> Data protection, fraud prevention, and legal compliance</li>
                        <li><strong>Booking Management Tools:</strong> Calendar management, notifications, messaging system</li>
                    </ul>

                    <p><strong>Industry Comparison:</strong> The 15% rate is competitive compared to other luxury car hire platforms (typically 10-20%) and significantly lower than general marketplace platforms (25-30%).</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Can I block out dates when my vehicle is unavailable?</h4>
                <div class="faq-answer">
                    <p><strong>Yes, absolutely!</strong></p>
                    <p>Managing your availability calendar is crucial:</p>
                    <ol>
                        <li>Go to <strong>Owner Dashboard → My Vehicles</strong></li>
                        <li>Click <strong>"Manage Availability"</strong> for the vehicle</li>
                        <li>Select dates to block (personal use, maintenance, other bookings, vacations)</li>
                        <li>Blocked dates are instantly removed from customer availability searches</li>
                        <li>You can unblock dates anytime if plans change</li>
                    </ol>

                    <p><strong>Best Practices:</strong></p>
                    <ul>
                        <li>Block dates immediately after creating external bookings</li>
                        <li>Schedule regular maintenance and block those dates in advance</li>
                        <li>Review your calendar weekly to ensure accuracy</li>
                        <li>Block major holidays if you prefer time off</li>
                    </ul>

                    <p>Keeping your calendar updated prevents double-bookings and customer disappointment.</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h3 class="category-title">State-Specific Requirements (Australia)</h3>

            <div class="faq-item">
                <h4 class="faq-question">Do I need special licenses or permits to hire my vehicle in New South Wales (NSW)?</h4>
                <div class="faq-answer">
                    <p><strong>For occasional peer-to-peer hire through Elite Car Hire, specific licensing requirements depend on how frequently you hire your vehicle and whether you provide driving services.</strong></p>

                    <p><strong>If You\'re Providing Chauffeur Service (driving customers):</strong></p>
                    <ul>
                        <li>Must be at least 21 years old</li>
                        <li>Hold unrestricted NSW driver license for 12+ months</li>
                        <li>Obtain <strong>T-Code (Passenger Transport Licence Code)</strong> added to your license - requires criminal history check and medical assessment</li>
                        <li>Vehicle requires annual safety inspections (pink slip) regardless of age</li>
                        <li>Comprehensive insurance with minimum $5 million third-party property damage coverage</li>
                    </ul>

                    <p><strong>If Customer is Self-Driving (no chauffeur service):</strong></p>
                    <ul>
                        <li>No special driver authorisation required for occasional hire</li>
                        <li>Vehicle must have current NSW registration</li>
                        <li>Insurance must cover peer-to-peer hire activities</li>
                    </ul>

                    <p><strong>Resources:</strong> For detailed information, visit <a href="https://www.service.nsw.gov.au/" target="_blank">Service NSW</a> or contact Transport for NSW.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Do I need special licenses or permits to hire my vehicle in Victoria (VIC)?</h4>
                <div class="faq-answer">
                    <p><strong>If You\'re Providing Chauffeur Service (driving customers):</strong></p>
                    <ul>
                        <li>Must be at least 21 years old</li>
                        <li>Hold open driver\'s license for 6+ months</li>
                        <li>Apply for <strong>Driver Accreditation</strong> through Commercial Passenger Vehicles Victoria (CPVV)</li>
                        <li>Register vehicle as <strong>Commercial Passenger Vehicle (CPV)</strong> under "Booked Service" category</li>
                        <li>Pass vehicle safety inspection every 12 months by VicRoads licensed tester or Redbook Pty Ltd</li>
                        <li>Comprehensive insurance covering commercial passenger transport</li>
                    </ul>

                    <p><strong>If Customer is Self-Driving (no chauffeur service):</strong></p>
                    <ul>
                        <li>No special driver accreditation required for occasional peer-to-peer hire</li>
                        <li>Vehicle must have full VIC registration (club registration may have usage restrictions)</li>
                        <li>Insurance must permit commercial hire or peer-to-peer rental</li>
                    </ul>

                    <p><strong>Resources:</strong> Visit <a href="https://www.cpv.vic.gov.au/" target="_blank">CPVV</a> for driver accreditation and CPV registration details.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Do I need special licenses or permits to hire my vehicle in Queensland (QLD)?</h4>
                <div class="faq-answer">
                    <p><strong>If You\'re Providing Chauffeur Service (driving customers):</strong></p>
                    <ul>
                        <li>Must be at least 21 years old</li>
                        <li>Hold unrestricted Australian license for 12+ months in the last 2 years</li>
                        <li>Obtain <strong>Driver Authorisation (DA)</strong> - must be BHTX (booked hire taxi) or taxi category</li>
                        <li>Obtain <strong>BHSL (Booked Hire Service Licence)</strong> for your vehicle</li>
                        <li>Vehicle requires <strong>Certificate of Inspection (COI)</strong></li>
                        <li>Change CTP insurance to Class 26 (commercial passenger)</li>
                        <li>Only right-hand drive vehicles accepted</li>
                    </ul>

                    <p><strong>If Customer is Self-Driving (no chauffeur service):</strong></p>
                    <ul>
                        <li>No special driver authorisation required for occasional hire</li>
                        <li>Vehicle must have current QLD registration</li>
                        <li>Insurance must cover peer-to-peer rental activities</li>
                    </ul>

                    <p><strong>Resources:</strong> Visit <a href="https://www.tmr.qld.gov.au/" target="_blank">Queensland Transport and Main Roads</a> for DA and BHSL application details.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What about other Australian states and territories?</h4>
                <div class="faq-answer">
                    <p><strong>South Australia (SA):</strong></p>
                    <p>For chauffeur services, contact <a href="https://www.sa.gov.au/" target="_blank">Service SA</a> regarding passenger transport licensing.</p>

                    <p><strong>Western Australia (WA):</strong></p>
                    <p>For chauffeur services, visit <a href="https://www.wa.gov.au/" target="_blank">WA Department of Transport</a> for on-demand passenger transport requirements.</p>

                    <p><strong>Tasmania (TAS):</strong></p>
                    <p>Contact <a href="https://www.transport.tas.gov.au/" target="_blank">Transport Tasmania</a> for information on passenger transport licensing.</p>

                    <p><strong>Northern Territory (NT):</strong></p>
                    <p>Visit <a href="https://nt.gov.au/driving/commercial" target="_blank">NT Department of Infrastructure</a> for commercial vehicle requirements.</p>

                    <p><strong>Australian Capital Territory (ACT):</strong></p>
                    <ul>
                        <li>Must be at least 20 years old</li>
                        <li>Need full ACT driver\'s license with \'H\' condition for chauffeur services</li>
                        <li>Complete <strong>Working With Vulnerable People (WWVP) Registration</strong> including criminal history check</li>
                        <li>Full vehicle registration required</li>
                        <li>CTP insurance with minimum $5 million third-party property damage coverage</li>
                    </ul>
                    <p>Contact <a href="https://www.accesscanberra.act.gov.au/" target="_blank">Access Canberra</a> for detailed requirements.</p>

                    <p><strong>Important Note:</strong> Requirements vary significantly between states. If you provide chauffeur services, always check with your state\'s transport authority before accepting bookings.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">My car is on club registration - can I still hire it out?</h4>
                <div class="faq-answer">
                    <p><strong>Club registration restrictions vary by state and typically limit:</strong></p>
                    <ul>
                        <li>Annual mileage (often 90-365 days per year)</li>
                        <li>Permitted uses (club events, displays, limited recreational use)</li>
                        <li>Commercial activities</li>
                    </ul>

                    <p><strong>For peer-to-peer hire:</strong></p>
                    <p>Most club registration schemes <strong>do NOT permit commercial or paid hire activities</strong>. Check your state\'s specific club registration rules:</p>
                    <ul>
                        <li><a href="https://www.service.nsw.gov.au/" target="_blank">New South Wales</a></li>
                        <li><a href="https://www.vicroads.vic.gov.au/" target="_blank">Victoria</a></li>
                        <li><a href="https://www.tmr.qld.gov.au/" target="_blank">Queensland</a></li>
                        <li><a href="https://www.sa.gov.au/" target="_blank">South Australia</a></li>
                        <li><a href="https://www.wa.gov.au/" target="_blank">Western Australia</a></li>
                        <li><a href="https://www.transport.tas.gov.au/" target="_blank">Tasmania</a></li>
                    </ul>

                    <p><strong>Recommendation:</strong> Contact your vehicle club and state transport authority to confirm whether peer-to-peer hire is permitted under your registration type. You may need to switch to full registration for hire activities.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- GENERAL SECTION -->
    <div class="faq-section">
        <h2 class="section-title">General Information</h2>

        <div class="faq-category">
            <h3 class="category-title">Platform & Account</h3>

            <div class="faq-item">
                <h4 class="faq-question">Is Elite Car Hire available across Australia?</h4>
                <div class="faq-answer">
                    <p><strong>Yes!</strong> Elite Car Hire operates nationwide across:</p>
                    <ul>
                        <li>New South Wales (NSW)</li>
                        <li>Victoria (VIC)</li>
                        <li>Queensland (QLD)</li>
                        <li>South Australia (SA)</li>
                        <li>Western Australia (WA)</li>
                        <li>Tasmania (TAS)</li>
                        <li>Northern Territory (NT)</li>
                        <li>Australian Capital Territory (ACT)</li>
                    </ul>
                    <p>You can search for vehicles by state and some owners are willing to travel interstate for special events (additional charges may apply).</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">How do I contact Elite Car Hire support?</h4>
                <div class="faq-answer">
                    <p>Our support team is here to help:</p>
                    <ul>
                        <li><strong>Email:</strong> support@elitecarhire.au</li>
                        <li><strong>Contact Form:</strong> Available at <a href="/contact">/contact</a></li>
                        <li><strong>Help Center:</strong> Browse FAQs and guides in your account dashboard</li>
                    </ul>
                    <p><strong>Response Times:</strong></p>
                    <ul>
                        <li>General inquiries: Within 48 hours</li>
                        <li>Urgent booking issues: Within 24 hours</li>
                        <li>Safety/incident reports: Immediate priority response</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">How do I report an incident or dispute?</h4>
                <div class="faq-answer">
                    <p><strong>For Incidents (accidents, damage, safety concerns):</strong></p>
                    <ol>
                        <li>Take immediate safety actions</li>
                        <li>Contact the other party (owner or customer)</li>
                        <li>Document with photos and details</li>
                        <li>Report to Elite Car Hire within 24 hours via the incident form in your dashboard or by email</li>
                    </ol>

                    <p><strong>For Disputes (pricing, service quality, booking issues):</strong></p>
                    <ol>
                        <li>Try to resolve directly with the other party first</li>
                        <li>If unresolved, raise a formal dispute through your dashboard under "Support → Raise Dispute"</li>
                        <li>Provide all relevant evidence (messages, photos, documentation)</li>
                        <li>Our mediation team will review and work toward a fair resolution</li>
                    </ol>

                    <p>Elite Car Hire takes all reports seriously and investigates thoroughly to ensure platform safety and fairness.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">What vehicles are best suited for specific events?</h4>
                <div class="faq-answer">
                    <p><strong>Weddings:</strong></p>
                    <ul>
                        <li>Classic Rolls-Royce, Bentley, or vintage luxury sedans</li>
                        <li>Classic American convertibles (great for photos)</li>
                        <li>Elegant white or cream-colored vehicles</li>
                        <li>Can be decorated with ribbons, flowers, and "Just Married" signs</li>
                    </ul>

                    <p><strong>School Formals/Proms:</strong></p>
                    <ul>
                        <li>Modern exotic sports cars (Lamborghini, Ferrari, McLaren)</li>
                        <li>Classic muscle cars (Mustang, Camaro, Charger)</li>
                        <li>Luxury SUVs for groups</li>
                        <li>Bright, eye-catching colors popular</li>
                    </ul>

                    <p><strong>Corporate Events/Business:</strong></p>
                    <ul>
                        <li>Premium luxury sedans (Mercedes S-Class, BMW 7 Series, Audi A8)</li>
                        <li>Executive SUVs (Range Rover, Porsche Cayenne)</li>
                        <li>Professional, understated elegance</li>
                        <li>Black, silver, or dark colors preferred</li>
                    </ul>

                    <p><strong>Photoshoots/Film/Social Media:</strong></p>
                    <ul>
                        <li>Unique, rare, or vintage collectibles</li>
                        <li>Vehicles with interesting colors and styling</li>
                        <li>Convertibles and open-top vehicles</li>
                        <li>Any vehicle that makes a statement!</li>
                    </ul>

                    <p><strong>Milestone Celebrations (birthdays, anniversaries):</strong></p>
                    <ul>
                        <li>Dream car from the celebrant\'s era (e.g., 1960s muscle car for 60th birthday)</li>
                        <li>Exotic supercars for thrill experiences</li>
                        <li>Vintage classics for nostalgia</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Can I use Elite Car Hire vehicles for photoshoots or film productions?</h4>
                <div class="faq-answer">
                    <p><strong>Absolutely!</strong> Many owners welcome photoshoot and film bookings.</p>

                    <p><strong>When booking for static use (non-driving):</strong></p>
                    <ul>
                        <li>Clearly indicate "Photoshoot" or "Film Production" in event type</li>
                        <li>Specify if the vehicle will be driven or stationary</li>
                        <li>Provide location details (studio, outdoor location, etc.)</li>
                        <li>Discuss any special requirements (props, modifications, positioning)</li>
                    </ul>

                    <p><strong>Rates for static bookings:</strong></p>
                    <ul>
                        <li>Often the same hourly rate or slightly discounted (no fuel/distance costs)</li>
                        <li>Owner delivers vehicle to location and retrieves after shoot</li>
                        <li>Minimum booking time still applies (usually 4 hours)</li>
                    </ul>

                    <p><strong>Benefits for owners:</strong></p>
                    <ul>
                        <li>Great exposure - professional photos can be used in your listing!</li>
                        <li>Bragging rights if featured in films or media</li>
                        <li>Often easier than event transport (less wear and tear)</li>
                    </ul>

                    <p>Make sure to tag Elite Car Hire if you share photos on social media - we love seeing our vehicles in action!</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h3 class="category-title">Security & Privacy</h3>

            <div class="faq-item">
                <h4 class="faq-question">Is my payment information secure?</h4>
                <div class="faq-answer">
                    <p><strong>Yes, completely secure.</strong></p>
                    <p>Elite Car Hire uses <strong>Stripe</strong>, one of the world\'s most trusted payment processors, used by millions of businesses globally.</p>

                    <p><strong>Security Features:</strong></p>
                    <ul>
                        <li><strong>PCI-DSS Level 1 Certified:</strong> The highest level of payment security</li>
                        <li><strong>Tokenization:</strong> Your card details are never stored on our servers</li>
                        <li><strong>Encryption:</strong> All payment data transmitted with bank-level SSL encryption</li>
                        <li><strong>3D Secure:</strong> Additional authentication for supported cards</li>
                        <li><strong>Fraud Prevention:</strong> Advanced algorithms detect and prevent fraudulent transactions</li>
                    </ul>

                    <p>We never see or store your complete card information - it\'s handled entirely by Stripe\'s secure infrastructure.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">How is my personal information used?</h4>
                <div class="faq-answer">
                    <p>Elite Car Hire collects and uses your personal information to:</p>
                    <ul>
                        <li><strong>Facilitate Bookings:</strong> Share relevant details between owners and customers (name, phone, event details)</li>
                        <li><strong>Process Payments:</strong> Handle transactions securely through Stripe</li>
                        <li><strong>Send Notifications:</strong> Email and SMS updates about bookings, confirmations, reminders</li>
                        <li><strong>Improve Services:</strong> Analyze platform usage to enhance user experience</li>
                        <li><strong>Provide Support:</strong> Respond to inquiries and resolve issues</li>
                        <li><strong>Legal Compliance:</strong> Meet Australian privacy laws and regulations</li>
                    </ul>

                    <p><strong>We DO NOT:</strong></p>
                    <ul>
                        <li>Sell your information to third parties</li>
                        <li>Send spam or excessive marketing emails</li>
                        <li>Share your data without your consent (except as required by law)</li>
                    </ul>

                    <p>For complete details, review our <a href="/privacy">Privacy Policy</a>.</p>
                </div>
            </div>

            <div class="faq-item">
                <h4 class="faq-question">Can I delete my account?</h4>
                <div class="faq-answer">
                    <p><strong>Yes, you can request account deletion at any time.</strong></p>

                    <p><strong>Before Deletion:</strong></p>
                    <ul>
                        <li>Complete or cancel all active bookings</li>
                        <li>Settle any outstanding payments or disputes</li>
                        <li>Download any information you want to keep</li>
                    </ul>

                    <p><strong>To Delete Your Account:</strong></p>
                    <ol>
                        <li>Contact support@elitecarhire.au</li>
                        <li>Request account deletion with subject "Account Deletion Request"</li>
                        <li>We\'ll confirm your identity and process the request within 7-10 business days</li>
                    </ol>

                    <p><strong>What Happens:</strong></p>
                    <ul>
                        <li>Personal information is deleted (name, email, phone, address)</li>
                        <li>Booking history may be retained in anonymized form for legal/tax records</li>
                        <li>Payment records retained as required by financial regulations</li>
                        <li>Vehicle listings and photos removed (for owners)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="faq-still-need-help">
        <h2>Still Need Help?</h2>
        <p>If you couldn\'t find the answer you were looking for, our support team is ready to assist.</p>
        <p><strong>Contact us:</strong></p>
        <ul>
            <li>Email: <a href="mailto:support@elitecarhire.au">support@elitecarhire.au</a></li>
            <li>Use our <a href="/contact">Contact Form</a></li>
            <li>Response time: Within 48 hours for general inquiries, 24 hours for urgent booking issues</li>
        </ul>
    </div>
</div>

<style>
.faq-container { max-width: 1000px; margin: 0 auto; padding: 2rem; line-height: 1.6; }
.faq-container h1 { color: var(--primary-gold, #D4AF37); border-bottom: 3px solid var(--primary-gold, #D4AF37); padding-bottom: 0.5rem; margin-bottom: 1rem; }
.faq-intro { font-size: 1.1em; color: var(--dark-gray, #555); margin-bottom: 2rem; }

.faq-search-container { margin: 2rem 0; }
#faq-search { width: 100%; padding: 1rem; font-size: 1em; border: 2px solid #ddd; border-radius: 8px; }
#faq-search:focus { outline: none; border-color: var(--primary-gold, #D4AF37); }

.faq-section { margin: 3rem 0; }
.section-title { color: var(--dark-charcoal, #2C3E50); font-size: 1.8em; border-left: 5px solid var(--primary-gold, #D4AF37); padding-left: 1rem; margin: 2rem 0 1.5rem 0; }

.faq-category { margin: 2rem 0; padding: 1.5rem; background: #f9fafb; border-radius: 8px; }
.category-title { color: var(--primary-gold, #D4AF37); font-size: 1.4em; margin-bottom: 1rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem; }

.faq-item { margin: 1.5rem 0; padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid var(--primary-gold, #D4AF37); }
.faq-question { color: var(--dark-charcoal, #2C3E50); font-size: 1.1em; margin: 0 0 0.75rem 0; cursor: pointer; display: flex; align-items: center; }
.faq-question:hover { color: var(--primary-gold, #D4AF37); }
.faq-question::before { content: "▶"; color: var(--primary-gold, #D4AF37); margin-right: 0.5rem; font-size: 0.8em; }
.faq-item.open .faq-question::before { content: "▼"; }
.faq-answer { color: var(--dark-gray, #555); }
.faq-answer p { margin: 0.75rem 0; }
.faq-answer ul, .faq-answer ol { margin: 1rem 0 1rem 1.5rem; }
.faq-answer li { margin: 0.5rem 0; }
.faq-answer a { color: var(--primary-gold, #D4AF37); text-decoration: underline; }

.faq-still-need-help { background: linear-gradient(135deg, var(--primary-gold, #D4AF37) 0%, #B8941F 100%); color: white; padding: 2rem; border-radius: 12px; margin-top: 3rem; text-align: center; }
.faq-still-need-help h2 { color: white; margin-bottom: 1rem; }
.faq-still-need-help a { color: white; font-weight: bold; text-decoration: underline; }
.faq-still-need-help ul { list-style: none; padding: 0; margin: 1rem 0; }

@media (max-width: 768px) {
    .faq-container { padding: 1rem; }
    .section-title { font-size: 1.4em; }
    .category-title { font-size: 1.2em; }
    .faq-question { font-size: 1em; }
}
</style>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    // FAQ Search Functionality
    const searchInput = document.getElementById(\'faq-search\');
    if (searchInput) {
        searchInput.addEventListener(\'input\', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll(\'.faq-item\');

            faqItems.forEach(item => {
                const question = item.querySelector(\'.faq-question\').textContent.toLowerCase();
                const answer = item.querySelector(\'.faq-answer\').textContent.toLowerCase();

                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = \'block\';
                    if (searchTerm.length > 2) {
                        item.classList.add(\'open\');
                    }
                } else {
                    item.style.display = \'none\';
                }
            });
        });
    }

    // FAQ Accordion Functionality
    const faqQuestions = document.querySelectorAll(\'.faq-question\');
    faqQuestions.forEach(question => {
        question.addEventListener(\'click\', function() {
            const faqItem = this.closest(\'.faq-item\');
            faqItem.classList.toggle(\'open\');
        });
    });
});
</script>
',
'Comprehensive FAQ covering bookings, payments, cancellations, driver requirements, insurance, state-specific regulations, and platform usage for Elite Car Hire',
'published')
ON DUPLICATE KEY UPDATE
    content = VALUES(content),
    meta_description = VALUES(meta_description),
    status = VALUES(status),
    updated_at = CURRENT_TIMESTAMP;
