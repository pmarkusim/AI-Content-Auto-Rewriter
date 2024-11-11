
<?php
/*
Plugin Name: AI Content Auto Rewriter
Description: Automatikusan újraírja a WP All Import által importált tartalmakat az OpenAI API segítségével.
Version: 3.71
Author: Márkus Péter
*/

// Dinamikus titkosító kulcs létrehozása és tárolása
function get_encryption_key() {
    $stored_key = get_option('ai_content_rewriter_encryption_key');
    if (!$stored_key) {
        $new_key = bin2hex(random_bytes(16));
        update_option('ai_content_rewriter_encryption_key', $new_key);
        $stored_key = $new_key;
    }
    return $stored_key;
}

// 🔐 API kulcs titkosítása és visszafejtése
function encrypt_api_key($api_key) {
    $encryption_key = get_encryption_key();
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($api_key, 'aes-256-cbc', $encryption_key, 0, $iv);
    if ($encrypted === false) {
        error_log('AI Content Auto Rewriter - API kulcs titkosítása sikertelen.');
        return '';
    }
    return base64_encode($iv . '::' . $encrypted);
}

function decrypt_api_key($encrypted_key) {
    $encryption_key = get_encryption_key();
    $data = base64_decode($encrypted_key);

    if ($data === false || strpos($data, '::') === false) {
        error_log('AI Content Auto Rewriter - Titkosított API kulcs dekódolása sikertelen.');
        return '';
    }

    list($iv, $encrypted_data) = explode('::', $data, 2);
    if (strlen($iv) !== 16 || empty($encrypted_data)) {
        error_log('AI Content Auto Rewriter - Érvénytelen IV vagy titkosított adat.');
        return '';
    }

    $decrypted = openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    if ($decrypted === false) {
        error_log('AI Content Auto Rewriter - API kulcs visszafejtése sikertelen.');
        return '';
    }
    return $decrypted;
}

// Admin oldal létrehozása
add_action('admin_menu', 'ai_content_rewriter_menu');
function ai_content_rewriter_menu() {
    add_menu_page('AI Content Auto Rewriter', 'AI Rewriter', 'manage_options', 'ai-content-rewriter-settings', 'ai_content_rewriter_settings_page');
}

function ai_content_rewriter_settings_page() {
    ?>
    <div class="wrap">
        <h1>AI Content Auto Rewriter Beállítások</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ai_content_rewriter_settings');
            do_settings_sections('ai-content-rewriter-settings');
            submit_button('Beállítások mentése');
            ?>
        </form>
    </div>
    <?php
}

// Beállítások regisztrálása és mezők hozzáadása
add_action('admin_init', 'ai_content_rewriter_register_settings');
function ai_content_rewriter_register_settings() {
    register_setting('ai_content_rewriter_settings', 'ai_content_rewriter_api_key', [
        'sanitize_callback' => 'save_encrypted_api_key'
    ]);
    register_setting('ai_content_rewriter_settings', 'ai_content_rewriter_model');
    register_setting('ai_content_rewriter_settings', 'ai_content_rewriter_temperature');
    register_setting('ai_content_rewriter_settings', 'ai_content_rewriter_prompt');
    register_setting('ai_content_rewriter_settings', 'ai_content_rewriter_max_tokens');

    add_settings_section('ai_content_rewriter_main', 'AI Beállítások', null, 'ai-content-rewriter-settings');
    add_settings_field('ai_content_rewriter_api_key', 'API Kulcs', 'ai_content_rewriter_api_key_field', 'ai-content-rewriter-settings', 'ai_content_rewriter_main');
    add_settings_field('ai_content_rewriter_model', 'AI Modell', 'ai_content_rewriter_model_field', 'ai-content-rewriter-settings', 'ai_content_rewriter_main');
    add_settings_field('ai_content_rewriter_temperature', 'Temperature', 'ai_content_rewriter_temperature_field', 'ai-content-rewriter-settings', 'ai_content_rewriter_main');
    add_settings_field('ai_content_rewriter_prompt', 'Prompt', 'ai_content_rewriter_prompt_field', 'ai-content-rewriter-settings', 'ai_content_rewriter_main');
    add_settings_field('ai_content_rewriter_max_tokens', 'Max Tokens', 'ai_content_rewriter_max_tokens_field', 'ai-content-rewriter-settings', 'ai_content_rewriter_main');
}

function ai_content_rewriter_api_key_field() {
    $encrypted_value = get_option('ai_content_rewriter_api_key', '');
    $is_set = !empty($encrypted_value);
    
    $placeholder = $is_set ? '********' : '';
    echo '<input type="password" name="ai_content_rewriter_api_key" value="' . esc_attr($placeholder) . '" class="regular-text" autocomplete="off" placeholder="Adja meg az API kulcsot">';
    if ($is_set) {
        echo '<p class="description">Az API kulcs már el van mentve. Új megadásához írja felül.</p>';
    }
}

function save_encrypted_api_key($api_key) {
    if (!empty($api_key) && $api_key !== '********') {
        $encrypted_api_key = encrypt_api_key($api_key);
        update_option('ai_content_rewriter_api_key', $encrypted_api_key);
    }
}

function ai_content_rewriter_model_field() {
    $value = get_option('ai_content_rewriter_model', 'text-davinci-003');
    echo '<select name="ai_content_rewriter_model">
            <option value="text-davinci-003" ' . selected($value, 'text-davinci-003', false) . '>text-davinci-003</option>
            <option value="gpt-3.5-turbo" ' . selected($value, 'gpt-3.5-turbo', false) . '>gpt-3.5-turbo</option>
            <option value="gpt-4" ' . selected($value, 'gpt-4', false) . '>gpt-4</option>
          </select>';
}

function ai_content_rewriter_temperature_field() {
    $value = get_option('ai_content_rewriter_temperature', '0.7');
    echo '<input type="number" name="ai_content_rewriter_temperature" value="' . esc_attr($value) . '" step="0.1" min="0" max="1">';
}

function ai_content_rewriter_prompt_field() {
    $value = get_option('ai_content_rewriter_prompt', 'Rewrite this content:');
    echo '<textarea name="ai_content_rewriter_prompt" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
}

function ai_content_rewriter_max_tokens_field() {
    $value = get_option('ai_content_rewriter_max_tokens', '1500');
    echo '<input type="number" name="ai_content_rewriter_max_tokens" value="' . esc_attr($value) . '" min="1" max="2000">';
}

// WP All Import integráció
add_action('pmxi_saved_post', 'rewrite_content_with_openai', 10, 1);
function rewrite_content_with_openai($post_id) {
    if (get_post_type($post_id) != 'post') return;

    $content = get_post_field('post_content', $post_id);
    if (empty($content) || get_post_meta($post_id, '_is_rewritten', true)) return;

    $api_key = decrypt_api_key(get_option('ai_content_rewriter_api_key'));
    if (empty($api_key)) {
        error_log('AI Content Auto Rewriter - API kulcs hiányzik.');
        return;
    }

    $model = get_option('ai_content_rewriter_model', 'text-davinci-003');
    $temperature = floatval(get_option('ai_content_rewriter_temperature', '0.7'));
    $prompt = get_option('ai_content_rewriter_prompt', 'Rewrite this content:') . "\n\n" . $content;
    $max_tokens = intval(get_option('ai_content_rewriter_max_tokens', 1500));

    $response = wp_remote_post('https://api.openai.com/v1/completions', [
        'body' => json_encode(['model' => $model, 'prompt' => $prompt, 'temperature' => $temperature, 'max_tokens' => $max_tokens]),
        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $api_key],
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        error_log('AI Content Auto Rewriter - API hívás sikertelen: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!empty($result['choices']) && isset($result['choices'][0]['text'])) {
        wp_update_post(['ID' => $post_id, 'post_content' => wp_kses_post($result['choices'][0]['text'])]);
        update_post_meta($post_id, '_is_rewritten', true);
    } else {
        error_log('AI Content Auto Rewriter - API válasz érvénytelen vagy üres: ' . print_r($body, true));
    }
}
?>
