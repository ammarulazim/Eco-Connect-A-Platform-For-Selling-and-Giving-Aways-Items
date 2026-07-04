<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Eco-Connect</title>
    <!-- Boxicons for clean modern icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel='stylesheet'>
    <style>
        :root {
            --primary-color: #2e7d32; /* Eco Green */
            --secondary-color: #4caf50;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark-color);
            background-color: #fff;
            line-height: 1.6;
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 0;
        }

        /* Hero Section */
        .about-hero {
            background: linear-gradient(rgba(46, 125, 50, 0.85), rgba(44, 62, 80, 0.9)), url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1200&q=80') no-repeat center center/cover;
            color: #fff;
            text-align: center;
            padding: 100px 20px;
        }

        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .about-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Introduction Section */
        .intro-section {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .intro-text {
            flex: 1;
        }

        .intro-text h2 {
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 20px;
        }

        .intro-image {
            flex: 1;
            text-align: center;
        }

        .intro-image img {
            width: 100%;
            max-width: 450px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* SDG / Impact Goals Section */
        .sdg-section {
            background-color: var(--light-bg);
            text-align: center;
        }

        .sdg-grid {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .sdg-card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            max-width: 350px;
            border-top: 5px solid var(--primary-color);
        }

        .sdg-card i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        /* Core Functions Section */
        .features-section h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1max));
            gap: 30px;
        }

        .feature-box {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #eef2f3;
            transition: transform 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.1);
        }

        .feature-box i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .feature-box h3 {
            margin: 10px 0;
            font-size: 1.3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .intro-section {
                flex-direction: column;
                text-align: center;
            }
            .about-hero h1 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Header -->
    <header class="about-hero">
        <h1>Eco-Connect</h1>
        <p>A Hyper-Local Community Platform for Selling and Giving Away Items.</p>
    </header>

    <!-- Project Introduction Section -->
    <section class="container intro-section">
        <div class="intro-text">
            <h2>Our Story</h2>
            <p><strong>Eco-Connect</strong> is a neighborhood-focused digital space dedicated to bridging the "clutter gap"—the period when functional items are no longer needed by one household but remain deeply valuable to another.</p>
            <p>Unlike traditional commercial e-commerce sites, Eco-Connect prioritizes community-driven exchange. Operating right here in the <strong>Puchong</strong> area, our platform facilitates a circular economy, simplifying how residents rehome surplus goods, minimize landfill waste, and foster local resource management.</p>
        </div>
        <div class="intro-image">
            <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=500&q=80" alt="Sustainable Community sharing items">
        </div>
    </section>

    <!-- Global Commitments / Vision Section -->
    <section class="sdg-section">
        <div class="container">
            <h2>Driving Sustainability Transformations</h2>
            <p style="color: var(--text-muted); max-width: 700px; margin: 0 auto;">We believe that localized sharing systems build social capital and neighborly trust that large scale commercial web giants simply lack.</p>
            
            <div class="sdg-grid">
                <!-- SDG 11 Card -->
                <div class="sdg-card">
                    <i class='bx bx-building-house'></i>
                    <h3>SDG 11 Alignment</h3>
                    <p><strong>Sustainable Cities & Communities:</strong> Turning a digital platform into a facilitator for real-world, localized social exchange and mutual support systems.</p>
                </div>

                <!-- SDG 12 Card -->
                <div class="sdg-card">
                    <i class='bx bx-recycle'></i>
                    <h3>SDG 12 Alignment</h3>
                    <p><strong>Responsible Consumption & Production:</strong> Lowering the environmental footprint of product manufacturing by optimizing the lifecycle of things we already own.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Platform Functions -->
    <section class="container features-section">
        <h2>What Makes Us Different?</h2>
        <div class="features-grid">
            
            <div class="feature-box">
                <i class='bx bx-git-commit'></i>
                <h3>Giveaways & Sales</h3>
                <p>Support for both selling unused assets and giving items away completely free, separating us from standard marketplaces.</p>
            </div>

            <div class="feature-box">
                <i class='bx bx-map-pin'></i>
                <h3>Hyper-Local Logistics</h3>
                <p>Tailored explicitly for the Puchong neighborhood to handle trust naturally via short travel gaps and peer proximity.</p>
            </div>

            <div class="feature-box">
                <i class='bx bx-message-rounded-dots'></i>
                <h3>Direct Communication</h3>
                <p>No convoluted validation setups. We lean on simplified neighbor-to-neighbor direct messaging tools to manage safe logistics seamlessly.</p>
            </div>

            <div class="feature-box">
                <i class='bx bx-search-alt'></i>
                <h3>Discovery Filtering</h3>
                <p>Advanced item listing, management, and targeted search configurations designed to track down nearby items rapidly.</p>
            </div>

        </div>
    </section>

</body>
</html>