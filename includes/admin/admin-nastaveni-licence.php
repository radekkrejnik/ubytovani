<?php
if (!defined('ABSPATH')) exit;

// 1. Registrace nastaveni
add_action('admin_init', function () {
    register_setting('ubytovani_nastaveni_licence', 'ubytovani_licencni_kod');

    add_settings_section(
        'ubytovani_licence_sekce',
        __('Nastavení licence', 'ubytovani'),
        '__return_null',
        'ubytovani-licence'
    );

    add_settings_field(
        'ubytovani_licencni_kod',
        __('Licenční kód', 'ubytovani'),
        'ubytovani_pole_licencni_kod_callback',
        'ubytovani-licence',
        'ubytovani_licence_sekce'
    );
});

// 2. Pole pro ruční zadání kódu
function ubytovani_pole_licencni_kod_callback() {
    $kod = esc_attr(get_option('ubytovani_licencni_kod'));
    echo '<input type="text" name="ubytovani_licencni_kod" value="' . $kod . '" class="regular-text">';
    echo '<p class="description">' . sprintf(esc_html__('Kód je vázán na e-mail: %s', 'ubytovani'), esc_html(get_option('admin_email'))) . '</p>';
}

// 4. HTML výstup admin stránky
function ubytovani_stranka_licence_html() {
    if (!current_user_can('manage_options')) return;

    $kod      = get_option('ubytovani_licencni_kod');
    $email    = get_option('admin_email');
    $expirace = get_option('ubytovani_licence_expires');

    // Ověření licence
    if (!empty($kod)) {
        $response = wp_remote_post('https://licence.zyxik.cz/wp-json/ubytovani/v1/overit-licenci', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['email' => $email, 'kod' => $kod]),
            'timeout' => 15,
        ]);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (!empty($body['success']) && !empty($body['expirace'])) {
                update_option('ubytovani_licence_expires', sanitize_text_field($body['expirace']));
                $expirace = $body['expirace'];
            } else {
                delete_option('ubytovani_licence_expires');
                $expirace = null;
            }
        }
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Nastavení licence', 'ubytovani') . '</h1>';

    // Notifikace
    if (isset($_GET['licence']) && $_GET['licence'] === 'ok') {
        echo '<div class="notice notice-success"><p>' . esc_html__('Licence byla úspěšně vytvořena.', 'ubytovani') . '</p></div>';
    }
    if (isset($_GET['licence']) && $_GET['licence'] === 'fail') {
        $msg = esc_html($_GET['msg'] ?? esc_html__('Chyba při vytvoření licence.', 'ubytovani'));
        echo '<div class="notice notice-error"><p>' . $msg . '</p></div>';
    }

    echo '<form method="post" action="options.php">';
    settings_fields('ubytovani_nastaveni_licence');
    do_settings_sections('ubytovani-licence');
    submit_button(__('Uložit', 'ubytovani'));
    echo '</form>';

    // Žádost o zkušební licenci
    if (empty($kod)) {
        echo '<hr><h2>' . esc_html__('Žádost o 3 měsíční licenci zdarma', 'ubytovani') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="ubytovani_zadat_licenci">';
        wp_nonce_field('ubytovani_zadat_licenci_nonce');
        echo '<p><strong>E-mail:</strong> ' . esc_html($email) . '</p>';
        echo '<p><input type="submit" class="button button-primary" value="' . esc_attr__('Zažádat o licenci zdarma', 'ubytovani') . '"></p>';
        echo '</form>';
    }

    // Placená licence
    echo '<hr><h2>' . esc_html__('Zakoupení dlouhodobé licence', 'ubytovani') . '</h2>';
    echo '<p>' . esc_html__('Platbu provedete převodem na účet.', 'ubytovani') . '</p>';
    echo '<p>' . esc_html__('Zvolte délku licence. Níže se zobrazí platební údaje, které vám také přijdou na e-mail.', 'ubytovani') . '</p>';
    echo '<div id="ubytovani-placena-licence">';
    echo '<button type="button" class="button" onclick="ziskatLicenci(1)">1&nbsp;rok</button> ';
    echo '<button type="button" class="button" onclick="ziskatLicenci(2)">2&nbsp;roky</button> ';
    echo '<button type="button" class="button" onclick="ziskatLicenci(3)">3&nbsp;roky</button>';
    echo '<div id="ubytovani-licence-platebni-udaje" style="margin-top:20px;"></div>';
    echo '</div>';

    ?>
    <script>
    document.addEventListener("DOMContentLoaded", async () => {
        try {
            const response = await fetch('<?php echo esc_url("https://licence.zyxik.cz/wp-json/ubytovani/v1/ziskat-ceny"); ?>');
            const data = await response.json();
            if (data.success && data.ceny) {
                const ceny = data.ceny;
                const wrapper = document.createElement("div");
                wrapper.innerHTML = `
                    <p><strong><?php echo esc_js(__('Cena na 1 rok:', 'ubytovani')); ?></strong> ${ceny["1"]} Kč</p>
                    <p><strong><?php echo esc_js(__('Cena na 2 roky:', 'ubytovani')); ?></strong> ${ceny["2"]} Kč</p>
                    <p><strong><?php echo esc_js(__('Cena na 3 roky:', 'ubytovani')); ?></strong> ${ceny["3"]} Kč</p>
                `;
                document.getElementById("ubytovani-placena-licence").appendChild(wrapper);
            }
        } catch (e) {
            console.warn("Nepodařilo se načíst ceny z licenčního serveru.");
        }
    });

    async function ziskatLicenci(delka) {
        const email = '<?php echo esc_js($email); ?>';
        const fakturace = {
            nazev: '<?php echo esc_js(get_option("ubytovani_firma_nazev", "")); ?>',
            adresa: '<?php echo esc_js(get_option("ubytovani_firma_adresa", "")); ?>',
            ico:    '<?php echo esc_js(get_option("ubytovani_firma_ico", "")); ?>',
            dic:    '<?php echo esc_js(get_option("ubytovani_firma_dic", "")); ?>'
        };

        const povinne = ['nazev', 'adresa', 'ico'];
        for (const klic of povinne) {
            if (!fakturace[klic]) {
                alert("<?php echo esc_js(__('Chybí fakturační údaj:', 'ubytovani')); ?> " + klic);
                return;
            }
        }

        const odpoved = await fetch('<?php echo esc_url("https://licence.zyxik.cz/wp-json/ubytovani/v1/vytvorit-placenou-licenci"); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, delka, fakturace })
        });

        const data = await odpoved.json();
        if (!data.success) {
            alert("<?php echo esc_js(__('Chyba:', 'ubytovani')); ?> " + data.message);
            return;
        }

        document.getElementById("ubytovani-licence-platebni-udaje").innerHTML = `
            <strong><?php echo esc_js(__('Licence vytvořena:', 'ubytovani')); ?></strong><br>
            <?php echo esc_js(__('Částka:', 'ubytovani')); ?> <strong>${data.castka} Kč</strong><br>
            VS: <code>${data.vs}</code><br>
            IBAN: CZ7303000000000309096258<br>
            Účet CZ: 309096258/0300<br>
            <p><?php echo esc_js(__('Po přijetí platby bude licence aktivována a odeslána na e-mail.', 'ubytovani')); ?></p>
        `;
    }
    </script>
    <?php

        // Zobrazení aktivní licence
    if (!empty($kod)) {
        echo '<hr><h2>' . esc_html__('Aktivní licence', 'ubytovani') . '</h2>';
        echo '<p><strong>' . esc_html__('Licenční kód:', 'ubytovani') . '</strong> ' . esc_html($kod) . '</p>';
        if (!empty($expirace)) {
            $datum = date_i18n('j. n. Y H:i', strtotime($expirace));
            echo '<p><strong>' . esc_html__('Platnost do:', 'ubytovani') . '</strong> ' . esc_html($datum) . '</p>';
        } else {
            echo '<p><em>' . esc_html__('Datum expirace není k dispozici.', 'ubytovani') . '</em></p>';
        }
    }

    echo '</div>'; // wrap
}
