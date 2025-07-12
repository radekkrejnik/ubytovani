<?php
add_action('wp_ajax_nova_rezervace', 'ubytovani_ulozit_rezervaci');
add_action('wp_ajax_nopriv_nova_rezervace', 'ubytovani_ulozit_rezervaci');



function ubytovani_ulozit_rezervaci() {
    $pocet_osob = intval($_POST['pocet_osob']);
    $pocet_noci = intval($_POST['pocet_noci']);
    $je_firma = isset($_POST['firma']) && trim($_POST['firma']) !== '';
    $pokoj_id = isset($_POST['pokoj_id']) ? intval($_POST['pokoj_id']) : 1;

    $cena_1_osoba = (float) get_option('ubytovani_cena_1_osoba', 800);
    $cena_2_osoby = (float) get_option('ubytovani_cena_2_osoby', 1200);
    $poplatek_osoba = (float) get_option('ubytovani_poplatek_osoba', 30);


    if ($pocet_osob === 1) {
        $cena_ubytovani = $pocet_noci * $cena_1_osoba;
    } else {
        $cena_ubytovani = $pocet_noci * $cena_2_osoby;
    }

    $cena_poplatek = $je_firma ? 0 : ($pocet_osob * $pocet_noci * $poplatek_osoba);
    $cena_celkem = $cena_ubytovani + $cena_poplatek;

    $data = [
        'pokoj_id'        => $pokoj_id,
        'prijmeni'        => sanitize_text_field($_POST['prijmeni']),
        'jmeno'           => sanitize_text_field($_POST['jmeno']),
        'firma'           => sanitize_text_field($_POST['firma']),
        'ico'             => sanitize_text_field($_POST['ico']),
        'dic'             => sanitize_text_field($_POST['dic']),
        'adresa'          => sanitize_text_field($_POST['adresa']),
        'telefon'         => sanitize_text_field($_POST['telefon']),
        'email'           => sanitize_email($_POST['email']),
        'cislo_op'        => sanitize_text_field($_POST['cislo_op']),
        'stat'            => sanitize_text_field($_POST['stat']),
        'datum_narozeni'  => sanitize_text_field($_POST['datum_narozeni']),
        'poznamka'        => sanitize_text_field($_POST['poznamka']),
        'pocet_osob'      => $pocet_osob,
        'datum_prijezdu'  => sanitize_text_field($_POST['datum_prijezdu']),
        'datum_odjezdu'   => sanitize_text_field($_POST['datum_odjezdu']),
        'pocet_noci'      => $pocet_noci,
        'cena'            => $cena_celkem,
        'poplatek'        => $cena_poplatek,
        'stav'            => 'čeká_na_schválení'
    ];

    global $wpdb;
    $table = $wpdb->prefix . 'ubytovani_zaznamy';
    $result = $wpdb->insert($table, $data);

    if (!$result) {
        wp_send_json_error([
            'message' => /* translators: Chyba při ukládání do databáze. */
__('Chyba při ukládání do databáze.', 'ubytovani'),
            'sql_error' => $wpdb->last_error,
            'data' => $data
        ]);
    }

    $datum_od = date('d.m.Y', strtotime($data['datum_prijezdu']));
    $datum_do = date('d.m.Y', strtotime($data['datum_odjezdu']));

    $odesilatel_jmeno = get_option('ubytovani_email_jmeno', get_bloginfo('name'));
    $odesilatel_email = get_option('ubytovani_email_adresa', get_option('admin_email'));

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        "From: $odesilatel_jmeno <$odesilatel_email>"
    ];
    $subject = /* translators: Vaše rezervace byla přijata –  */
__('Vaše rezervace byla přijata – ', 'ubytovani') . get_bloginfo('name');

    $message = "
        <html>
        <body style=\"font-family: Arial, sans-serif; color: #333;\">
            <h2 style=\"color:rgb(242, 122, 25);\">" . sprintf(/* translators: Dobrý den %s %s, */
__('Dobrý den %s %s,', 'ubytovani'), esc_html($data['jmeno']), esc_html($data['prijmeni'])) . "</h2>
            <p>" . /* translators: Děkujeme za vaši rezervaci. Níže naleznete shrnutí: */
__('Děkujeme za vaši rezervaci. Níže naleznete shrnutí:', 'ubytovani') . "</p>
            <table cellpadding=\"6\" cellspacing=\"0\" border=\"0\" style=\"background-color: #f9f9f9; border: 1px solid #ccc;\">
                <tr><td><strong>" . /* translators: Termín: */
__('Termín:', 'ubytovani') . "</strong></td><td>{$datum_od} – {$datum_do}</td></tr>
                <tr><td><strong>" . /* translators: Počet osob: */
__('Počet osob:', 'ubytovani') . "</strong></td><td>{$data['pocet_osob']}</td></tr>
                <tr><td><strong>" . /* translators: Cena: */
__('Cena:', 'ubytovani') . "</strong></td><td>{$data['cena']} Kč</td></tr>
                <tr><td><strong>" . /* translators: Poznámka: */
__('Poznámka:', 'ubytovani') . "</strong></td><td>" . nl2br(esc_html($data['poznamka'])) . "</td></tr>
            </table>
            <p style=\"margin-top: 20px;\">" . /* translators: Rezervace nyní <strong>čeká na schválení</strong>. Jakmile ji potvrdíme, obdržíte další e-mail. */
__('Rezervace nyní <strong>čeká na schválení</strong>. Jakmile ji potvrdíme, obdržíte další e-mail.', 'ubytovani') . "</p>
            <p>" . /* translators: S pozdravem */
__('S pozdravem', 'ubytovani') . " " . esc_html($odesilatel_jmeno) . ",<br><strong>" . esc_html(get_bloginfo('name')) . "</strong></p>
        </body>
        </html>
    ";

    wp_mail($data['email'], $subject, $message, $headers);

    $admin_subject = /* translators: Nová rezervace čeká na schválení */
__('Nová rezervace čeká na schválení', 'ubytovani');
    $admin_message = 
        /* translators: ID rezervace */
__('ID rezervace', 'ubytovani') . ": {$wpdb->insert_id}\n" .
        /* translators: Jméno */
__('Jméno', 'ubytovani') . ": {$data['jmeno']} {$data['prijmeni']}\n" .
        /* translators: Email */
__('Email', 'ubytovani') . ": {$data['email']}\n" .
        /* translators: Telefon */
__('Telefon', 'ubytovani') . ": {$data['telefon']}\n" .
        /* translators: Termín */
__('Termín', 'ubytovani') . ": {$datum_od} – {$datum_do}\n" .
        /* translators: Počet osob */
__('Počet osob', 'ubytovani') . ": {$data['pocet_osob']}\n" .
        /* translators: Cena */
__('Cena', 'ubytovani') . ": {$data['cena']} Kč";

    wp_mail(get_option('admin_email'), $admin_subject, $admin_message);

   $dekovna_stranka = get_option('ubytovani_dekovna_stranka');
$redirect_base = $dekovna_stranka ? get_permalink($dekovna_stranka) : home_url();
$redirect_url = add_query_arg([
    'rezervace' => $wpdb->insert_id,
    'pokoj' => $pokoj_id
], $redirect_base);

    wp_send_json_success([
        'message' => /* translators: Rezervace byla uložena. */
__('Rezervace byla uložena.', 'ubytovani'),
        'rezervace_id' => $wpdb->insert_id,
        'redirect' => $redirect_url
    ]);

    wp_die(); // DŮLEŽITÉ!
}
