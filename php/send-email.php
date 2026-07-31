<?php

// Replace this with your own email address
$to = 'desicode.mav@gmail.com';

function url(){
  return sprintf(
    "%s://%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
  );
}

// Strip CR/LF and any header-like content from user input so it can't be used
// to inject extra "From:"/"Bcc:"/etc. headers into the outgoing email
// (classic PHP mail() header injection / spam-relay vulnerability).
function clean_input($value) {
  $value = str_replace(array("\r", "\n", "%0a", "%0d"), '', (string) $value);
  return trim(stripslashes($value));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   $name = clean_input($_POST['name'] ?? '');
   $email = clean_input($_POST['email'] ?? '');
   $subject = clean_input($_POST['subject'] ?? '');
   $contact_message = trim(stripslashes($_POST['message'] ?? ''));

   // Basic required-field and email-format validation
   if ($name === '' || $email === '' || $contact_message === '') {
     echo "Please fill in all required fields.";
     exit;
   }

   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     echo "Please enter a valid email address.";
     exit;
   }

   if ($subject == '') { $subject = "Contact Form Submission"; }

   // Escape values before placing them in an HTML email body
   $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
   $safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
   $safe_message = htmlspecialchars($contact_message, ENT_QUOTES, 'UTF-8');

   // Set Message
   $message = "Email from: " . $safe_name . "<br />";
   $message .= "Email address: " . $safe_email . "<br />";
   $message .= "Message: <br />";
   $message .= nl2br($safe_message);
   $message .= "<br /> ----- <br /> This email was sent from your site " . url() . " contact form. <br />";

   // Set From: header (use the sanitized, newline-stripped name/email only)
   $from = $name . " <" . $email . ">";

   // Email Headers
   $headers = "From: " . $from . "\r\n";
   $headers .= "Reply-To: " . $email . "\r\n";
   $headers .= "MIME-Version: 1.0\r\n";
   $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

   ini_set("sendmail_from", $to); // for windows server
   $mail = mail($to, $subject, $message, $headers);

   if ($mail) { echo "OK"; }
   else { echo "Something went wrong. Please try again."; }

}

?>
