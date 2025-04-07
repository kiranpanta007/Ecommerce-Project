<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; // Include database connection if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MyShop</title>
    <style>
        /* Scoped Styles for Contact Page Content */
        main.contact-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }

        main.contact-container h1 {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 40px;
            font-weight: 700;
        }

        main.contact-container section {
            background-color: #ffffff;
            padding: 30px 40px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        main.contact-container section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        main.contact-container section h2 {
            font-size: 28px;
            color: #007BFF;
            margin-bottom: 15px;
            position: relative;
        }

        main.contact-container section h2::after {
            display: block;
            width: 60px;
            height: 4px;
            background-color: #007BFF;
            margin-top: 8px;
        }

        main.contact-container p {
            font-size: 18px;
            color: black;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        main.contact-container ul {
            list-style-type: none;
            padding: 0;
        }

        main.contact-container ul li {
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        main.contact-container ul li::before {
            content: '📞';
            margin-right: 10px;
            color: #007BFF;
            font-size: 18px;
        }

        main.contact-container form div {
            margin-bottom: 20px;
        }

        main.contact-container label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        main.contact-container input,
        main.contact-container textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            resize: vertical;
        }

        main.contact-container button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        main.contact-container button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            main.contact-container section {
                padding: 20px;
            }

            main.contact-container h1 {
                font-size: 28px;
            }

            main.contact-container section h2 {
                font-size: 24px;
            }

            main.contact-container p,
            main.contact-container ul li,
            main.contact-container label,
            main.contact-container input,
            main.contact-container textarea,
            main.contact-container button {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; // Include navbar ?>

<main class="contact-container">
    <h1>Contact Us</h1>

    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="success-message">'. $_SESSION['success'] .'</div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo '<div class="error-message">'. $_SESSION['error'] .'</div>';
        unset($_SESSION['error']);
    }
    ?>

    <section>
        <h2>Get in Touch</h2>
        <p>We'd love to hear from you! Whether you have a question, feedback, or just want to say hello, feel free to reach out to us.</p>
    </section>

    <section>
        <h2>Contact Information</h2>
        <ul>
            <li><strong>Email:</strong> support@meroshopping.com</li>
            <li><strong>Phone:</strong> +1 (123) 456-7890</li>
            <li><strong>Address:</strong> PipalChowk, Bharatpur, Nepal</li>
        </ul>
    </section>

    <section>
        <h2>Send Us a Message</h2>
        <form action="submit_contact.php" method="POST">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit">Send Message</button>
        </form>
    </section>
</main>

<?php include 'includes/footer.php'; // Include footer ?>

</body>
</html>
