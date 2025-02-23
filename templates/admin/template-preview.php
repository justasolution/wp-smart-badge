<?php
if (!defined('ABSPATH')) {
    exit;
}

$template_data = isset($_GET['data']) ? json_decode(stripslashes($_GET['data']), true) : null;
if (!$template_data) {
    wp_die(__('No template data provided', 'wp-smart-badge'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php _e('Badge Preview', 'wp-smart-badge'); ?></title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .preview-header {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .preview-header h1 {
            margin: 0;
            font-size: 24px;
            color: #1d2327;
        }

        .badge-preview {
            display: flex;
            gap: 40px;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        .badge-side {
            flex: 1;
            max-width: 500px;
        }

        .badge-side h2 {
            margin: 0 0 20px;
            font-size: 18px;
            color: #1d2327;
            text-align: center;
        }

        .badge-content {
            position: relative;
            width: 100%;
            height: 300px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .badge-field {
            position: absolute;
            background: rgba(255,255,255,0.9);
            border: 1px solid #ddd;
            padding: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .badge-field .dashicons {
            width: 16px;
            height: 16px;
            font-size: 16px;
        }

        .preview-actions {
            margin-top: 20px;
            text-align: center;
        }

        .preview-actions button {
            padding: 8px 16px;
            margin: 0 5px;
            background: #2271b1;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .preview-actions button:hover {
            background: #135e96;
        }

        .preview-actions button.secondary {
            background: #f0f0f0;
            color: #2271b1;
        }

        .preview-actions button.secondary:hover {
            background: #ddd;
        }

        @media print {
            body {
                background: #fff;
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

            .badge-preview {
                padding: 0;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <h1><?php _e('Badge Preview', 'wp-smart-badge'); ?></h1>
        </div>

        <div class="badge-preview">
            <div class="badge-side">
                <h2><?php _e('Front', 'wp-smart-badge'); ?></h2>
                <div class="badge-content front" id="preview_front"></div>
            </div>

            <div class="badge-side">
                <h2><?php _e('Back', 'wp-smart-badge'); ?></h2>
                <div class="badge-content back" id="preview_back"></div>
            </div>
        </div>

        <div class="preview-actions">
            <button onclick="window.print()"><?php _e('Print', 'wp-smart-badge'); ?></button>
            <button class="secondary" onclick="window.close()"><?php _e('Close', 'wp-smart-badge'); ?></button>
        </div>
    </div>

    <script>
    (function() {
        const templateData = <?php echo json_encode($template_data); ?>;
        
        function applyStyles(container, styles) {
            if (!styles) return;
            
            if (styles.background) {
                if (styles.background.type === 'gradient') {
                    container.style.backgroundImage = `linear-gradient(${styles.background.gradient.direction}, ${styles.background.gradient.start}, ${styles.background.gradient.end})`;
                } else {
                    container.style.background = styles.background.color;
                }
            }
            
            if (styles.text) {
                container.style.color = styles.text.color;
                container.style.fontSize = getFontSize(styles.text.size);
            }
        }

        function getFontSize(size) {
            switch (size) {
                case 'small': return '10px';
                case 'large': return '14px';
                default: return '12px';
            }
        }

        function createField(field) {
            const fieldElement = document.createElement('div');
            fieldElement.className = 'badge-field';
            fieldElement.style.left = field.x + 'px';
            fieldElement.style.top = field.y + 'px';
            fieldElement.style.width = field.width + 'px';
            fieldElement.style.height = field.height + 'px';

            const icon = document.createElement('span');
            icon.className = 'dashicons';
            switch (field.type) {
                case 'photo':
                    icon.className += ' dashicons-format-image';
                    break;
                case 'name':
                    icon.className += ' dashicons-admin-users';
                    break;
                case 'id':
                    icon.className += ' dashicons-id';
                    break;
                case 'designation':
                    icon.className += ' dashicons-businessman';
                    break;
                case 'department':
                    icon.className += ' dashicons-groups';
                    break;
                case 'blood_group':
                    icon.className += ' dashicons-heart';
                    break;
                case 'qr_code':
                    icon.className += ' dashicons-qr-code';
                    break;
            }

            const text = document.createElement('span');
            text.textContent = field.text;

            fieldElement.appendChild(icon);
            fieldElement.appendChild(text);

            return fieldElement;
        }

        // Apply template data
        if (templateData.layout) {
            const frontContainer = document.getElementById('preview_front');
            const backContainer = document.getElementById('preview_back');

            // Apply styles
            if (templateData.styles) {
                applyStyles(frontContainer, templateData.styles);
                applyStyles(backContainer, templateData.styles);
            }

            // Add fields
            if (templateData.layout.front) {
                templateData.layout.front.forEach(field => {
                    frontContainer.appendChild(createField(field));
                });
            }

            if (templateData.layout.back) {
                templateData.layout.back.forEach(field => {
                    backContainer.appendChild(createField(field));
                });
            }
        }
    })();
    </script>
    <?php wp_footer(); ?>
</body>
</html>
