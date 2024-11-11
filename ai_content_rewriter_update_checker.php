
<?php
/*
Plugin Name: AI Content Auto Rewriter - Update Checker
*/

// Function to check for updates
function ai_content_rewriter_check_for_update() {
    // Remote version file URL (adjust to actual GitHub repo path for version.txt)
    $remote_version_url = 'https://raw.githubusercontent.com/username/ai-content-auto-rewriter/main/version.txt';

    // Get remote version
    $response = wp_remote_get($remote_version_url);
    if (is_wp_error($response)) {
        error_log('Could not retrieve version information from the remote server.');
        return;
    }

    $remote_version = trim(wp_remote_retrieve_body($response));
    $current_version = '3.71';

    if (version_compare($current_version, $remote_version, '<')) {
        add_action('admin_notices', 'ai_content_rewriter_update_notice');
    }
}

// Admin notice if an update is available
function ai_content_rewriter_update_notice() {
    echo '<div class="notice notice-warning is-dismissible"><p>Új verzió érhető el az AI Content Auto Rewriter plugin-ből! Kérjük, frissítsd a legújabb verzióra.</p></div>';
}

// Hook for checking updates during admin initialization
add_action('admin_init', 'ai_content_rewriter_check_for_update');
?>
