Wedding RSVP Portal (PHP + MySQL + Twilio)

What this includes:
- Guest lookup by phone number
- 3-event RSVP flow
- Admin dashboard to add/edit guests
- Admin can assign events per guest
- Admin can send SMS or WhatsApp invite using Twilio
- CSV export

Setup steps:
1. Upload all files inside this folder to public_html in Hostinger.
2. Create a MySQL database and user in cPanel.
3. Import schema.sql using phpMyAdmin.
4. Update config/db.php with your database credentials.
5. Update includes/messaging.php with your Twilio values:
   - TWILIO_ACCOUNT_SID
   - TWILIO_AUTH_TOKEN
   - TWILIO_SMS_FROM
   - TWILIO_WHATSAPP_FROM
6. Create an admin password hash.

Create admin password hash:
- Create a temporary file named make_hash.php with this content:
  <?php echo password_hash('YourStrongPassword123!', PASSWORD_DEFAULT); ?>
- Open it once in browser and copy the output.
- Insert admin user in phpMyAdmin:
  INSERT INTO admin_users (username, password_hash)
  VALUES ('admin', 'PASTE_HASH_HERE');
- Delete make_hash.php after that.

Admin login:
- /admin/login.php

Guest RSVP page:
- /rsvp.php?phone=8175551234

Twilio notes:
- SMS should work with a Twilio SMS-capable number.
- WhatsApp needs a Twilio WhatsApp sender. The sandbox can be used for testing.
- For production WhatsApp business-initiated notifications, Twilio/WhatsApp may require approved templates depending on your setup.

Suggested first test:
1. Add admin user.
2. Log in to admin dashboard.
3. Add one guest and select events.
4. Click Send SMS.
5. Open the RSVP link.
