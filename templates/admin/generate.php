<?php
if (!defined('ABSPATH')) exit;

// Localize script data
wp_localize_script('wp-smart-badge-admin', 'wpSmartBadge', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'adminUrl' => admin_url(),
    'previewKey' => wp_create_nonce('badge_preview'),
    'nonce' => wp_create_nonce('wp_smart_badge_nonce')
));
?>

<div class="wrap">
    <h1>Generate ID Badges</h1>
    
    <div class="badge-actions">
        <div class="left-actions">
            <button id="bulk_generate" class="button button-primary" disabled>
                Generate Selected Badges
            </button>
            
            <form method="post" enctype="multipart/form-data" style="display: inline-block; margin-left: 10px;">
                <?php wp_nonce_field('import_users_csv', 'import_users_nonce'); ?>
                <input type="file" name="import_users_file" id="import_users_file" accept=".csv" style="display: none;" />
                <button type="button" class="button" onclick="document.getElementById('import_users_file').click();">
                    Import Users
                </button>
                <input type="submit" name="do_import_users" id="do_import_users" style="display: none;" />
            </form>
            
            <form method="post" style="display: inline-block; margin-left: 10px;">
                <?php wp_nonce_field('export_users_csv', 'export_users_nonce'); ?>
                <input type="submit" name="export_users" class="button" value="Export Users" />
            </form>
        </div>
        
        <div class="search-filter">
            <input type="text" id="quickFilter" placeholder="Quick search..." />
        </div>
    </div>
    
    <div id="userGrid" class="ag-theme-alpine"></div>
</div>

<style>
.badge-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    gap: 20px;
}

.left-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-filter input {
    width: 300px;
    height: 30px;
    padding: 5px 10px;
}

#userGrid {
    width: 100%;
    height: 600px;
}

.template-select {
    height: 28px;
    margin-right: 5px;
    min-width: 120px;
}

.button.button-small {
    padding: 0 10px;
    height: 28px;
    line-height: 26px;
}

/* AG Grid Theme Customizations */
.ag-theme-alpine {
    --ag-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    --ag-font-size: 13px;
    --ag-selected-row-background-color: rgba(0, 115, 170, 0.1);
    --ag-row-hover-color: rgba(0, 115, 170, 0.05);
    --ag-header-background-color: #f8f9fa;
    --ag-odd-row-background-color: #ffffff;
}

.ag-header-cell-label {
    font-weight: 600;
}

.ag-row-hover {
    background-color: var(--ag-row-hover-color) !important;
}

.ag-row-selected {
    background-color: var(--ag-selected-row-background-color) !important;
}

/* Add loading indicator styles */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Loading overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<script>
document.getElementById('import_users_file').addEventListener('change', function() {
    if (this.files.length > 0) {
        document.getElementById('do_import_users').click();
    }
});
</script>
