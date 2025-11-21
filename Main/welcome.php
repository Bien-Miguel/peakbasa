<?php
session_start();

// --- 1. Connect to the database ---
// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PeakBasa</title>
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
      overflow: hidden;
    }

    /* Splash screen */
    #splash {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      width: 100%;
      background: #ec5757;
      color: #fff;  
      flex-direction: column;
      text-align: center;
      padding: 1rem;
    }

    .logo {
      font-size: clamp(1.8rem, 5vw, 2.5rem);
      font-weight: bold;
      animation: bounce 1.5s infinite;
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-20px); }
      60% { transform: translateY(-10px); }
    }

    .loading-text {
      margin-top: 1rem;
      font-size: clamp(0.9rem, 3vw, 1.2rem);
      opacity: 0.8;
      animation: blink 1.2s infinite;
    }

    @keyframes blink {
      0% { opacity: 0.2; }
      50% { opacity: 1; }
      100% { opacity: 0.2; }
    }

    /* === Navbar === */
    nav {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: rgba(236, 87, 87, 0.95);
      backdrop-filter: blur(10px);
      color: white;
      padding: 0.7rem 2rem;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 10px;
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

    nav ul li a:hover {
      background: rgba(255,255,255,0.2);
    }

    /* === Main content === */
    #main {
      display: none;
      justify-content: center;
      align-items: center;  
      height: 100vh;
      width: 100%;
      padding: 6rem 2rem 2rem;
      gap: 4rem;
    }

    .main-img img {
      max-width: 350px;
      height: auto;
      filter: drop-shadow(5px 5px 10px rgba(0, 0, 0, 0.3));
    }

    .main-text h1 {
      font-size: clamp(1.5rem, 5vw, 2.5rem);
      margin-bottom: 1rem;
      color: #333;
    }

    .main-text {
      max-width: 400px;
      text-align: left;
      position: relative;
      z-index: 5;
    }

    .btn-start {
      padding: 0.9rem 2rem;
      font-size: 1.1rem;
      background: #ec5757; 
      border: none;
      border-radius: 25px;
      color: white;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-start:hover {
      background: #d84c4c;
      transform: scale(1.05);
    }

    .main-img {
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .main-img img {
      max-width: 350px;
      height: auto;
      display: block;
    }

    .main-img .mountain {
      position: absolute;
      top: 55%;
      left: 54%;
      transform: translateX(-50%);
      z-index: 2;
      max-width: 100%;
      width: 300%;   
      max-width: none;
    }

    .main-img .illustration {
      position: relative;
      z-index: 1;
    }

    /* === Modal Styles === */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from { 
        opacity: 0;
        transform: translateY(50px);
      }
      to { 
        opacity: 1;
        transform: translateY(0);
      }
    }

    .modal-content {
      background: #fff;
      margin: 10% auto;
      padding: 40px;
      border-radius: 20px;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
      text-align: center;
      animation: slideUp 0.4s;
      position: relative;
    }

    .close-btn {
      position: absolute;
      right: 20px;
      top: 20px;
      font-size: 28px;
      font-weight: bold;
      color: #999;
      cursor: pointer;
      transition: 0.3s;
      line-height: 1;
    }

    .close-btn:hover {
      color: #ec5757;
    }

    .modal-content h2 {
      color: #ec5757;
      margin-bottom: 30px;
      font-size: 1.8rem;
    }

    .role-btn {
      display: block;
      width: 85%;
      margin: 15px auto;
      padding: 18px;
      background: #ec5757;
      color: white;
      border-radius: 12px;
      text-decoration: none;
      font-size: 1.15rem;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
    }

    .role-btn:hover {
      background: #c04161;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(236, 87, 87, 0.3);
    }

    @media (max-width: 768px) {
      #main {
        flex-direction: column;
        text-align: center;
        justify-content: center;
        align-items: center;
        gap: 2rem;
      }

      .main-text {
        text-align: center;
        order: 1;
      }

      .main-img {
        order: 2;
      }

      .btn-start {
        width: 80%;
      }

      nav {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 0.7rem 1rem;
      }

      nav ul {
        flex-direction: row;
        gap: 0.6rem;
        margin-left: 0.5rem;
      }

      nav ul li a {
        font-size: 0.8rem;
      }

      .nav-left img {
        width: 35px;
      }

      .nav-logo {
        font-size: 0.9rem;
      }

      .modal-content {
        margin: 20% auto; 
        padding: 30px;
      }
    }
  </style>
</head>
<body>
  <!-- Splash screen -->
  <div id="splash">
    <img src="../ui/Illustration17.png" style="max-width: 100%; height: auto; width: 200px; filter: drop-shadow(5px 5px 10px rgba(0, 0, 0, 0.8));" class="logo" >
    <div class="logo">PeakBasa</div>
    <div class="loading-text">Loading...</div>
  </div>

  <!-- Navbar -->
  <nav id="navbar">
    <div class="nav-left">
      <img src="../ui/Illustration17.png" alt="Logo" style="width:50px; height:auto;"/>
      <div class="nav-logo">PeakBasa</div>
    </div>
    <ul>
      <li><a href="moreinfo.php">More Info</a></li>
    </ul>
  </nav>

  <!-- Main content -->
  <div id="main">
    <div class="main-img">
      <img src="../ui/3.png" alt="Mountain" class="mountain">
      <img src="../ui/2.png" alt="Illustration" class="illustration">
    </div>
    <div class="main-text"> 
      <h1>Welcome to PeakBasa!</h1>
      <p style="margin-bottom: 15px;">Climb your way to the peak of literacy.</p>
      <button class="btn-start" onclick="openRoleModal()">Start Learning</button>
    </div>
  </div>

  <!-- Role Selection Modal -->
  <div id="roleModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeRoleModal()">&times;</span>
      <h2>Choose Your Role</h2>
      <a href="../Verification/register.php?role=student" class="role-btn">👩‍🎓 Register as Student</a>
      <a href="../Verification/teacher_register.php?role=teacher" class="role-btn">👨‍🏫 Register as Teacher</a>
    </div>
  </div>

  <script>
  // Modal functions
  function openRoleModal() {
    document.getElementById('roleModal').style.display = 'block';
  }

  function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('roleModal');
    if (event.target == modal) {
      closeRoleModal();
    }
  }

  // Close modal on ESC key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeRoleModal();
    }
  });

  window.addEventListener("load", () => {
    setTimeout(() => {
      document.getElementById("splash").style.display = "none";
      document.getElementById("main").style.display = "flex";
      document.getElementById("navbar").style.display = "flex";
    }, 3000);

    function lerpColor(a, b, amount) {
      const ah = parseInt(a.replace('#', ''), 16),
            ar = ah >> 16, ag = ah >> 8 & 0xff, ab = ah & 0xff,
            bh = parseInt(b.replace('#', ''), 16),
            br = bh >> 16, bg = bh >> 8 & 0xff, bb = bh & 0xff,
            rr = Math.round(ar + amount * (br - ar)),
            rg = Math.round(ag + amount * (bg - ag)),
            rb = Math.round(ab + amount * (bb - ab));
      return '#' + (((1 << 24) + (rr << 16) + (rg << 8) + rb).toString(16).slice(1));
    }

    const bgPhases = [
      ["#a3d5f7", "#e3f2fd"],
      ["#ffd59e", "#ffe6b3"],
      ["#f9a07f", "#fbc4ab"],
      ["#1c2541", "#3a506b"],
    ];

    const textPhases = [
      "#222222",
      "#333333",
      "#333333",
      "#f1f1f1"
    ];

    let start = null;
    const duration = 30000;

    function animate(time) {
      if (!start) start = time;
      const progress = ((time - start) % duration) / duration;
      const phase = Math.floor(progress * bgPhases.length);
      const nextPhase = (phase + 1) % bgPhases.length;
      const localProgress = (progress * bgPhases.length) % 1;

      const c1 = lerpColor(bgPhases[phase][0], bgPhases[nextPhase][0], localProgress);
      const c2 = lerpColor(bgPhases[phase][1], bgPhases[nextPhase][1], localProgress);
      document.body.style.background = `linear-gradient(to top, ${c1}, ${c2})`;

      const tColor = lerpColor(textPhases[phase], textPhases[nextPhase], localProgress);
      document.querySelector(".main-text h1").style.color = tColor;
      document.querySelector(".main-text p").style.color = tColor;

      requestAnimationFrame(animate);
    }

    requestAnimationFrame(animate);
  });
</script>
</body>
</html>