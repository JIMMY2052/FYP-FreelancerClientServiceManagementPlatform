<?php
session_start();

$_title = 'Contact Us';
include '../_head.php';
require_once 'config.php';

// Handle inquiry form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inquiry') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } else {
        $conn = getDBConnection();
        $created_at = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO inquiry (Name, Email, Phone, Subject, Message, CreatedAt) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $created_at);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Thank you for your inquiry. We will get back to you soon.';
                $stmt->close();
                $conn->close();
                header('Location: /page/contact_us.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to submit inquiry. Please try again.';
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = 'Database error.';
        }
        $conn->close();
    }
}
?>

<div class="container contact-container">
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with us today.</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="contact-content">
        <!-- Contact Information -->
        <section class="contact-info">
            <h2>Get in Touch</h2>
            
            <div class="info-box">
                <div class="info-icon">📞</div>
                <div class="info-details">
                    <h3>Phone</h3>
                    <p><a href="tel:+60123456789">+60 1 2345 6789</a></p>
                    <p><small>Mon - Fri, 9:00 AM - 6:00 PM</small></p>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">✉️</div>
                <div class="info-details">
                    <h3>Email</h3>
                    <p><a href="mailto:support@worksync.com">support@worksync.com</a></p>
                    <p><small>We'll respond within 24 hours</small></p>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">📍</div>
                <div class="info-details">
                    <h3>Address</h3>
                    <p>WorkSync Tower<br>123 Innovation Street<br>Kuala Lumpur, 50000<br>Malaysia</p>
                </div>
            </div>

            <div class="social-links">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon">f</a>
                    <a href="#" class="social-icon">𝕏</a>
                    <a href="#" class="social-icon">in</a>
                </div>
            </div>
        </section>

        <!-- Inquiry Form -->
        <section class="inquiry-form-section">
            <h2>Send us a Message</h2>
            <form method="post" class="inquiry-form">
                <input type="hidden" name="action" value="inquiry">
                
                <label>Full Name *
                    <input type="text" name="name" required placeholder="Your name">
                </label>

                <label>Email *
                    <input type="email" name="email" required placeholder="your@email.com">
                </label>

                <label>Phone Number
                    <input type="tel" name="phone" placeholder="Your phone number (optional)">
                </label>

                <label>Subject *
                    <input type="text" name="subject" required placeholder="What is this about?">
                </label>

                <label>Message *
                    <textarea name="message" required placeholder="Tell us more..." rows="6"></textarea>
                </label>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </section>
    </div>
</div>

<?php
include '../_foot.php';
?>

<style>
/* Contact Us Page */
.contact-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 16px;
}

.contact-header {
    text-align: center;
    margin-bottom: 50px;
}

.contact-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 12px 0;
}

.contact-header p {
    font-size: 1.1rem;
    color: #666;
    margin: 0;
}

.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}

.contact-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

/* Contact Information Section */
.contact-info {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.contact-info h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 24px 0;
}

.info-box {
    display: flex;
    gap: 16px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 18px;
    transition: all 0.3s ease;
}

.info-box:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
}

.info-icon {
    font-size: 2rem;
    min-width: 50px;
    text-align: center;
}

.info-details h3 {
    margin: 0 0 8px 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
}

.info-details p {
    margin: 6px 0;
    color: #666;
    font-size: 0.95rem;
}

.info-details a {
    color: rgb(159, 232, 112);
    text-decoration: none;
    font-weight: 600;
}

.info-details a:hover {
    text-decoration: underline;
}

.info-details small {
    color: #999;
    font-size: 0.85rem;
}

.social-links {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e9ecef;
}

.social-links h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 12px 0;
}

.social-icons {
    display: flex;
    gap: 12px;
}

.social-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgb(159, 232, 112);
    color: #333;
    border-radius: 50%;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background: rgb(140, 210, 90);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
}

/* Inquiry Form Section */
.inquiry-form-section {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.inquiry-form-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 24px 0;
}

.inquiry-form label {
    display: block;
    margin-bottom: 14px;
    font-weight: 600;
    color: #2c3e50;
}

.inquiry-form input[type="text"],
.inquiry-form input[type="email"],
.inquiry-form input[type="tel"],
.inquiry-form textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #e6e6e6;
    font-size: 0.95rem;
    font-family: inherit;
    transition: all 0.3s ease;
    margin-top: 6px;
}

.inquiry-form input[type="text"]:focus,
.inquiry-form input[type="email"]:focus,
.inquiry-form input[type="tel"]:focus,
.inquiry-form textarea:focus {
    outline: none;
    border-color: rgb(159, 232, 112);
    box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
}

.btn-submit {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 14px 32px;
    border-radius: 20px;
    border: none;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 12px;
}

.btn-submit:hover {
    background: rgb(140, 210, 90);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-content {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .contact-header h1 {
        font-size: 1.8rem;
    }

    .contact-info,
    .inquiry-form-section {
        padding: 20px;
    }

    .info-box {
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .contact-container {
        margin: 24px auto;
    }

    .contact-header h1 {
        font-size: 1.5rem;
    }

    .contact-header p {
        font-size: 0.95rem;
    }

    .contact-info,
    .inquiry-form-section {
        padding: 16px;
    }

    .info-box {
        margin-bottom: 12px;
    }

    .social-icons {
        gap: 8px;
    }

    .social-icon {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
}
</style>

<?php
// Database table for inquiries (run this SQL to create the table)
/*
CREATE TABLE inquiry (
    InquiryID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Phone VARCHAR(20),
    Subject VARCHAR(200) NOT NULL,
    Message TEXT NOT NULL,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(Email),
    INDEX(CreatedAt)
);
*/
?>