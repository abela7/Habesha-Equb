<?php
/**
 * ULTRA-SIMPLE Force Browser Redirect
 * Immediately opens habeshaequb.com in browser
 */

// Always redirect to main site - no parameters needed for security
$target_url = 'https://habeshaequb.com/user/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opening HabeshaEqub Dashboard...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #F1ECE2 0%, #E8DCC6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .container {
            background: white;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(48, 25, 52, 0.15);
            text-align: center;
            max-width: 450px;
            width: 90%;
            position: relative;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #301934;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #301934, #DAA520);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .spinner-container {
            margin: 30px 0;
            position: relative;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(218, 165, 32, 0.2);
            border-top: 4px solid #DAA520;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status {
            font-size: 20px;
            color: #301934;
            font-weight: 600;
            margin: 25px 0 15px 0;
        }
        
        .subtitle {
            color: #5D4225;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 30px;
        }
        
        .manual-btn {
            background: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.3);
        }
        
        .manual-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 165, 32, 0.4);
        }
        
        .progress {
            width: 100%;
            height: 4px;
            background: rgba(218, 165, 32, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #DAA520, #CDAF56);
            width: 0%;
            animation: progress 3s ease-in-out;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        
        .success-icon {
            font-size: 24px;
            margin-right: 10px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-5px); }
            60% { transform: translateY(-3px); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏛️ HabeshaEqub</div>
        
        <div class="spinner-container">
            <div class="spinner"></div>
        </div>
        
        <div class="status">
            <span class="success-icon">🚀</span>
            Opening Dashboard...
        </div>
        
        <div class="subtitle">
            Your browser should open automatically.<br>
            If not, click the button below.
        </div>
        
        <a href="<?php echo htmlspecialchars($target_url, ENT_QUOTES, 'UTF-8'); ?>" 
           class="manual-btn" 
           target="_blank" 
           id="manualButton">
            Open Dashboard Manually
        </a>
        
        <div class="progress">
            <div class="progress-bar"></div>
        </div>
    </div>

    <script>
        // ULTRA-AGGRESSIVE browser forcing methods
        const targetUrl = 'https://habeshaequb.com/user/login.php';
        
        // Method 1: IMMEDIATE window.open (no delay)
        try {
            const newWindow = window.open(targetUrl, '_blank', 'noopener,noreferrer,width=1200,height=800');
            if (newWindow) {
                newWindow.focus();
                console.log('✅ New window opened successfully!');
                document.querySelector('.status').innerHTML = '✅ Opening in your browser...';
                document.querySelector('.subtitle').innerHTML = 'You can close this tab now.';
            }
        } catch (e) {
            console.log('Method 1 failed:', e);
        }
        
        // Method 2: IMMEDIATE location change (aggressive)
        setTimeout(() => {
            try {
                window.location.href = targetUrl;
                console.log('✅ Redirecting current window...');
            } catch (e) {
                console.log('Method 2 failed:', e);
            }
        }, 100);
        
        // Method 3: Create and auto-click link (bypass popup blockers)
        setTimeout(() => {
            try {
                const link = document.createElement('a');
                link.href = targetUrl;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                console.log('✅ Auto-clicked link created!');
            } catch (e) {
                console.log('Method 3 failed:', e);
            }
        }, 200);
        
        // Method 4: Force current window navigation (ultimate fallback)
        setTimeout(() => {
            try {
                window.location.replace(targetUrl);
                console.log('✅ Force replacing current window...');
            } catch (e) {
                console.log('Method 4 failed:', e);
            }
        }, 500);
        
        // Auto-click manual button as backup
        setTimeout(() => {
            const button = document.getElementById('manualButton');
            if (button) {
                button.click();
                console.log('✅ Auto-clicked manual button!');
            }
        }, 1000);
        
        // Manual button click handler
        document.addEventListener('DOMContentLoaded', function() {
            const button = document.getElementById('manualButton');
            if (button) {
                button.addEventListener('click', function() {
                    window.open(targetUrl, '_blank');
                    document.querySelector('.status').innerHTML = '✅ Opening dashboard...';
                });
            }
        });
    </script>
</body>
</html>