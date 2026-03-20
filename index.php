<?php
require_once __DIR__ . '/includes/functions.php';
$phone = isset($_GET['phone']) ? normalize_phone($_GET['phone']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satya Dev & Sowmya Sri</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="wedding-body">
    <div class="petals">
        <span class="petal p1"></span>
        <span class="petal p2"></span>
        <span class="petal p3"></span>
        <span class="petal p4"></span>
        <span class="petal p5"></span>
        <span class="petal p6"></span>
        <span class="petal p7"></span>
        <span class="petal p8"></span>
    </div>

    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-inner">
            <div class="hero-badge fade-up">Together with our families</div>
            <p class="hero-intro fade-up delay-1">joyfully invite you to celebrate</p>
            <h1 class="couple-names fade-up delay-2">Satya Dev <span>&amp;</span> Sowmya Sri</h1>
            <p class="hero-date fade-up delay-3">Wedding Celebration • May 3, 2026 • Weston, Texas</p>

            <div class="hero-actions fade-up delay-4">
                <a class="btn btn-luxury" href="#rsvp-start">RSVP Now</a>
                <a class="btn btn-outline-light" href="#events">View Events</a>
            </div>

            <div class="countdown-card fade-up delay-5">
                <div class="countdown-item">
                    <div class="countdown-value" id="days">0</div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-value" id="hours">0</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-value" id="minutes">0</div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-value" id="seconds">0</div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>
        </div>
    </section>

    <main class="landing-shell">
        <section id="events" class="landing-section reveal">
            <div class="section-header">
                <p class="eyebrow">Our Celebration</p>
                <h2>Marriage ceremony</h2>
                
            </div>

            <div class="event-grid-landing">
                <article class="timeline-card">
                    <div class="timeline-glow"></div>
                    <p class="timeline-date">May 3, 2026</p>
                    <h3>Marriage</h3>
                    <p>The main wedding ceremony and the moment we begin our new life together.</p>
                </article>
            </div>
        </section>

        <section class="landing-section reveal">
            <div class="split-card">
                <div>
                    <p class="eyebrow">Venue</p>
                    <h2>Join us in Weston, Texas</h2>
                    <p class="venue-address">2425 Weston Rd<br>Weston, TX 75009</p>
                    <p class="section-copy">Tap below for directions and easy navigation on the day of the event.</p>
                    <div class="hero-actions">
                        <a class="btn btn-luxury" target="_blank" rel="noopener" href="https://maps.google.com/?q=2425+Weston+Rd,+Weston,+TX+75009">Open in Google Maps</a>
                    </div>
                </div>
                <div class="map-frame-wrap">
                    <iframe
                        class="venue-map"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q=2425%20Weston%20Rd%20Weston%20TX%2075009&t=&z=13&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
            </div>
        </section>

        <section id="rsvp-start" class="landing-section reveal">
            <div class="section-header">
                <p class="eyebrow">Interactive RSVP</p>
                <h2>Enter your phone number to find your invitation</h2>
                <p class="section-copy">If you came from a text or WhatsApp invite, your phone number may already be filled in.</p>
            </div>

            <div class="rsvp-panel shimmer-panel">
                <form method="post" action="rsvp.php">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?php echo e($phone); ?>" placeholder="8175551234" required>
                    <div class="hero-actions">
                        <button type="submit" class="btn btn-luxury">Find My Invitation</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
        const weddingDate = new Date('2026-05-03T09:00:00-05:00').getTime();
        function updateCountdown() {
            const now = Date.now();
            const diff = Math.max(weddingDate - now, 0);
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((diff / (1000 * 60)) % 60);
            const seconds = Math.floor((diff / 1000) % 60);
            document.getElementById('days').textContent = days;
            document.getElementById('hours').textContent = hours;
            document.getElementById('minutes').textContent = minutes;
            document.getElementById('seconds').textContent = seconds;
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    </script>
</body>
</html>
