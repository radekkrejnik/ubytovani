<?php
function ubytovani_ziskej_stav_licence() {
    $licencni_kod = trim(get_option('ubytovani_licencni_kod'));
    $email = trim(get_option('ubytovani_licencni_email'));
    
    if (empty($email)) {
        $email = get_option('admin_email');
    }

    if (empty($licencni_kod) || empty($email)) {
        return 'expirovana';
    }

    $cached = get_transient('ubytovani_overeni_licence');
    if ($cached && isset($cached['kod']) && $cached['kod'] === $licencni_kod) {
        return $cached['stav'];
    }

    $response = wp_remote_post('https://licence.zyxik.cz/wp-json/ubytovani/v1/overit-licenci', [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode([
            'kod' => $licencni_kod,
            'email' => $email,
        ]),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>⚠️ ' . esc_html__('Nepodařilo se kontaktovat licenční server.', 'ubytovani') . '</p></div>';
        });
        return 'chyba';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($body['success'])) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success"><p>✅ Licence byla ověřena jako aktivní.</p></div>';
        });
        set_transient('ubytovani_overeni_licence', [
            'kod' => $licencni_kod,
            'stav' => 'aktivni',
        ], 6 * HOUR_IN_SECONDS);
        return 'aktivni';
    }

    add_action('admin_notices', function () use ($body) {
        $msg = $body['message'] ?? 'Neznámá chyba při ověřování licence.';
        echo '<div class="notice notice-error"><p>❌ Licence není platná: ' . esc_html($msg) . '</p></div>';
    });

    set_transient('ubytovani_overeni_licence', [
        'kod' => $licencni_kod,
        'stav' => 'expirovana',
    ], 6 * HOUR_IN_SECONDS);
    return 'expirovana';
}