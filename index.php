<?php
session_start();

// Preserve alerts and form state
$alerts = $_SESSION['alerts'] ?? [];
$active_form = $_SESSION['active_form'] ?? '';
$name = $_SESSION['name'] ?? null;

// Clear only alert-related session data
unset($_SESSION['alerts'], $_SESSION['active_form']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore | Premium Computer Solutions</title>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Webstyle.css">
    <style>
        html {
             scroll-behavior: smooth;
        }
        :root {
            --primary: #e35f26;
            --primary-dark: #c14f1f;
            --dark: #222;
            --light: #f9f9f9;
        }
        
        .hero {
            height: 85vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/tech-bg.png') no-repeat center center/cover;
            display: flex;
            align-items: center;
            padding: 0 100px;
            color: #fff;
            position: relative;
        }
        
        .hero-content {
            max-width: 650px;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 30px;
            opacity: 0.9;
            max-width: 90%;
        }
        
        .btn-hero {
            display: inline-block;
            padding: 15px 40px;
            background: var(--primary);
            color: white;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(227, 95, 38, 0.3);
        }
        
        .btn-hero:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 100px 100px;
            background: var(--light);
        }
        
        .feature-card {
            text-align: center;
            padding: 40px 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .feature-icon {
            font-size: 60px;
            color: var(--primary);
            margin-bottom: 25px;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.7;
            font-size: 1.05rem;
        }
        
        .featured-products {
            padding: 100px 100px;
            text-align: center;
            background: #fff;
        }
        
        .section-title {
            font-size: 2.8rem;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            color: var(--dark);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background: var(--primary);
            border-radius: 10px;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
            max-width: 700px;
            margin: 25px auto 50px;
            line-height: 1.6;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .product-img {
            height: 250px;
            overflow: hidden;
            background: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-img img {
            transform: scale(1.1);
        }
        
        .product-info {
            padding: 25px;
            text-align: left;
        }
        
        .product-info h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
            color: var(--dark);
        }
        
        .price {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 20px;
            display: block;
        }
        
        .btn-product {
            display: inline-block;
            padding: 12px 30px;
            background: var(--dark);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-product:hover {
            background: var(--primary);
        }
        
        .testimonials {
            padding: 100px 100px;
            background: var(--light);
            text-align: center;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }
        
        .testimonial-card {
            background: white;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            text-align: left;
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 25px;
            font-size: 5rem;
            color: var(--primary);
            opacity: 0.1;
            font-family: serif;
        }
        
        .testimonial-content {
            margin-bottom: 25px;
            line-height: 1.8;
            color: #555;
            position: relative;
            z-index: 2;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .author-info h4 {
            margin-bottom: 5px;
        }
        
        .author-info p {
            color: #777;
        }
        
        @media (max-width: 1200px) {
            .hero, .features, .featured-products, .testimonials {
                padding: 80px 50px;
            }
            
            .hero h1 {
                font-size: 3rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero {
                padding: 0 30px;
                height: 75vh;
                text-align: center;
            }
            
            .hero p {
                max-width: 100%;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2.3rem;
            }
            
            .features, .featured-products, .testimonials {
                padding: 60px 30px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="#" class="logo">TechStore</a>

        <nav>
            <a href="#" class="active">Home</a>
            <a href="#featured-products">Products</a>
            <a href="purchase.php">Shop</a>
            <a href="#">Support</a>
            <a href="#">Contact</a>
        </nav>

        <div class="user-auth">
            <?php if (!empty($name)): ?>
            <div class="profile-box">
                <div class="avatar-circle"><?php echo strtoupper($name[0]); ?></div>
                <div class="dropdown">
                    <a href="#">My Account</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <?php else: ?>
            <button type="button" class="login-btn-modal">Login</button>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Premium Computers & Tech Solutions</h1>
            <p>Discover high-performance systems tailored for gaming, business, and creative work with unbeatable prices and lifetime support.</p>
            <a href="purchase.php" class="btn-hero">Shop Now</a>
        </div>
    </section>

    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">
                <i class='bx bx-chip'></i>
            </div>
            <h3>Cutting-Edge Hardware</h3>
            <p>Powered by the latest generation processors and graphics cards for maximum performance and efficiency.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i class='bx bx-shield-quarter'></i>
            </div>
            <h3>Lifetime Warranty</h3>
            <p>All systems come with comprehensive lifetime warranty and premium support options.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i class="bx bx-spanner "></i>
            </div>
            <h3>Custom Builds</h3>
            <p>Configure your perfect system with our advanced custom build options and expert consultation.</p>
        </div>
    </section>

    <section class="featured-products" id="featured-products">
        <h2 class="section-title">Featured Systems</h2>
        <p class="section-subtitle">Our most popular configurations chosen by professionals and enthusiasts</p>
        
        <div class="products-grid">
            <div class="product-card">
                <div class="product-img">
                    <img src="images/gaming-pc.png" alt="Gaming PC">
                </div>
                <div class="product-info">
                    <h3>Quantum Gaming Pro</h3>
                    <div class="price">$1,899</div>
                    <a href="#" class="btn-product">View Details</a>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-img">
                    <img src="images/workstation.jpg" alt="Workstation">
                </div>
                <div class="product-info">
                    <h3>Creator Workstation</h3>
                    <div class="price">$2,499</div>
                    <a href="#" class="btn-product">View Details</a>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-img">
                    <img src="images/office-pc.webp" alt="Office PC">
                </div>
                <div class="product-info">
                    <h3>Office Pro Elite</h3>
                    <div class="price">$999</div>
                    <a href="#" class="btn-product">View Details</a>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-img">
                    <img src="images/streaming-pc.webp" alt="Streaming PC">
                </div>
                <div class="product-info">
                    <h3>Streaming Ultimate</h3>
                    <div class="price">$1,599</div>
                    <a href="#" class="btn-product">View Details</a>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <h2 class="section-title">What Our Customers Say</h2>
        <p class="section-subtitle">Join thousands of satisfied customers who trust our products and services</p>
        
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p class="testimonial-content">The Quantum Gaming Pro exceeded all my expectations. The performance is incredible and the cooling system keeps everything running smoothly even during marathon sessions.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">M</div>
                    <div class="author-info">
                        <h4>Michael Rodriguez</h4>
                        <p>Professional Gamer</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <p class="testimonial-content">Our studio upgraded to Creator Workstations and the difference is night and day. Rendering times cut in half and the reliability is unmatched. Worth every penny!</p>
                <div class="testimonial-author">
                    <div class="author-avatar">S</div>
                    <div class="author-info">
                        <h4>Sarah Johnson</h4>
                        <p>Creative Director</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <p class="testimonial-content">TechStore's lifetime warranty gave me the confidence to invest in their systems. When I had an issue, their support team resolved it within 24 hours. Unbeatable service!</p>
                <div class="testimonial-author">
                    <div class="author-avatar">J</div>
                    <div class="author-info">
                        <h4>James Wilson</h4>
                        <p>IT Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($alerts)): ?>
    <div class="alert-box">
        <?php foreach ($alerts as $alert): ?>
        <div class="alert <?php echo $alert['type']; ?>">
            <i class='bx <?php echo $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-error-circle'; ?>'></i>
            <span><?php echo $alert['message']; ?></span> 
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="auth-modal <?php echo $active_form === 'register' ? 'show slide' : ($active_form === 'login' ? 'show' : ''); ?>">
        <button type="button" class="close-btn-modal"><i class='bx bx-x'></i></button>
        <div class="form-box login">
            <h2>Login</h2>
            <form action="auth_process.php" method="POST">
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i> 
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-keyhole'></i> 
                </div>
                <button type="submit" name="login_btn" class="btn">Login</button>
                <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
            </form>
        </div>

        <div class="form-box register">
            <h2>Register</h2>
            <form action="auth_process.php" method="POST">
                <div class="input-box">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <i class='bx bxs-user'></i>  
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i> 
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-keyhole'></i> 
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <i class='bx bxs-lock-keyhole'></i> 
                </div>
                <button type="submit" name="register_btn" class="btn">Register</button>
                <p>Already have an account? <a href="#" class="login-link">Login</a></p>
            </form>
        </div>
    </div>
    
    <script src="Webscript.js"></script>
    <script>
        // Enhanced animations
        document.addEventListener('DOMContentLoaded', function() {
            // Feature card animations
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-10px)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
            
            // Product card animations
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-15px)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
            
            // Scroll animations
            const observerOptions = {
                threshold: 0.1
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.feature-card, .product-card, .testimonial-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>