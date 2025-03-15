<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    if (isset($_GET['preview_key']) && isset($_GET['user_ids'])) {
        // Validate preview key
        $preview_key = sanitize_text_field($_GET['preview_key']);
        $stored_key = get_transient('badge_preview_' . $_GET['user_ids']);
        
        if ($preview_key !== $stored_key) {
            die('Invalid preview key');
        }
    } else {
        die('Direct access not allowed');
    }
}

require_once dirname(__FILE__) . '/../includes/class-badge-generator.php';
require_once dirname(__FILE__) . '/../includes/class-badge-template.php';
require_once dirname(__FILE__) . '/../includes/templates/class-class-3-4-template.php';
require_once dirname(__FILE__) . '/../includes/templates/class-active-employee-horizontal-template.php';
// require_once dirname(__FILE__) . '/../includes/templates/class-retired-officer-template.php';
// require_once dirname(__FILE__) . '/../includes/templates/class-retired-medical-template.php';
// require_once dirname(__FILE__) . '/../includes/templates/class-retired-travel-template.php';
// require_once dirname(__FILE__) . '/../includes/templates/class-vertical-card-template.php';

$user_ids = isset($_GET['user_ids']) ? array_map('intval', explode(',', $_GET['user_ids'])) : array();
if (empty($user_ids)) {
    die('No users selected');
}

// Load CSS
$plugin_url = plugin_dir_url(dirname(__FILE__));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Badge Preview</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .preview-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .preview-header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .preview-actions {
            text-align: center;
            margin-bottom: 20px;
        }
        .preview-actions button {
            margin: 0 5px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-print {
            background: #4CAF50;
            color: white;
        }
        .btn-close {
            background: #f44336;
            color: white;
        }
        .btn-download {
            background: #2196F3;
            color: white;
        }
        .preview-content {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .preview-side {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .preview-side h2 {
            margin: 0 0 10px;
            color: #666;
            font-size: 16px;
            text-align: center;
        }
        .badge-container {
            display: inline-block;
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error {
            color: #f44336;
            padding: 20px;
            text-align: center;
            background: #ffebee;
            border-radius: 4px;
            margin: 20px 0;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .preview-container {
                box-shadow: none;
                padding: 0;
            }
            .preview-header,
            .preview-actions {
                display: none;
            }
            .preview-content {
                gap: 0;
            }
            .preview-side {
                box-shadow: none;
                padding: 0;
            }
            .preview-side h2 {
                display: none;
            }
            .badge-container {
                border: none;
                padding: 0;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <h1>ID Card Preview</h1>
        </div>
        
        <div class="preview-actions">
            <button class="btn-print" onclick="window.print()">Print Badges</button>
            <button class="btn-close" onclick="window.close()">Close Preview</button>
            <button class="btn-download" onclick="downloadPDF()">Download PDF</button>
        </div>
        
        <div class="preview-content">
            <?php
            if (!empty($user_ids)) {
                foreach ($user_ids as $user_id) {
                    try {
                        $generator = new WpSmartBadge\Badge_Generator($user_id);
                        $template = $generator->get_template();
                        
                        if ($template) {
                            ?>
                            <div class="preview-side">
                                <h2>Front Side</h2>
                                <div class="badge-container">
                                    <?php echo $template->generate_front(); ?>
                                </div>
                            </div>
                            
                            <div class="preview-side">
                                <h2>Back Side</h2>
                                <div class="badge-container">
                                    <?php echo $template->generate_back(); ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            echo '<div class="error">Error: Template not found for user ' . esc_html($user_id) . '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">Error generating badge for user ' . esc_html($user_id) . ': ' . esc_html($e->getMessage()) . '</div>';
                    }
                }
            } else {
                echo '<div class="error">No users selected for badge generation</div>';
            }
            ?>
        </div>
    </div>
    
    <script>
    function downloadPDF() {
        var previewKey = '<?php echo esc_js($preview_key); ?>';
        var userIds = '<?php echo esc_js(implode(",", $user_ids)); ?>';
        
        // Construct the download URL
        var downloadUrl = ajaxurl + '?action=download_badges&preview_key=' + previewKey + '&user_ids=' + userIds;
        
        // Create a temporary link and trigger the download
        var link = document.createElement('a');
        link.href = downloadUrl;
        link.download = 'badges.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
    <?php wp_footer(); ?>
</body>
</html>
