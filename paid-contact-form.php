<?php
/*
Plugin Name: Paid Contact Form
Description: A contact form that requires PayPal payment to be submitted. Is this script useful? Donate via PayPal: alex@alexandermirvisc.com or CashApp/Venmo: $LynxGeekNYC 
Version: 1.2
Author: Alexander Mirvis
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!session_id()) {
    session_start();
}

// Enqueue PayPal SDK and custom script
function paid_contact_form_enqueue_scripts() {
    wp_enqueue_script('paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID', array(), null, true);
    wp_enqueue_script('paid-contact-form', plugins_url('paid-contact-form.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'paid_contact_form_enqueue_scripts');

// Create the form shortcode
function paid_contact_form_shortcode() {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $num1 + $num2;

    ob_start(); ?>
    <form id="paid-contact-form" method="post" action="">
        <label for="name">Name</label>
        <input type="text" name="name" required><br>
        <label for="email">Email</label>
        <input type="email" name="email" required><br>
        <label for="phone">Phone</label>
        <input type="text" name="phone" required><br>
        <label for="message">Message</label>
        <textarea name="message" required></textarea><br>
        <label for="captcha">What is <?php echo $num1; ?> + <?php echo $num2; ?>?</label>
        <input type="text" name="captcha" required><br>
        <input type="hidden" name="payment_status" id="payment_status" value="unpaid">
        <div id="paypal-button-container"></div>
        <button type="submit" id="submit-button" disabled>Send Message</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('paid_contact_form', 'paid_contact_form_shortcode');

// Handle form submission
function handle_paid_contact_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_status']) && $_POST['payment_status'] === 'paid') {
        $captcha = intval($_POST['captcha']);
        if ($captcha !== $_SESSION['captcha_answer']) {
            echo '<p>Incorrect CAPTCHA answer. Please try again.</p>';
            return;
        }

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $message = sanitize_textarea_field($_POST['message']);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo '<p>Invalid email address. Please enter a valid email.</p>';
            return;
        }

        // Send the email
        $to = get_option('admin_email');
        $subject = 'New Contact Form Message';
        $headers = "From: $name <$email>";
        $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";

        wp_mail($to, $subject, $body, $headers);

        echo '<p>Thank you for your message! We will get back to you soon.</p>';
    }
}
add_action('wp', 'handle_paid_contact_form_submission');
