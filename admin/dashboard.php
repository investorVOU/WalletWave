<?php
session_start();
include_once '../includes/db.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$pendingCampaigns = [];
$activeTab = 'pending'; // Default active tab

// Process campaign approval/rejection if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['campaign_id'])) {
        $action = $_POST['action'];
        $campaignId = (int)$_POST['campaign_id'];
        
        try {
            if ($action === 'approve') {
                // Approve campaign
                $stmt = $pdo->prepare("UPDATE campaigns SET status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $message = 'Campaign approved successfully.';
            } else if ($action === 'reject') {
                // Reject campaign
                $stmt = $pdo->prepare("UPDATE campaigns SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $message = 'Campaign rejected.';
            }
            
            if (isset($stmt)) {
                $stmt->execute([$campaignId]);
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => $message
                ];
            }
        } catch (PDOException $e) {
            error_log("Database error in campaign approval: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'text' => 'A system error occurred. Please try again.'
            ];
        }
        
        // Redirect to avoid form resubmission
        header('Location: dashboard.php');
        exit;
    }
}

// Get flash message if exists
$flashMessage = null;
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch pending campaigns
try {
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE status = 'pending' ORDER BY created_at DESC");
    $stmt->execute();
    $pendingCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch approved campaigns
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE status = 'approved' ORDER BY created_at DESC");
    $stmt->execute();
    $approvedCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch rejected campaigns
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE status = 'rejected' ORDER BY created_at DESC");
    $stmt->execute();
    $rejectedCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process campaign data for display
    $allCampaigns = array_merge($pendingCampaigns, $approvedCampaigns, $rejectedCampaigns);
    foreach ($allCampaigns as &$campaign) {
        // Calculate days left based on end_date
        $endDate = new DateTime($campaign['end_date']);
        $now = new DateTime();
        $interval = $now->diff($endDate);
        $campaign['days_left'] = $interval->days;
        
        // Set creator info
        if (!isset($campaign['creator_name'])) {
            $campaign['creator_name'] = 'Anonymous';
        }
        if (!isset($campaign['creator_wallet'])) {
            $campaign['creator_wallet'] = 'No wallet';
        }
        
        // Set image URL from thumbnail_url
        $campaign['image_url'] = $campaign['thumbnail_url'];
        
        // Set target amount from funding_goal
        $campaign['target_amount'] = $campaign['funding_goal'];
        
        // Set duration as days between start and end date
        $startDate = new DateTime($campaign['start_date']);
        $duration = $startDate->diff($endDate);
        $campaign['duration'] = $duration->days;
    }
    
} catch (PDOException $e) {
    error_log("Database error fetching campaigns: " . $e->getMessage());
    $error = 'A system error occurred while fetching campaigns. Please try again later.';
}

// Handle tab switching
if (isset($_GET['tab'])) {
    $activeTab = $_GET['tab'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CryptoFund</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">
    <style>
        .admin-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
    </style>
</head>
<body class="admin-bg min-h-screen flex flex-col">
    <!-- Admin Header -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="dashboard.php" class="flex items-center space-x-2">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 w-8 h-8 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cube text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-white">CryptoFund Admin</span>
                </a>
                
                <!-- Admin Controls -->
                <div class="flex items-center space-x-4">
                    <span class="text-gray-300">
                        <i class="fas fa-user-shield text-blue-400 mr-2"></i>
                        <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    </span>
                    <a href="logout.php" class="bg-red-600/20 text-red-400 hover:bg-red-600/30 px-3 py-1 rounded-lg text-sm font-medium transition duration-200">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Campaign Management</h1>
                <p class="text-gray-400">Review, approve, or reject submitted campaigns</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="../index.php" class="inline-flex items-center text-blue-400 hover:text-blue-300 transition">
                    <i class="fas fa-external-link-alt mr-2"></i> View Public Site
                </a>
            </div>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-600/20 border border-green-600 text-green-400' : 'bg-red-600/20 border border-red-600 text-red-400'; ?>">
                <p>
                    <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($flashMessage['text']); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <div class="border-b border-slate-700 mb-8">
            <nav class="flex space-x-4" aria-label="Tabs">
                <a href="?tab=pending" class="<?php echo $activeTab === 'pending' ? 'border-b-2 border-blue-500 text-blue-400' : 'text-gray-400 hover:text-gray-300'; ?> px-3 py-2 text-sm font-medium">
                    <i class="fas fa-clock mr-2"></i> Pending 
                    <span class="bg-yellow-600/30 text-yellow-400 text-xs px-2 py-0.5 rounded-full ml-1"><?php echo count($pendingCampaigns); ?></span>
                </a>
                <a href="?tab=approved" class="<?php echo $activeTab === 'approved' ? 'border-b-2 border-blue-500 text-blue-400' : 'text-gray-400 hover:text-gray-300'; ?> px-3 py-2 text-sm font-medium">
                    <i class="fas fa-check-circle mr-2"></i> Approved
                    <span class="bg-green-600/30 text-green-400 text-xs px-2 py-0.5 rounded-full ml-1"><?php echo count($approvedCampaigns); ?></span>
                </a>
                <a href="?tab=rejected" class="<?php echo $activeTab === 'rejected' ? 'border-b-2 border-blue-500 text-blue-400' : 'text-gray-400 hover:text-gray-300'; ?> px-3 py-2 text-sm font-medium">
                    <i class="fas fa-times-circle mr-2"></i> Rejected
                    <span class="bg-red-600/30 text-red-400 text-xs px-2 py-0.5 rounded-full ml-1"><?php echo count($rejectedCampaigns); ?></span>
                </a>
            </nav>
        </div>
        
        <!-- Campaign Table -->
        <div class="bg-slate-800/30 backdrop-blur-lg border border-slate-700 rounded-xl overflow-hidden shadow-lg">
            <?php 
            $campaigns = $activeTab === 'pending' ? $pendingCampaigns : ($activeTab === 'approved' ? $approvedCampaigns : $rejectedCampaigns);
            
            if (empty($campaigns)): 
            ?>
                <div class="p-8 text-center">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas <?php echo $activeTab === 'pending' ? 'fa-inbox' : ($activeTab === 'approved' ? 'fa-clipboard-check' : 'fa-ban'); ?>"></i>
                    </div>
                    <h3 class="text-xl font-medium text-white mb-2">No <?php echo ucfirst($activeTab); ?> Campaigns</h3>
                    <p class="text-gray-400">
                        <?php 
                        if ($activeTab === 'pending') {
                            echo 'There are no campaigns waiting for approval.';
                        } else if ($activeTab === 'approved') {
                            echo 'You haven\'t approved any campaigns yet.';
                        } else {
                            echo 'You haven\'t rejected any campaigns yet.';
                        }
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Campaign
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Creator
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Target
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Submitted
                                </th>
                                <?php if ($activeTab === 'pending'): ?>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700 bg-slate-800/20">
                            <?php foreach ($campaigns as $campaign): ?>
                            <tr class="hover:bg-slate-700/30 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if (!empty($campaign['image_url'])): ?>
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-lg object-cover" src="<?php echo htmlspecialchars($campaign['image_url']); ?>" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                        </div>
                                        <?php else: ?>
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-700 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-500"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-white">
                                                <?php echo htmlspecialchars($campaign['title']); ?>
                                            </div>
                                            <div class="text-sm text-gray-400">
                                                <?php echo mb_substr(htmlspecialchars($campaign['description']), 0, 50) . (mb_strlen($campaign['description']) > 50 ? '...' : ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white"><?php echo htmlspecialchars($campaign['creator_name'] ?? 'Anonymous'); ?></div>
                                    <div class="text-sm text-gray-400"><?php echo htmlspecialchars($campaign['creator_wallet'] ?? 'No wallet'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-blue-400 font-medium"><?php echo htmlspecialchars($campaign['target_amount']); ?> ETH</div>
                                    <div class="text-xs text-gray-400"><?php echo htmlspecialchars($campaign['duration']); ?> days duration</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    <?php echo date('M j, Y', strtotime($campaign['created_at'])); ?>
                                </td>
                                <?php if ($activeTab === 'pending'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form method="POST" action="" class="inline-block">
                                        <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="bg-green-600/20 text-green-400 hover:bg-green-600/30 px-3 py-1 rounded-lg text-sm font-medium transition duration-200 mr-2">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="" class="inline-block" onsubmit="return confirm('Are you sure you want to reject this campaign?');">
                                        <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="bg-red-600/20 text-red-400 hover:bg-red-600/30 px-3 py-1 rounded-lg text-sm font-medium transition duration-200">
                                            <i class="fas fa-times mr-1"></i> Reject
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Admin Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-500 text-sm">
                    &copy; <?php echo date('Y'); ?> CryptoFund Admin Portal. All rights reserved.
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="https://cryptofund.example.com/docs/admin" class="text-gray-500 hover:text-gray-300 text-sm transition">
                        Admin Documentation
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript for interactions -->
    <script>
        // Auto-close flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    message.classList.add('opacity-0');
                    setTimeout(() => {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>