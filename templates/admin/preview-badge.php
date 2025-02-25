<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_ids = isset($_REQUEST['user_ids']) ? array_map('intval', explode(',', $_REQUEST['user_ids'])) : array();
if (empty($user_ids)) {
    wp_die('No users selected');
}

// Get the posted user data if available
$user_data = null;
if (isset($_POST['user_data'])) {
    $user_data = json_decode(stripslashes($_POST['user_data']), true);
}

require_once WP_SMART_BADGE_PATH . 'includes/class-badge-generator.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Badge Preview</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .badge-container {
                page-break-inside: avoid;
                margin: 0;
                padding: 0;
            }
        }
        
        body {
            margin: 0;
            padding: 20px;
            background: #f0f0f1;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        
        .controls {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            z-index: 100;
            text-align: center;
        }
        
        .badge-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(3.375in, 1fr));
            gap: 20px;
            padding-top: 60px;
        }
        
        .badge-container {
            background: #fff;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .button {
            display: inline-block;
            padding: 8px 12px;
            background: #2271b1;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            margin: 0 5px;
        }
        
        .button:hover {
            background: #135e96;
        }
    </style>
</head>
<body>
    <div class="controls no-print">
        <button onclick="window.print()" class="button">Print Badges</button>
        <a href="<?php echo admin_url('admin.php?page=wp-smart-badge'); ?>" class="button">Back to Grid</a>
    </div>
    
    <div class="badge-grid">
        <?php
        foreach ($user_ids as $user_id) {
            try {
                $generator = new WP_Badge_Generator($user_id, $user_data);
                echo $generator->generate_html();
            } catch (Exception $e) {
                echo '<div class="error">Error generating badge for user ' . esc_html($user_id) . ': ' . esc_html($e->getMessage()) . '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
