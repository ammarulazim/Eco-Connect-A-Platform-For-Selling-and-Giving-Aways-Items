<?php
    include 'database.php';
    $page_title = "About Us | Eco-Connect";
    $page_css = "aboutus.css";
    $page_script = "script2.js";
    include 'header.php';
?>

    <section class="intro-section">
        <div class="container intro-wrapper reveal-item">
            <div class="intro-text">
                <h2>Our Story</h2>
                <p>Eco-Connect is a neighborhood-focused digital space dedicated to bridging the "clutter gap" the period when functional items are no longer needed by one household but remain deeply valuable to another.</p>
                <p>Unlike traditional commercial e-commerce sites, Eco-Connect prioritizes community-driven exchange. Operating in a local community, our platform facilitates a circular economy, simplifying how residents rehome surplus goods, minimize landfill waste, and foster local resource management.</p>
            </div>
            <div class="intro-image">
                <img src="images/logo/ecoconnect.jpg" alt="Sustainable Community sharing items">
            </div>
        </div>
    </section>

    <section class="sdg-section">
        <div class="container sdg-wrapper reveal-item">
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

    <section class="features-section">
        <div class="container reveal-item">
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
        </div>
        
    </section>

    <?php include 'footer.php'; ?>