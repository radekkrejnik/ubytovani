<?php
// Pokud licence není ověřena, omez funkčnost pluginu
add_action('admin_notices', 'ubytovani_zobraz_licencni_varovani');
add_action('init', 'ubytovani_omez_funkce_pokud_neaktivni');


function ubytovani_zobraz_licencni_varovani() {
    if (!ubytovani_licence_je_platna()) {
        add_action('admin_notices', function () {
    echo '<div class="notice notice-error"><p>' . esc_html__('Plugin Ubytování: Vaše licence není aktivní nebo vypršela. Některé funkce jsou omezené.', 'ubytovani') . '</p></div>';
});

    }
}

function ubytovani_omez_funkce_pokud_neaktivni() {
    if (!ubytovani_licence_je_platna()) {
        // Například: omezíme počet pokojů na 1
        update_option('ubytovani_pocet_pokoju', 1);
        // A další omezení můžeš přidat zde
    }
}

add_action('admin_init', 'ubytovani_overit_licenci_automaticky');

function ubytovani_overit_licenci_automaticky() {
    // Zabrání spuštění během admin-post.php a podobných požadavků
    if (
        defined('DOING_AJAX') && DOING_AJAX ||
        defined('DOING_CRON') && DOING_CRON ||
        (defined('WP_ADMIN') && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin-post.php') !== false)
    ) {
        return;
    }

    $kod = get_option('ubytovani_licencni_kod');
    $email = get_option('admin_email');

    if (empty($kod) || empty($email)) {
        return;
    }

    // Ověření jen jednou za 24h
    $posledni_overeni = get_transient('ubytovani_licence_overeni');
    if ($posledni_overeni !== false) {
        return;
    }

    $response = wp_remote_post('https://licence.zyxik.cz/wp-json/ubytovani/v1/overit-licenci', [
        'timeout' => 10,
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode([
            'kod'   => $kod,
            'email' => $email,
        ]),
    ]);

    if (is_wp_error($response)) {
        update_option('ubytovani_licence_stav', 'chyba');
    } else {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['success'])) {
            update_option('ubytovani_licence_stav', 'platna');
        } else {
            update_option('ubytovani_licence_stav', 'neplatna');
        }
    }

    set_transient('ubytovani_licence_overeni', 'ok', WEEK_IN_SECONDS);
}


// Globální kontrola platnosti licence
function ubytovani_licence_je_platna() {
    return get_option('ubytovani_licence_stav') === 'platna';
}