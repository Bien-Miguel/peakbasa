<?php
// Start session at the very beginning to access session variables
session_start(); 
?>
<!DOCTYPE html>
<html lang="en"></html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PeakBasa - More Info</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body, html {
      height: 100%;
      width: 100%;
      scroll-behavior: smooth;
      overflow-x: hidden;
    }

    /* === Navbar === */
    nav {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: rgba(236, 87, 87, 0.95);
      backdrop-filter: blur(10px);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      height: 70px;
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .nav-logo-img {
      width: 40px; 
      height: auto;
    }

    .nav-logo {
      font-size: 1.2rem;
      font-weight: bold;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 1.5rem;
    }

    nav ul li a {
      text-decoration: none;
      color: white;
      font-weight: 500;
      transition: all 0.3s;
      padding: 0.5rem 1rem;
      border-radius: 20px;
    }

    nav ul li a:hover,
    nav ul li a.active {
      background: rgba(255,255,255,0.2);
    }

    .hamburger-menu {
      display: none;
      font-size: 1.8rem;
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      padding: 0.5rem;
      line-height: 1;
    }

    /* === Sections with Sticky Stacking === */
    .section {
      height: 100vh;
      width: 100%;
      position: sticky;
      top: 0;
      padding-top: 70px;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow-y: auto;
    }

    /* Home Section */
    #home {
      background: linear-gradient(to bottom, #a3d5f7, #e3f2fd);
      z-index: 1;
    }

    /* Features Section */
    #features {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      z-index: 2;
    }

    /* About Section */
    #about {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
      z-index: 3;
    }

    /* Developers Section */
    #developers {
      background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
      color: white;
      z-index: 4;
    }

    /* Contact Section */
    #contact {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      z-index: 5;
    }

    .home-content {
      max-width: 1200px;
      width: 100%;
      text-align: center;
      padding: 2rem;
    }

    .home-content h1 {
      font-size: clamp(2rem, 6vw, 3.5rem);
      color: #222;
      margin-bottom: 1.5rem;
      font-weight: 700;
    }

    .home-content p {
      font-size: clamp(1rem, 3vw, 1.3rem);
      color: #333;
      max-width: 700px;
      margin: 0 auto 2.5rem;
      line-height: 1.6;
    }

    .cta-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .cta-btn {
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      display: inline-block;
    }

    .cta-btn.primary {
      background: #ec5757;
      color: white;
    }

    .cta-btn.primary:hover {
      background: #d84c4c;
      transform: scale(1.05);
      box-shadow: 0 5px 20px rgba(236, 87, 87, 0.3);
    }

    .cta-btn.secondary {
      background: white;
      color: #ec5757;
      border: 2px solid #ec5757;
    }

    .cta-btn.secondary:hover {
      background: #ec5757;
      color: white;
      transform: scale(1.05);
    }

    .features-content {
      max-width: 1200px;
      width: 100%;
      text-align: center;
      padding: 2rem;
      overflow-y: auto;
      max-height: calc(100vh - 70px);
    }

    .features-content h2 {
      font-size: clamp(2rem, 5vw, 3rem);
      margin-bottom: 2rem;
      font-weight: 700;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }

    .feature-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 2rem;
      border-radius: 20px;
      transition: transform 0.3s;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .feature-card:hover {
      transform: translateY(-10px);
      background: rgba(255, 255, 255, 0.15);
    }

    .feature-card h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      font-weight: 600;
    }

    .feature-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
    }

    .about-content {
      max-width: 900px;
      width: 100%;
      text-align: center;
      padding: 2rem;
      overflow-y: auto;
      max-height: calc(100vh - 70px);
    }

    .about-content h2 {
      font-size: clamp(2rem, 5vw, 3rem);
      margin-bottom: 2rem;
      font-weight: 700;
    }

    .about-content p {
      font-size: 1.2rem;
      line-height: 1.8;
      margin-bottom: 1.5rem;
    }

    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }

    .team-member {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 2rem;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .team-member h4 {
      font-size: 1.3rem;
      margin-top: 1rem;
      font-weight: 600;
    }

    .developers-content {
      max-width: 1200px;
      width: 100%;
      text-align: center;
      padding: 2rem;
      overflow-y: auto;
      max-height: calc(100vh - 70px);
    }

    .developers-content h2 {
      font-size: clamp(2rem, 5vw, 3rem);
      margin-bottom: 1rem;
      font-weight: 700;
    }

    .developers-content .subtitle {
      font-size: 1.2rem;
      margin-bottom: 3rem;
      opacity: 0.95;
    }

    .developers-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }

    .developer-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 2.5rem 2rem;
      border-radius: 20px;
      transition: all 0.3s;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .developer-card:hover {
      transform: translateY(-10px);
      background: rgba(255, 255, 255, 0.25);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .developer-icon {
      font-size: 4rem;
      margin-bottom: 1.5rem;
    }

    .developer-card h3 {
      font-size: 1.6rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .developer-role {
      font-size: 1.1rem;
      margin-bottom: 1rem;
      opacity: 0.9;
      font-weight: 500;
    }

    .developer-card p {
      font-size: 1rem;
      line-height: 1.6;
      opacity: 0.95;
    }

    .contact-content {
      max-width: 600px;
      width: 100%;
      text-align: center;
      padding: 2rem;
      overflow-y: auto;
      max-height: calc(100vh - 70px);
    }

    .contact-content h2 {
      font-size: clamp(2rem, 5vw, 3rem);
      margin-bottom: 2rem;
      font-weight: 700;
    }

    .contact-form {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 2.5rem;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-group {
      margin-bottom: 1.5rem;
      text-align: left;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      font-size: 1rem;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 0.9rem;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      background: rgba(255, 255, 255, 0.2);
      color: white;
      font-size: 1rem;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 120px;
      font-family: inherit;
    }

    .btn-submit {
      padding: 1rem 2rem;
      font-size: 1.1rem;
      background: white;
      color: #4facfe;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: 0.3s;
      font-weight: 600;
      width: 100%;
      margin-top: 1rem;
    }

    .btn-submit:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 20px rgba(255, 255, 255, 0.3);
    }

    .feedback-alert {
    padding: 1rem;
    margin-bottom: 1.5rem; /* Space before the form */
    border-radius: 8px;
    font-weight: 500;
    border: 1px solid transparent;
    text-align: center; 
    }
    .alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
    .alert-error { color: #842029; background-color: #f8d7da; border-color: #f5c2c7; }
    .alert-warning { color: #664d03; background-color: #fff3cd; border-color: #ffecb5; }
    .alert-info { color: #055160; background-color: #cff4fc; border-color: #b6effb; }

    /* === MOBILE STYLES === */
    @media (max-width: 768px) {
      nav {
        padding: 0.8rem 1.5rem;
        height: 60px;
      }
      
      .section {
        padding-top: 60px;
        height: 100vh;
      }

      .nav-logo-img {
        width: 35px;
      }
      
      .nav-logo {
        font-size: 1.1rem;
      }

      .hamburger-menu {
        display: block;
      }

      nav ul {
        display: none;
        position: fixed;
        top: 60px;
        left: 0;
        width: 100%;
        background: rgba(236, 87, 87, 0.98);
        flex-direction: column;
        padding: 0;
        gap: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        max-height: calc(100vh - 60px);
        overflow-y: auto;
      }
      
      nav ul.active {
        display: flex;
      }
      
      nav ul li {
        width: 100%;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      }

      nav ul li a {
        font-size: 1.1rem;
        padding: 1.2rem;
        display: block;
        width: 100%;
        border-radius: 0;
      }
      
      nav ul li a.active,
      nav ul li a:hover {
        background: rgba(255,255,255,0.15);
      }

      .home-content,
      .features-content,
      .about-content,
      .developers-content,
      .contact-content {
        padding: 1.5rem;
      }

      .features-content,
      .about-content,
      .developers-content,
      .contact-content {
        max-height: calc(100vh - 60px);
      }

      .home-content h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
      }

      .home-content p {
        font-size: 1rem;
        margin-bottom: 2rem;
      }

      .features-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .developers-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .team-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .contact-form {
        padding: 2rem 1.5rem;
      }

      .cta-buttons {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
      }

      .cta-btn {
        width: 100%;
        padding: 1rem 2rem;
      }

      .about-content p {
        font-size: 1rem;
      }

      .feature-card {
        padding: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .nav-logo-img {
        width: 30px;
      }

      .nav-logo {
        font-size: 1rem;
      }

      .home-content,
      .features-content,
      .about-content,
      .developers-content,
      .contact-content {
        padding: 1rem;
      }

      .contact-form {
        padding: 1.5rem 1rem;
      }

      .form-group input,
      .form-group textarea {
        padding: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div class="nav-left">
      <img src="../ui/Illustration17.png" alt="Logo" class="nav-logo-img">
      <div class="nav-logo">PeakBasa</div>
    </div>
    <ul id="nav-links">
      <li><a href="welcome.php" class="nav-link">Home</a></li>
      <li><a href="#features" class="nav-link">Features</a></li>
      <li><a href="#about" class="nav-link">About</a></li>
      <li><a href="#developers" class="nav-link">Developers</a></li>
      <li><a href="#contact" class="nav-link">Contact</a></li>
    </ul>
    <button class="hamburger-menu" id="hamburger-btn" aria-label="Toggle menu">
      &#9776;
    </button>
  </nav>

  <section id="home" class="section">
    <div class="home-content">
      <h1>Discover PeakBasa</h1>
      <p>Your ultimate platform for climbing to new heights in literacy and education. Join thousands of students and teachers on their journey to success.</p>
      <div class="cta-buttons">
        <a href="register.php?role=student" class="cta-btn primary">Get Started</a>
        <a href="#features" class="cta-btn secondary">Learn More</a>
      </div>
    </div>
  </section>

  <section id="features" class="section">
    <div class="features-content">
      <h2>Amazing Features</h2>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">📚</div>
          <h3>Interactive Learning</h3>
          <p>Engage with dynamic content designed to make learning fun and effective.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🎯</div>
          <h3>Progress Tracking</h3>
          <p>Monitor your growth and achievements as you climb to literacy mastery.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">👥</div>
          <h3>Collaborative Tools</h3>
          <p>Connect with teachers and classmates for a richer learning experience.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🏆</div>
          <h3>Gamification</h3>
          <p>Earn badges and rewards as you complete challenges and reach new heights.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">📊</div>
          <h3>Analytics Dashboard</h3>
          <p>Visualize your learning journey with comprehensive analytics and insights.</p>
        </div>

      </div>
    </div>
  </section>

  <section id="about" class="section">
    <div class="about-content">
      <h2>About PeakBasa</h2>
      <p>PeakBasa is an innovative literacy platform designed to help students and teachers reach new heights in education. Our mission is to make learning accessible, engaging, and rewarding for everyone.</p>
      <p>We believe that every learner has the potential to reach their peak, and we're here to provide the tools and support to make that journey possible.</p>
      <div class="team-grid">
        <div class="team-member">
          <div style="font-size: 3rem;">👨‍💻</div>
          <h4>Developers</h4>
          <p>Building the future of education</p>
        </div>
        <div class="team-member">
          <div style="font-size: 3rem;">🎨</div>
          <h4>Designers</h4>
          <p>Creating beautiful experiences</p>
        </div>
        <div class="team-member">
          <div style="font-size: 3rem;">📖</div>
          <h4>Educators</h4>
          <p>Shaping learning content</p>
        </div>
      </div>
    </div>
  </section>

  <section id="developers" class="section">
    <div class="developers-content">
      <h2>Meet the Developers</h2>
      <p class="subtitle">The talented team behind PeakBasa</p>
      <div class="developers-grid">
        <div class="developer-card">
          <div class="developer-icon">🎨</div>
          <h3>UI/UX Designer</h3>
          <div class="developer-role">Frontend Design Lead</div>
          <p>Crafting beautiful and intuitive user experiences that make learning engaging and accessible for everyone.</p>
        </div>
        <div class="developer-card">
          <div class="developer-icon">⚙️</div>
          <h3>Backend Developer</h3>
          <div class="developer-role">Server Architecture</div>
          <p>Building robust and scalable backend systems that power PeakBasa's learning platform and ensure reliability.</p>
        </div>
        <div class="developer-card">
          <div class="developer-icon">📝</div>
          <h3>Documentation Specialist</h3>
          <div class="developer-role">Technical Writer</div>
          <p>Creating comprehensive documentation and guides to help users and developers make the most of PeakBasa.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="contact" class="section">
    <div class="contact-content">
      <h2>Get In Touch</h2>
      <div class="contact-form">

    <?php 
      if (isset($_SESSION['feedback_message'])) {
          $message = $_SESSION['feedback_message'];
          $type = $_SESSION['feedback_type'] ?? 'info'; 

          $alertClass = 'alert-info'; // Default
          if ($type === 'success') $alertClass = 'alert-success';
          if ($type === 'error') $alertClass = 'alert-error';
          if ($type === 'warning') $alertClass = 'alert-warning';

          echo "<div class='feedback-alert $alertClass'>" . htmlspecialchars($message) . "</div>";

          // Clear the message after displaying it
          unset($_SESSION['feedback_message']);
          unset($_SESSION['feedback_type']);
      }
      ?>

    <form action="contact_submit.php" method="POST">
        <form action="contact_submit.php" method="POST">
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="Your name" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Your message..." required></textarea>
          </div>
          <button type="submit" class="btn-submit">Send Message</button>
        </form>
      </div>
    </div>
  </section>

  <script>
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const navMenu = document.getElementById('nav-links');
    const navMenuLinks = navMenu.querySelectorAll('a');

    // Toggle menu on hamburger click
    hamburgerBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navMenu.classList.toggle('active');
      hamburgerBtn.innerHTML = navMenu.classList.contains('active') ? '&times;' : '&#9776;';
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!navMenu.contains(e.target) && !hamburgerBtn.contains(e.target)) {
        navMenu.classList.remove('active');
        hamburgerBtn.innerHTML = '&#9776;';
      }
    });

    // Close menu when a link is clicked
    navMenuLinks.forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        hamburgerBtn.innerHTML = '&#9776;';
      });
    });

    // Smooth scrolling with offset for fixed navbar
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
          targetElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Active navigation link on scroll
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          const sections = document.querySelectorAll('.section');
          const navLinks = document.querySelectorAll('.nav-link');
          const scrollPosition = window.scrollY + 150;
          
          sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
              navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                  link.classList.add('active');
                }
              });
            }
          });
          
          ticking = false;
        });
        ticking = true;
      }  
    });
  </script>
</body>
</html>