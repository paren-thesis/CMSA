<?php
/**
 * Password Creation Utility for CMS
 * 
 * This utility helps create hashed passwords for the Church Management System.
 * Useful for free hosting environments where password_hash() might not work properly.
 * 
 * Usage: 
 * 1. Run this file in your browser
 * 2. Enter the desired password
 * 3. Copy the generated hash to your database
 * 4. Delete this file after use for security
 */

// Prevent direct access if not needed
if (!isset($_POST['create_password'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Creation Utility - CMS</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 30px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #555;
            }
            input[type="password"], input[type="text"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                box-sizing: border-box;
            }
            button {
                background-color: #007bff;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                width: 100%;
            }
            button:hover {
                background-color: #0056b3;
            }
            .result {
                margin-top: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                word-break: break-all;
            }
            .warning {
                background-color: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .success {
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 15px;
                border-radius: 4px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔐 Password Creation Utility</h1>
            
            <div class="warning">
                <strong>⚠️ Security Warning:</strong> This utility is for creating passwords only. 
                Delete this file after use to prevent unauthorized access.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">Enter Password:</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter the password you want to hash">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm the password">
                </div>
                
                <button type="submit" name="create_password">Generate Hash</button>
            </form>
            
            <div class="success">
                <strong>Instructions:</strong>
                <ol>
                    <li>Enter the password you want to create</li>
                    <li>Click "Generate Hash"</li>
                    <li>Copy the generated hash</li>
                    <li>Use it in your database or login system</li>
                    <li>Delete this file for security</li>
                </ol>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process password creation
if ($_POST['create_password']) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = "Password cannot be empty.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Try to create hash using password_hash first
        $hash = null;
        $method = '';
        
        if (function_exists('password_hash')) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $method = 'password_hash() (PHP built-in)';
            } catch (Exception $e) {
                // Fallback to manual hashing
            }
        }
        
        // Fallback to manual hashing if password_hash fails
        if (!$hash) {
            $hash = hash('sha256', $password);
            $method = 'SHA-256 (manual fallback)';
        }
        
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Creation Utility - CMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            word-break: break-all;
        }
        .copy-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .copy-btn:hover {
            background-color: #218838;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Creation Utility</h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
            <a href="create_password.php" class="back-btn">Try Again</a>
        <?php elseif (isset($success)): ?>
            <div class="success">
                <strong>✅ Password hash generated successfully!</strong>
            </div>
            
            <div class="result">
                <strong>Hash Method:</strong> <?php echo htmlspecialchars($method); ?><br><br>
                <strong>Generated Hash:</strong><br>
                <code id="hashOutput"><?php echo htmlspecialchars($hash); ?></code><br>
                <button class="copy-btn" onclick="copyHash()">Copy Hash</button>
            </div>
            
            <div class="success">
                <strong>Next Steps:</strong>
                <ol>
                    <li>Copy the hash above</li>
                    <li>Use it in your database or login system</li>
                    <li>Test the login with your password</li>
                    <li><strong>Delete this file for security!</strong></li>
                </ol>
            </div>
            
            <a href="create_password.php" class="back-btn">Create Another Password</a>
        <?php endif; ?>
    </div>
    
    <script>
        function copyHash() {
            const hashText = document.getElementById('hashOutput').textContent;
            navigator.clipboard.writeText(hashText).then(function() {
                alert('Hash copied to clipboard!');
            }).catch(function(err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = hashText;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Hash copied to clipboard!');
            });
        }
    </script>
</body>
</html> 