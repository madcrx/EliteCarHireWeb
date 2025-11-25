-- Update Terms of Service and Privacy Policy for Elite Car Hire
-- Run this on your live database

-- Update Terms of Service
UPDATE cms_pages SET content = '
<div class="legal-content">
    <h1>Terms of Service</h1>
    <p><strong>Last Updated:</strong> November 2025</p>
    <p>Welcome to Elite Car Hire. These Terms of Service ("Terms") govern your use of our platform and services. By accessing or using Elite Car Hire, you agree to be bound by these Terms.</p>

    <h2>1. About Elite Car Hire</h2>
    <p>Elite Car Hire is a booking service platform that connects vehicle owners ("Owners") with customers ("Hirers") seeking luxury, vintage, classic, and specialty vehicle hire services in Melbourne and surrounding areas. We facilitate bookings but are not responsible for the vehicles or services provided by Owners.</p>

    <h2>2. Definitions</h2>
    <ul>
        <li><strong>"Platform"</strong> refers to the Elite Car Hire website and associated services</li>
        <li><strong>"Services"</strong> means the booking facilitation and platform services we provide</li>
        <li><strong>"Owner"</strong> means a registered user who lists vehicles for hire</li>
        <li><strong>"Hirer"</strong> means a registered user who books vehicles through our platform</li>
        <li><strong>"Booking"</strong> means a confirmed reservation for vehicle hire</li>
    </ul>

    <h2>3. User Accounts and Registration</h2>
    <h3>3.1 Account Creation</h3>
    <p>To use our Services, you must create an account and provide accurate, complete information. You are responsible for maintaining the confidentiality of your account credentials.</p>

    <h3>3.2 Account Requirements</h3>
    <p>You must be at least 21 years old and hold a valid Australian driver''s license to hire vehicles. Owners must be at least 18 years old and provide proof of vehicle ownership and insurance.</p>

    <h3>3.3 Account Security</h3>
    <p>You are responsible for all activities that occur under your account. Notify us immediately of any unauthorized use.</p>

    <h2>4. Booking Process</h2>
    <h3>4.1 Making a Booking</h3>
    <p>Bookings are subject to vehicle availability. When you make a booking request:</p>
    <ul>
        <li>You submit a binding offer to hire the vehicle</li>
        <li>The Owner has the right to accept or decline your request</li>
        <li>Once accepted, a legally binding agreement is formed between you and the Owner</li>
        <li>Elite Car Hire facilitates payment processing but is not party to the rental agreement</li>
    </ul>

    <h3>4.2 Booking Confirmation</h3>
    <p>You will receive email confirmation once your booking is confirmed. This confirmation contains important details about your hire period, pickup/delivery arrangements, and terms.</p>

    <h3>4.3 Cancellations and Modifications</h3>
    <ul>
        <li><strong>Hirer Cancellations:</strong> Cancel more than 48 hours before pickup for a full refund minus processing fees. Cancellations within 48 hours are subject to a 50% cancellation fee.</li>
        <li><strong>Owner Cancellations:</strong> If an Owner cancels a confirmed booking, you will receive a full refund and we will assist in finding alternative arrangements.</li>
        <li><strong>Modifications:</strong> Changes to booking dates or times are subject to vehicle availability and Owner approval.</li>
    </ul>

    <h2>5. Payment Terms</h2>
    <h3>5.1 Pricing</h3>
    <p>All prices are displayed in Australian Dollars (AUD) and include GST where applicable. The total hire cost includes the vehicle hourly/daily rate, any additional fees, and our service fee.</p>

    <h3>5.2 Payment Processing</h3>
    <p>Payment is processed securely through our platform. We hold the payment and release it to the Owner after successful completion of the hire period.</p>

    <h3>5.3 Security Deposits</h3>
    <p>Owners may require a security deposit (bond) which is held separately and returned after the vehicle is returned in satisfactory condition.</p>

    <h2>6. Driver Requirements and Responsibilities</h2>
    <h3>6.1 License Requirements</h3>
    <p>All drivers must:</p>
    <ul>
        <li>Hold a current, valid Australian driver''s license (or acceptable international license)</li>
        <li>Be at least 21 years of age (25 for some premium vehicles)</li>
        <li>Provide license verification before vehicle pickup</li>
    </ul>

    <h3>6.2 Driver Responsibilities</h3>
    <p>As a Hirer, you agree to:</p>
    <ul>
        <li>Operate the vehicle safely, lawfully, and in accordance with all traffic regulations</li>
        <li>Not drive under the influence of alcohol or drugs</li>
        <li>Not allow unauthorized persons to drive the vehicle</li>
        <li>Not use the vehicle for illegal purposes, racing, or commercial activities unless specifically authorized</li>
        <li>Return the vehicle in the same condition as received, normal wear and tear excepted</li>
        <li>Pay for any traffic fines, tolls, or parking violations incurred during the hire period</li>
    </ul>

    <h2>7. Insurance and Liability</h2>
    <h3>7.1 Owner Insurance</h3>
    <p>All vehicles listed on our platform must maintain current comprehensive insurance. Owners are responsible for ensuring adequate insurance coverage.</p>

    <h3>7.2 Hirer Liability</h3>
    <p>You are responsible for any damage to the vehicle during the hire period, including:</p>
    <ul>
        <li>Accident damage (subject to insurance excess)</li>
        <li>Interior damage or excessive cleaning requirements</li>
        <li>Mechanical damage resulting from misuse</li>
        <li>Loss or damage to vehicle accessories</li>
    </ul>

    <h3>7.3 Incident Reporting</h3>
    <p>Any accidents, damage, or mechanical issues must be reported to the Owner and Elite Car Hire immediately.</p>

    <h2>8. Vehicle Condition and Inspection</h2>
    <h3>8.1 Pre-Hire Inspection</h3>
    <p>Before accepting a vehicle, conduct a thorough inspection and document any existing damage. Report discrepancies immediately.</p>

    <h3>8.2 Return Condition</h3>
    <p>Vehicles must be returned clean, with the same fuel level, and in the same condition as received (normal wear excepted). Additional fees may apply for excessive cleaning or refueling.</p>

    <h2>9. Owner Obligations</h2>
    <h3>9.1 Vehicle Standards</h3>
    <p>Owners must ensure vehicles are:</p>
    <ul>
        <li>Registered, roadworthy, and legally compliant</li>
        <li>Clean, maintained, and safe to operate</li>
        <li>Adequately insured for hire purposes</li>
        <li>Accurately described in listings</li>
    </ul>

    <h3>9.2 Availability</h3>
    <p>Owners must maintain accurate availability calendars and honor confirmed bookings.</p>

    <h2>10. Prohibited Activities</h2>
    <p>The following activities are strictly prohibited:</p>
    <ul>
        <li>Providing false or misleading information</li>
        <li>Using the platform for fraudulent purposes</li>
        <li>Harassing or threatening other users</li>
        <li>Violating any applicable laws or regulations</li>
        <li>Attempting to circumvent our platform or payment processing</li>
        <li>Uploading malicious code or interfering with platform functionality</li>
    </ul>

    <h2>11. Dispute Resolution</h2>
    <h3>11.1 Platform Mediation</h3>
    <p>If disputes arise between Hirers and Owners, Elite Car Hire will attempt to mediate in good faith. However, we are not obligated to resolve disputes between users.</p>

    <h3>11.2 Damage Claims</h3>
    <p>Damage claims must be submitted with photographic evidence within 24 hours of vehicle return. We reserve the right to verify claims and facilitate fair resolution.</p>

    <h2>12. Privacy and Data Protection</h2>
    <p>Your use of our Services is subject to our Privacy Policy. We collect, use, and protect your personal information in accordance with Australian privacy laws.</p>

    <h2>13. Intellectual Property</h2>
    <p>All content on our platform, including text, graphics, logos, and software, is owned by Elite Car Hire or our licensors and protected by copyright and trademark laws.</p>

    <h2>14. Limitation of Liability</h2>
    <h3>14.1 Platform Role</h3>
    <p>Elite Car Hire is a booking facilitation platform. We do not own, operate, or control the vehicles listed on our platform. All bookings create a direct contractual relationship between the Hirer and Owner.</p>

    <h3>14.2 Liability Cap</h3>
    <p>To the maximum extent permitted by law, our total liability for any claim arising from use of our Services is limited to the service fees paid by you for the specific booking in question.</p>

    <h3>14.3 Exclusions</h3>
    <p>We are not liable for:</p>
    <ul>
        <li>Vehicle condition, safety, or suitability</li>
        <li>Owner or Hirer conduct</li>
        <li>Accidents, injuries, or property damage during hire periods</li>
        <li>Indirect, consequential, or punitive damages</li>
        <li>Service interruptions or technical issues</li>
    </ul>

    <h2>15. Indemnification</h2>
    <p>You agree to indemnify and hold Elite Car Hire harmless from any claims, losses, or damages arising from your use of our Services, violation of these Terms, or infringement of any third-party rights.</p>

    <h2>16. Termination</h2>
    <p>We reserve the right to suspend or terminate your account at our discretion if you violate these Terms or engage in fraudulent or harmful conduct. You may close your account at any time, subject to completing any pending bookings.</p>

    <h2>17. Modifications to Terms</h2>
    <p>We may update these Terms periodically. Continued use of our Services after changes constitutes acceptance of the modified Terms. We will notify you of significant changes via email or platform notification.</p>

    <h2>18. Governing Law</h2>
    <p>These Terms are governed by the laws of Victoria, Australia. Any disputes will be subject to the exclusive jurisdiction of the courts of Victoria.</p>

    <h2>19. Contact Information</h2>
    <p>For questions about these Terms, contact us at:</p>
    <p>
        <strong>Elite Car Hire</strong><br>
        Phone: 0406 907 849<br>
        Email: support@elitecarhire.au<br>
        Address: Melbourne, Victoria, Australia
    </p>

    <h2>20. Severability</h2>
    <p>If any provision of these Terms is found to be unenforceable, the remaining provisions will remain in full effect.</p>

    <p><em>By using Elite Car Hire, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</em></p>
</div>
', updated_at = NOW() WHERE page_key = 'terms';

-- Update Privacy Policy
UPDATE cms_pages SET content = '
<div class="legal-content">
    <h1>Privacy Policy</h1>
    <p><strong>Last Updated:</strong> November 2025</p>
    <p>Elite Car Hire ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your personal information when you use our platform and services.</p>

    <h2>1. Information We Collect</h2>
    <h3>1.1 Personal Information You Provide</h3>
    <p>We collect information you provide directly, including:</p>
    <ul>
        <li><strong>Account Information:</strong> Name, email address, phone number, date of birth, postal address</li>
        <li><strong>Driver Information:</strong> Driver''s license number, license expiry date, license issuing state/country</li>
        <li><strong>Payment Information:</strong> Credit/debit card details, billing address (processed securely through third-party payment processors)</li>
        <li><strong>Vehicle Owner Information:</strong> Vehicle registration details, insurance information, bank account details for payments</li>
        <li><strong>Communications:</strong> Messages, reviews, and feedback you provide through our platform</li>
    </ul>

    <h3>1.2 Information Collected Automatically</h3>
    <p>When you use our platform, we automatically collect:</p>
    <ul>
        <li><strong>Device Information:</strong> IP address, browser type, operating system, device identifiers</li>
        <li><strong>Usage Data:</strong> Pages visited, time spent on pages, click patterns, search queries</li>
        <li><strong>Location Data:</strong> Approximate geographic location based on IP address</li>
        <li><strong>Cookies and Similar Technologies:</strong> We use cookies to enhance your experience and analyze platform usage</li>
    </ul>

    <h3>1.3 Information from Third Parties</h3>
    <p>We may receive information from:</p>
    <ul>
        <li>Payment processors regarding transaction status</li>
        <li>Identity verification services for security purposes</li>
        <li>Public databases to verify license and registration information</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>We use your personal information to:</p>
    <ul>
        <li><strong>Provide Services:</strong> Facilitate bookings, process payments, and manage your account</li>
        <li><strong>Communications:</strong> Send booking confirmations, updates, and customer support responses</li>
        <li><strong>Safety and Security:</strong> Verify identities, prevent fraud, and ensure platform integrity</li>
        <li><strong>Improvement:</strong> Analyze usage patterns to improve our platform and services</li>
        <li><strong>Marketing:</strong> Send promotional communications about new features or vehicles (you can opt out anytime)</li>
        <li><strong>Legal Compliance:</strong> Comply with legal obligations and enforce our Terms of Service</li>
        <li><strong>Dispute Resolution:</strong> Resolve disputes between users and investigate violations</li>
    </ul>

    <h2>3. Information Sharing and Disclosure</h2>
    <h3>3.1 With Other Users</h3>
    <p>When you make or accept a booking:</p>
    <ul>
        <li>Hirers see Owner''s name, vehicle details, and general location</li>
        <li>Owners see Hirer''s name, contact information, and driver''s license details</li>
        <li>Reviews and ratings are publicly visible</li>
    </ul>

    <h3>3.2 With Service Providers</h3>
    <p>We share information with third-party service providers who assist us with:</p>
    <ul>
        <li>Payment processing (secure payment gateways)</li>
        <li>Email communications and notifications</li>
        <li>Website hosting and maintenance</li>
        <li>Analytics and platform improvement</li>
        <li>Identity and license verification services</li>
    </ul>
    <p>These providers are contractually obligated to protect your information and use it only for specified purposes.</p>

    <h3>3.3 For Legal Reasons</h3>
    <p>We may disclose your information if required by law or if we believe disclosure is necessary to:</p>
    <ul>
        <li>Comply with legal processes or government requests</li>
        <li>Enforce our Terms of Service and other agreements</li>
        <li>Protect the rights, property, or safety of Elite Car Hire, our users, or the public</li>
        <li>Investigate fraud or security issues</li>
    </ul>

    <h3>3.4 Business Transfers</h3>
    <p>If Elite Car Hire is involved in a merger, acquisition, or asset sale, your information may be transferred. We will notify you of any such change.</p>

    <h3>3.5 With Your Consent</h3>
    <p>We may share your information for other purposes with your explicit consent.</p>

    <h2>4. Data Security</h2>
    <p>We implement appropriate technical and organizational measures to protect your personal information, including:</p>
    <ul>
        <li>Encryption of sensitive data in transit and at rest</li>
        <li>Secure authentication and access controls</li>
        <li>Regular security assessments and updates</li>
        <li>Employee training on data protection</li>
        <li>Incident response procedures</li>
    </ul>
    <p>However, no method of transmission over the Internet is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.</p>

    <h2>5. Data Retention</h2>
    <p>We retain your personal information for as long as necessary to:</p>
    <ul>
        <li>Provide our Services and maintain your account</li>
        <li>Comply with legal, tax, and accounting obligations</li>
        <li>Resolve disputes and enforce our agreements</li>
        <li>Meet legitimate business purposes</li>
    </ul>
    <p>When information is no longer needed, we securely delete or anonymize it. Booking records and financial information are retained for 7 years to comply with Australian tax laws.</p>

    <h2>6. Your Rights and Choices</h2>
    <h3>6.1 Access and Update</h3>
    <p>You can access and update your account information through your account dashboard. Contact us if you need assistance.</p>

    <h3>6.2 Data Portability</h3>
    <p>You have the right to request a copy of your personal information in a structured, commonly used format.</p>

    <h3>6.3 Deletion</h3>
    <p>You can request deletion of your account and personal information. Note that we may retain certain information as required by law or for legitimate business purposes.</p>

    <h3>6.4 Marketing Communications</h3>
    <p>You can opt out of promotional emails by clicking "unsubscribe" in any marketing email or adjusting your account preferences. Transactional emails (booking confirmations, account notifications) cannot be opted out of while you use our Services.</p>

    <h3>6.5 Cookies</h3>
    <p>Most browsers allow you to control cookies through settings. Disabling cookies may limit platform functionality.</p>

    <h2>7. Cookies and Tracking Technologies</h2>
    <p>We use cookies and similar technologies to:</p>
    <ul>
        <li><strong>Essential Cookies:</strong> Required for platform functionality (e.g., maintaining your session)</li>
        <li><strong>Performance Cookies:</strong> Help us understand how you use our platform to improve performance</li>
        <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
        <li><strong>Advertising Cookies:</strong> Deliver relevant advertisements (with your consent)</li>
    </ul>
    <p>You can manage cookie preferences through your browser settings.</p>

    <h2>8. Third-Party Links</h2>
    <p>Our platform may contain links to third-party websites or services. We are not responsible for the privacy practices of these external sites. We encourage you to review their privacy policies.</p>

    <h2>9. Children''s Privacy</h2>
    <p>Our Services are not intended for individuals under 18 years of age. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p>

    <h2>10. International Data Transfers</h2>
    <p>Your information is primarily stored and processed in Australia. If we transfer data internationally, we ensure appropriate safeguards are in place to protect your information in accordance with Australian privacy laws.</p>

    <h2>11. Changes to This Privacy Policy</h2>
    <p>We may update this Privacy Policy periodically to reflect changes in our practices or legal requirements. We will notify you of significant changes via email or platform notification. The "Last Updated" date at the top indicates when the policy was last revised.</p>

    <h2>12. Australian Privacy Principles</h2>
    <p>Elite Car Hire complies with the Australian Privacy Principles (APPs) contained in the Privacy Act 1988 (Cth). We are committed to handling your personal information transparently and responsibly.</p>

    <h2>13. Contact Us</h2>
    <p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:</p>
    <p>
        <strong>Elite Car Hire - Privacy Officer</strong><br>
        Phone: 0406 907 849<br>
        Email: support@elitecarhire.au<br>
        Address: Melbourne, Victoria, Australia
    </p>

    <h2>14. Complaints</h2>
    <p>If you believe we have breached the Australian Privacy Principles, you may lodge a complaint with us. We will investigate and respond within 30 days. If you are not satisfied with our response, you may contact the Office of the Australian Information Commissioner (OAIC):</p>
    <p>
        Website: <a href="https://www.oaic.gov.au" target="_blank">www.oaic.gov.au</a><br>
        Phone: 1300 363 992<br>
        Email: enquiries@oaic.gov.au
    </p>

    <p><em>By using Elite Car Hire, you acknowledge that you have read and understood this Privacy Policy and consent to the collection, use, and disclosure of your personal information as described herein.</em></p>
</div>
', updated_at = NOW() WHERE page_key = 'privacy';
