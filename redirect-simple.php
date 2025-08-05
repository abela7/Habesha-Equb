<!DOCTYPE html>
<html>
<head>
    <title>Opening HabeshaEqub...</title>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px; 
            background: #F1ECE2;
        }
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            max-width: 400px; 
            margin: 0 auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .logo { 
            font-size: 24px; 
            color: #301934; 
            font-weight: bold; 
            margin-bottom: 20px; 
        }
        .btn { 
            background: #DAA520; 
            color: white; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block;
        }
        .spinner { 
            width: 30px; 
            height: 30px; 
            border: 3px solid #DAA520; 
            border-top: 3px solid transparent; 
            border-radius: 50%; 
            animation: spin 1s linear infinite; 
            margin: 20px auto; 
        }
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏛️ HabeshaEqub</div>
        <div class="spinner"></div>
        <h3>Opening Dashboard...</h3>
        <p>This will open in your browser automatically.</p>
        <a href="https://habeshaequb.com/user/login.php" class="btn" target="_blank" id="manualBtn">
            Click if not opened
        </a>
    </div>

    <script>
        // ULTRA-SIMPLE: Just open the window immediately
        const url = 'https://habeshaequb.com/user/login.php';
        
        // Try 1: Immediate window.open
        window.open(url, '_blank');
        
        // Try 2: Current window redirect after short delay
        setTimeout(() => {
            window.location.href = url;
        }, 1000);
        
        // Try 3: Auto-click the manual button
        setTimeout(() => {
            document.getElementById('manualBtn').click();
        }, 2000);
    </script>
</body>
</html>