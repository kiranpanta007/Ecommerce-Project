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
    <title>About Us - MyShop</title>
    <style>
        /* Scoped Styles for About Page Content */
        main.about-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }

        main.about-container h1 {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 40px;
            font-weight: 700;
        }

        main.about-container section {
            background-color: #ffffff;
            padding: 30px 40px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        main.about-container section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        main.about-container section h2 {
            font-size: 28px;
            color: #007BFF;
            margin-bottom: 15px;
            position: relative;
        }

        main.about-container section h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            margin-top: 8px;
        }

        main.about-container section p {
            font-size: 18px;
            color: black;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        main.about-container ul {
            list-style-type: none;
            padding: 0;
        }

        main.about-container ul li {
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        main.about-container ul li::before {
            color: #007BFF;
            margin-right: 10px;
            font-size: 18px;
        }

        main.about-container .team-img {
            display: block;
            margin: 20px auto;
            max-width: 200px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            main.about-container section {
                padding: 20px;
            }

            main.about-container h1 {
                font-size: 28px;
            }

            main.about-container section h2 {
                font-size: 24px;
            }

            main.about-container section p, 
            main.about-container ul li {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; // Include navbar ?>

<main class="about-container">
    <h1>About Us</h1>

    <section>
        <h2>Our Story</h2>
        <p>Welcome to Mero Shopping! We are a dedicated team committed to providing the best shopping experience. Our mission is to offer high-quality products at affordable prices while ensuring exceptional customer service.</p>
        <p>Since our inception, we’ve focused on innovation and customer satisfaction, continuously improving our services and product selection to meet your needs.</p>
    </section>

    <section>
        <h2>Our Values</h2>
        <ul>
            <li><strong>Quality:</strong> We source only the finest products for our customers.</li>
            <li><strong>Customer Satisfaction:</strong>Your happiness is our top priority.</li>
            <li><strong>Innovation:</strong>We constantly adapt to meet your evolving needs.</li>
            <li><strong>Integrity:</strong>Transparency and honesty in all we do.</li>
        </ul>
    </section>

    <section>
        <h2>Meet the Team</h2>
        <p>Our dedicated team of professionals works tirelessly to ensure your shopping experience is seamless and enjoyable. We are passionate about providing top-notch customer service and quality products.</p>
    </section>
</main>

<?php include 'includes/footer.php'; // Include footer ?>

</body>
</html>
