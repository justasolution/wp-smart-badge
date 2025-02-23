<?php
if (!defined('ABSPATH')) exit;

// Verify nonce
if (!isset($_GET['preview_key']) || !wp_verify_nonce($_GET['preview_key'], 'badge_preview')) {
    wp_die('Invalid preview key.');
}

// Get parameters
$user_ids = isset($_GET['user_ids']) ? explode(',', $_GET['user_ids']) : [];
$template_type = isset($_GET['template_type']) ? sanitize_text_field($_GET['template_type']) : 'ActiveEmployee';
$is_bulk = isset($_GET['bulk']) && $_GET['bulk'] === '1';

// Verify user permissions
if (!current_user_can('manage_options')) {
    wp_die('Sorry, you are not allowed to access this page.');
}

// Load preview content
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .badge-preview {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .actions {
            margin-top: 20px;
            text-align: right;
        }
        .button {
            display: inline-block;
            padding: 8px 16px;
            background: #2271b1;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background: #135e96;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <?php
        foreach ($user_ids as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $user_data = [
                    'ID' => $user_id,
                    'emp_full_name' => $user->display_name
                ];
                ?>
                <div class="badge-preview">
                    <?php
                    /**
                     * Template for badge preview
                     */
                    ?>
                    <?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1><?php esc_html_e('Badge Preview', 'wp-smart-badge'); ?></h1>
    
    <div class="badge-preview-container" style="max-width: 800px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div class="badge-preview-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
            <h2 style="margin: 0; font-size: 1.4em; color: #1d2327;"><?php echo esc_html(sprintf(__('Preview for: %s', 'wp-smart-badge'), $user_data['emp_full_name'])); ?></h2>
            <div class="badge-preview-actions" style="display: flex; gap: 10px;">
                <button type="button" class="button button-secondary" onclick="window.print()">
                    <span class="dashicons dashicons-printer" style="margin-right: 5px;"></span>
                    <?php esc_html_e('Print Badge', 'wp-smart-badge'); ?>
                </button>
                <button type="button" class="button button-primary" onclick="downloadBadge(<?php echo esc_js($user_data['ID']); ?>, '<?php echo esc_js($template_type); ?>')">
                    <span class="dashicons dashicons-download" style="margin-right: 5px;"></span>
                    <?php esc_html_e('Download Badge', 'wp-smart-badge'); ?>
                </button>
            </div>
        </div>
        
        <div class="badge-preview" style="display: flex; gap: 40px; justify-content: center; margin: 20px 0;">
            <div class="badge-side" style="position: relative;">
                <div class="badge-label" style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #f0f0f1; padding: 4px 12px; border-radius: 4px; font-size: 12px; color: #50575e;">
                    <?php esc_html_e('Front', 'wp-smart-badge'); ?>
                </div>
                <div class="badge-front" style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <?php
                    // Generate badge preview
                    $template_class = "WpSmartBadge\\Templates\\{$template_type}Template";
                    if (class_exists($template_class)) {
                        $template = new $template_class($user_id);
                        echo $template->generate_front();
                    } else {
                        echo '<p>Error: Template not found.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="badge-side" style="position: relative;">
                <div class="badge-label" style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #f0f0f1; padding: 4px 12px; border-radius: 4px; font-size: 12px; color: #50575e;">
                    <?php esc_html_e('Back', 'wp-smart-badge'); ?>
                </div>
                <div class="badge-back" style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <?php
                    // Generate badge preview
                    $template_class = "WpSmartBadge\\Templates\\{$template_type}Template";
                    if (class_exists($template_class)) {
                        $template = new $template_class($user_id);
                        echo $template->generate_back();
                    } else {
                        echo '<p>Error: Template not found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .wrap h1,
    .badge-preview-header,
    .badge-label {
        display: none !important;
    }
    
    .badge-preview-container {
        margin: 0 !important;
        padding: 0 !important;
        background: none !important;
        box-shadow: none !important;
    }
    
    .badge-preview {
        display: block !important;
        margin: 0 !important;
    }
    
    .badge-side {
        margin-bottom: 20px !important;
        page-break-after: always !important;
    }
    
    .badge-front,
    .badge-back {
        border: none !important;
        box-shadow: none !important;
    }
    
    @page {
        size: 54mm 85.6mm;
        margin: 0;
    }
}</style>

<script>
function downloadBadge(userId, templateType) {
    const button = document.querySelector('.button-primary');
    button.disabled = true;
    button.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; margin-right: 5px;"></span><?php esc_html_e('Generating...', 'wp-smart-badge'); ?>';
    
    const data = new FormData();
    data.append('action', 'wp_smart_badge_download');
    data.append('nonce', '<?php echo wp_create_nonce('wp_smart_badge_download'); ?>');
    data.append('user_id', userId);
    data.append('template_type', templateType);
    
    fetch(ajaxurl, {
        method: 'POST',
        body: data,
        credentials: 'same-origin'
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `badge_${userId}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?php esc_html_e('Error downloading badge. Please try again.', 'wp-smart-badge'); ?>');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<span class="dashicons dashicons-download" style="margin-right: 5px;"></span><?php esc_html_e('Download Badge', 'wp-smart-badge'); ?>';
    });
}

@keyframes spin {
    100% { transform: rotate(360deg); }
}
</script>

                </div>
                <?php
            }
        }
        ?>
    </div>
</body>
</html>
