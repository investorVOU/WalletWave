<?php
session_start();
include_once '../includes/db.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Initialize variables
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Get admin user from database
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log("Database error in admin login: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CryptoFund</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">
    <style>
        .admin-login-bg {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }
    </style>
</head>
<body class="admin-login-bg min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-flex items-center justify-center space-x-2">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 w-12 h-12 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cube text-white text-2xl"></i>
                </div>
                <span class="text-3xl font-bold text-white">CryptoFund</span>
            </a>
            <h1 class="text-xl text-gray-300 mt-2">Admin Portal</h1>
        </div>
        
        <!-- Login Form -->
        <div class="bg-slate-800/50 backdrop-blur-lg rounded-xl shadow-2xl border border-slate-700 p-8">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">Admin Login</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                    <p><i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-6">
                    <label for="username" class="block text-gray-300 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-user"></i>
                        </span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 px-4 pl-10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            required
                        >
                    </div>
                </div>
                
                <div class="mb-8">
                    <label for="password" class="block text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 px-4 pl-10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg shadow-lg transition duration-300"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i> Log In
                </button>
            </form>
        </div>
        
        <!-- Back to site link -->
        <div class="text-center mt-8">
            <a href="../index.php" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to CryptoFund
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="mt-auto text-center text-gray-500 text-sm py-4">
        &copy; <?php echo date('Y'); ?> CryptoFund Admin Portal. All rights reserved.
    </div>
</body>
</html>