<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="badge-designer">
        <!-- Elements Panel -->
        <div class="elements-panel">
            <h3><?php _e('Badge Elements', 'wp-smart-badge'); ?></h3>
            <div class="field-item" data-field="photo">
                <span class="dashicons dashicons-format-image"></span>
                <?php _e('Photo', 'wp-smart-badge'); ?>
            </div>
            <div class="field-item" data-field="name">
                <span class="dashicons dashicons-admin-users"></span>
                <?php _e('Name', 'wp-smart-badge'); ?>
            </div>
            <div class="field-item" data-field="id">
                <span class="dashicons dashicons-id"></span>
                <?php _e('ID', 'wp-smart-badge'); ?>
            </div>
            <div class="field-item" data-field="designation">
                <span class="dashicons dashicons-businessman"></span>
                <?php _e('Designation', 'wp-smart-badge'); ?>
            </div>
            <div class="field-item" data-field="department">
                <span class="dashicons dashicons-groups"></span>
                <?php _e('Department', 'wp-smart-badge'); ?>
            </div>
            <div class="field-item" data-field="qr_code">
                <span class="dashicons dashicons-qrcode"></span>
                <?php _e('QR Code', 'wp-smart-badge'); ?>
            </div>
            <div class="field-item" data-field="blood_group">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php _e('Blood Group', 'wp-smart-badge'); ?>
            </div>

            <!-- Style Options -->
            <div class="style-panel">
                <h3><?php _e('Style Options', 'wp-smart-badge'); ?></h3>
                <div class="style-option">
                    <label for="background_color"><?php _e('Background Color', 'wp-smart-badge'); ?></label>
                    <input type="text" id="background_color" class="color-picker" value="#ffffff" />
                </div>
                <div class="style-option">
                    <label for="text_color"><?php _e('Text Color', 'wp-smart-badge'); ?></label>
                    <input type="text" id="text_color" class="color-picker" value="#000000" />
                </div>
            </div>
        </div>

        <!-- Badge Preview -->
        <div class="badge-preview">
            <div class="badge-controls">
                <select id="template_select">
                    <option value="default"><?php _e('Default Template', 'wp-smart-badge'); ?></option>
                    <?php
                    $templates = get_option('wp_smart_badge_templates', array());
                    foreach ($templates as $id => $template) {
                        if ($id !== 'default') {
                            echo '<option value="' . esc_attr($id) . '">' . esc_html($template['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <div class="template-actions">
                    <button id="save_template" class="button button-primary"><?php _e('Save Template', 'wp-smart-badge'); ?></button>
                    <button id="reset_template" class="button"><?php _e('Reset', 'wp-smart-badge'); ?></button>
                </div>
            </div>

            <div class="badge-content">
                <!-- Badge fields will be added here dynamically -->
            </div>
        </div>
    </div>
</div>