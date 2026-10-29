<?php
require_once __DIR__ . '/api/lib/mailer.php';

$result = send_email(
    'conscious@gmail.com', // Replace with your real personal email so you can check your inbox!
    'Test Email from Conscious India',
    '<h1>It works!</h1><p>This email was sent using the Resend integration.</p>'
);

if ($result) {
    echo "Email sent successfully! Check your inbox.";
} else {
    echo "Failed to send email. Check the error log.";
}