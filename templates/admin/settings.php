<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Smart Badge Settings</h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('wp_smart_badge_settings'); ?>
        
        <div class="card">
            <h2>General Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="organization_name">Organization Name</label>
                    </th>
                    <td>
                        <input type="text" id="organization_name" name="wp_smart_badge_settings[organization_name]" class="regular-text" value="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="organization_logo">Organization Logo</label>
                    </th>
                    <td>
                        <input type="text" id="organization_logo" name="wp_smart_badge_settings[organization_logo]" class="regular-text" value="">
                        <button type="button" class="button" id="upload_logo">Upload Logo</button>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2>Badge Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="badge_size">Badge Size</label>
                    </th>
                    <td>
                        <select id="badge_size" name="wp_smart_badge_settings[badge_size]">
                            <option value="cr80">CR80 (85.6mm x 54mm)</option>
                            <option value="custom">Custom Size</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>
