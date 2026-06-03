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
    <title>Login - Academic Record System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        :root {
            --bg-base: #f4f7f6;
            --surface-card: rgba(255, 255, 255, 0.85);
            --border-card: rgba(45, 106, 79, 0.08);
            --shadow-card: 0 24px 48px rgba(31, 49, 39, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            --text-title: #1b4332;
            --text-subtitle: #52b788;
            --text-muted: #64748b;
            --input-bg: rgba(255, 255, 255, 0.9);
            --input-border: #d8e2dc;
            --input-text: #1e293b;
            --input-placeholder: #94a3b8;
            --primary-start: #2d6a4f;
            --primary-end: #40916c;
            --primary-shadow: rgba(45, 106, 79, 0.15);
            --primary-hover-start: #1b4332;
            --primary-hover-end: #2d6a4f;
            --primary-hover-shadow: rgba(45, 106, 79, 0.25);
            --ambient-1: radial-gradient(circle, rgba(82, 183, 136, 0.15) 0%, rgba(0,0,0,0) 70%);
            --ambient-2: radial-gradient(circle, rgba(116, 198, 157, 0.12) 0%, rgba(0,0,0,0) 70%);
            --banner-bg: rgba(239, 68, 68, 0.06);
            --banner-text: #dc2626;
            --banner-border: rgba(239, 68, 68, 0.15);
            --mode-btn-bg: rgba(45, 106, 79, 0.06);
            --mode-btn-border: rgba(45, 106, 79, 0.1);
            --mode-btn-text: #2d6a4f;
        }

        [data-theme="dark"] {
            --bg-base: #0f1412;
            --surface-card: rgba(24, 34, 30, 0.7);
            --border-card: rgba(255, 255, 255, 0.04);
            --shadow-card: 0 32px 64px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.05);
            --text-title: #ffffff;
            --text-subtitle: #74c69d;
            --text-muted: #94a3b8;
            --input-bg: rgba(255, 255, 255, 0.02);
            --input-border: rgba(255, 255, 255, 0.08);
            --input-text: #ffffff;
            --input-placeholder: rgba(255, 255, 255, 0.25);
            --primary-start: #40916c;
            --primary-end: #52b788;
            --primary-shadow: rgba(82, 183, 136, 0.15);
            --primary-hover-start: #52b788;
            --primary-hover-end: #74c69d;
            --primary-hover-shadow: rgba(82, 183, 136, 0.3);
            --ambient-1: radial-gradient(circle, rgba(45, 106, 79, 0.25) 0%, rgba(0,0,0,0) 70%);
            --ambient-2: radial-gradient(circle, rgba(82, 183, 136, 0.15) 0%, rgba(0,0,0,0) 70%);
            --banner-bg: rgba(239, 68, 68, 0.1);
            --banner-text: #f87171;
            --banner-border: rgba(239, 68, 68, 0.2);
            --mode-btn-bg: rgba(255, 255, 255, 0.04);
            --mode-btn-border: rgba(255, 255, 255, 0.08);
            --mode-btn-text: #74c69d;
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
            width: 600px;
            height: 600px;
            background: var(--ambient-1);
            top: -15%;
            left: -10%;
            z-index: 1;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 700px;
            height: 700px;
            background: var(--ambient-2);
            bottom: -25%;
            right: -10%;
            z-index: 1;
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
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-card);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-card h2 {
            color: var(--text-title);
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            transition: color 0.4s;
        }

        .login-card p.subtitle {
            color: var(--text-subtitle);
            font-size: 14px;
            margin-bottom: 38px;
            font-weight: 500;
            transition: color 0.4s;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group input {
            width: 100%;
            padding: 16px 18px;
            font-size: 15px;
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
            border-color: #40916c;
            box-shadow: 0 0 0 4px rgba(64, 145, 108, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
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
            <h2>Welcome back</h2>
            <p class="subtitle">Academic Record System</p>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-banner">
                    Invalid username or password. Please try again.
                </div>
            <?php endif; ?>
            
            <div class="input-group">
                <input type="text" id="username" name="username" placeholder="Username" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
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
                    usernameInput.classList.add('input-error');
                    isValid = false;
                } else {
                    usernameInput.classList.remove('input-error');
                }

                if (passwordInput.value.trim() === '') {
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