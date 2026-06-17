<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <style>
        html, body {
            background: #e8f5e9; 
        }
        html[data-theme="dark"], html[data-theme="dark"] body {
            background: #0f1412 !important; 
        }
    </style>

    <title>Academic Record System</title>
    <link rel="stylesheet" href="style.css">
    </head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academic Record System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        :root {
            --bg-base: #e8f5e9;
            --surface-card: rgba(255, 255, 255, 0.4);
            --border-card: rgba(45, 106, 79, 0.1);
            --shadow-card: 0 32px 64px rgba(27, 67, 50, 0.08), inset 0 1px 1px rgba(255, 255, 255, 0.6);
            --text-title: #1b4332;
            --text-subtitle: #2d6a4f;
            --text-muted: #52b788;
            --input-bg: rgba(255, 255, 255, 0.65);
            --input-border: rgba(45, 106, 79, 0.15);
            --input-text: #1b4332;
            --input-placeholder: #74c69d;
            --primary-start: #2d6a4f;
            --primary-end: #40916c;
            --primary-shadow: rgba(45, 106, 79, 0.15);
            --primary-hover-start: #1b4332;
            --primary-hover-end: #2d6a4f;
            --primary-hover-shadow: rgba(45, 106, 79, 0.25);
            --ambient-1: radial-gradient(circle, #52b788 0%, rgba(232, 245, 233, 0) 70%);
            --ambient-2: radial-gradient(circle, #74c69d 0%, rgba(232, 245, 233, 0) 70%);
            --banner-bg: rgba(239, 68, 68, 0.08);
            --banner-text: #dc2626;
            --banner-border: rgba(239, 68, 68, 0.15);
            --mode-btn-bg: rgba(45, 106, 79, 0.08);
            --mode-btn-border: rgba(45, 106, 79, 0.12);
            --mode-btn-text: #2d6a4f;
            --logo-border: rgba(45, 106, 79, 0.15);
        }

        [data-theme="dark"] {
            --bg-base: #0f1412;
            --surface-card: rgba(24, 34, 30, 0.45);
            --border-card: rgba(255, 255, 255, 0.06);
            --shadow-card: 0 32px 64px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.05);
            --text-title: #ffffff;
            --text-subtitle: #74c69d;
            --text-muted: #94a3b8;
            --input-bg: rgba(15, 23, 42, 0.4);
            --input-border: rgba(255, 255, 255, 0.08);
            --input-text: #ffffff;
            --input-placeholder: rgba(255, 255, 255, 0.25);
            --primary-start: #40916c;
            --primary-end: #52b788;
            --primary-shadow: rgba(82, 183, 136, 0.15);
            --primary-hover-start: #52b788;
            --primary-hover-end: #74c69d;
            --primary-hover-shadow: rgba(82, 183, 136, 0.3);
            --ambient-1: radial-gradient(circle, rgba(45, 106, 79, 0.35) 0%, rgba(15, 20, 18, 0) 70%);
            --ambient-2: radial-gradient(circle, rgba(82, 183, 136, 0.25) 0%, rgba(15, 20, 18, 0) 70%);
            --banner-bg: rgba(239, 68, 68, 0.1);
            --banner-text: #f87171;
            --banner-border: rgba(239, 68, 68, 0.2);
            --mode-btn-bg: rgba(255, 255, 255, 0.04);
            --mode-btn-border: rgba(255, 255, 255, 0.08);
            --mode-btn-text: #74c69d;
            --logo-border: rgba(255, 255, 255, 0.15);
        }

        body {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: var(--bg-base);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ambient-glow-1 {
            position: absolute;
            width: 800px;
            height: 800px;
            background: var(--ambient-1);
            top: -25%;
            left: -15%;
            z-index: 1;
            opacity: 0.6;
            transition: background 0.4s, opacity 0.4s;
        }

        [data-theme="dark"] .ambient-glow-1 {
            opacity: 1;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 900px;
            height: 900px;
            background: var(--ambient-2);
            bottom: -30%;
            right: -15%;
            z-index: 1;
            opacity: 0.6;
            transition: background 0.4s, opacity 0.4s;
        }

        [data-theme="dark"] .ambient-glow-2 {
            opacity: 1;
        }

        .theme-switcher {
            position: absolute;
            top: 24px;
            right: 24px;
            z-index: 10;
        }

        .theme-toggle-btn {
            background: var(--mode-btn-bg);
            border: 1px solid var(--mode-btn-border);
            color: var(--mode-btn-text);
            padding: 10px 16px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .theme-toggle-btn:hover {
            transform: translateY(-1px);
        }

        .login-wrapper {
            position: relative;
            z-index: 3;
            width: 100%;
            max-width: 440px;
            padding: 24px;
        }

        .login-card {
            background: var(--surface-card);
            backdrop-filter: blur(30px) saturate(170%);
            -webkit-backdrop-filter: blur(30px) saturate(170%);
            border-radius: 24px;
            padding: 44px 40px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-card);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-container {
            width: 88px;
            height: 88px;
            margin: 0 auto 20px auto;
            border-radius: 50%;
            border: 2px solid var(--logo-border);
            padding: 2px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: transparent;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .login-card h2 {
            color: var(--text-title);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
            transition: color 0.4s;
        }

        .login-card p.subtitle {
            color: var(--text-subtitle);
            font-size: 13px;
            margin-bottom: 36px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color 0.4s;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            color: var(--text-title);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            padding-left: 2px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 16px;
            font-size: 14.5px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            color: var(--input-text);
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-group input::placeholder {
            color: var(--input-placeholder);
        }

        .input-group input:focus {
            border-color: var(--text-subtitle);
            box-shadow: 0 0 0 4px rgba(82, 183, 136, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--primary-shadow);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary-hover-start) 0%, var(--primary-hover-end) 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px var(--primary-hover-shadow);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .error-banner {
            background: var(--banner-bg);
            color: var(--banner-text);
            border: 1px solid var(--banner-border);
            padding: 14px;
            border-radius: 12px;
            font-size: 13.5px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
            transition: all 0.4s;
        }

        .input-error {
            border-color: rgba(239, 68, 68, 0.4) !important;
            background: rgba(239, 68, 68, 0.04) !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15) !important;
        }

        .footer-note {
            margin-top: 32px;
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="theme-switcher">
        <button class="theme-toggle-btn" id="themeToggle">
            <span id="themeText">Light Mode</span>
        </button>
    </div>

    <div class="login-wrapper">
        <form class="login-card" id="loginForm" method="POST" action="authenticate.php" novalidate>
            <div class="logo-container">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ-_Nssj-vBA66Npb16JJfkH129sz0OyrrrhQ&s" alt="Timoteo Paez Integrated School Seal">
            </div>

            <h2>T-PAEZ Record System</h2>
            <p class="subtitle">Timoteo Paez Integrated School</p>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-banner">
                    Invalid username or password. Please try again.
                </div>
            <?php endif; ?>
            
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-login">Sign In</button>

            <div class="footer-note">
                Official Portal &bull; K-to-12 Grading Matrix Framework<br>
                Secured Academic Database Infrastructure
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const themeToggle = document.getElementById('themeToggle');
            const themeText = document.getElementById('themeText');

            const currentTheme = localStorage.getItem('theme') || 'light';
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                themeText.textContent = 'Dark Mode';
            }

            themeToggle.addEventListener('click', () => {
                let theme = 'light';
                if (document.documentElement.getAttribute('data-theme') !== 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    themeText.textContent = 'Dark Mode';
                    theme = 'dark';
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    themeText.textContent = 'Light Mode';
                }
                localStorage.setItem('theme', theme);
            });

            loginForm.addEventListener('submit', (e) => {
                let isValid = true;

                if (usernameInput.value.trim() === '') {
                    usernameInput.value = '';
                    usernameInput.classList.add('input-error');
                    isValid = false;
                } else {
                    usernameInput.classList.remove('input-error');
                }

                if (passwordInput.value.trim() === '') {
                    passwordInput.value = '';
                    passwordInput.classList.add('input-error');
                    isValid = false;
                } else {
                    passwordInput.classList.remove('input-error');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            const clearStatus = (e) => {
                if (e.target.value.trim() !== '') {
                    e.target.classList.remove('input-error');
                }
            };

            usernameInput.addEventListener('input', clearStatus);
            passwordInput.addEventListener('input', clearStatus);
        });
    </script>
</body>
</html>