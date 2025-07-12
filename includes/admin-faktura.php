<?php

add_action('admin_post_ubytovani_odeslat_fakturu', 'ubytovani_odeslat_fakturu');

function ubytovani_odeslat_fakturu() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Neautorizovaný přístup.', 'ubytovani'));
    }

    $id = intval($_POST['rezervace_id']);
    if (!$id || !wp_verify_nonce($_POST['_wpnonce'], 'faktura_odeslat_' . $id)) {
        wp_die(esc_html__('Neplatný požadavek.', 'ubytovani'));
    }

    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
    require_once plugin_dir_path(__FILE__) . '../includes/faktura-funkce.php';
    require_once plugin_dir_path(__FILE__) . '../libs/dompdf/autoload.inc.php';

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $rez = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$rez) {
        wp_die(esc_html__('Rezervace nenalezena.', 'ubytovani'));
    }

    $cislo_faktury = isset($_POST['cislo_faktury']) && $_POST['cislo_faktury'] !== ''
        ? sanitize_text_field($_POST['cislo_faktury'])
        : __('F', 'ubytovani') . $id;

    $zprava_prijemce = isset($_POST['zprava_prijemce']) && trim($_POST['zprava_prijemce']) !== ''
        ? sanitize_text_field($_POST['zprava_prijemce'])
        : __('Platba za ubytování', 'ubytovani');

    $html = ubytovani_vytvor_fakturu_html($rez, $cislo_faktury, $zprava_prijemce);

    // Generování PDF
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf = $dompdf->output();

    // Název PDF
    $filename = sanitize_file_name($cislo_faktury) . '.pdf';
    $tmp_path = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tmp_path, $pdf);

    // Hlavičky a text e-mailu
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $subject = __('Faktura za ubytování – Byt číslo 37', 'ubytovani');
    $body = __('Dobrý den,<br>v příloze zasíláme fakturu za vaši rezervaci.<br><br>S pozdravem<br>Byt číslo 37', 'ubytovani');

    $odeslano = 0;

    if (!empty($_POST['poslat_zakaznikovi']) && is_email($rez->email)) {
        wp_mail($rez->email, $subject, $body, $headers, [$tmp_path]);
        $odeslano++;
    }

    if (!empty($_POST['poslat_spravci'])) {
        $admin_email = get_option('admin_email');
        wp_mail($admin_email, $subject, $body, $headers, [$tmp_path]);
        $odeslano++;
    }

    @unlink($tmp_path);

    $redirect = admin_url('admin.php?page=ubytovani-zaznamy');
    $redirect = add_query_arg([
        'faktura_odeslana' => $odeslano,
        'id' => $id
    ], $redirect);

    wp_redirect($redirect);
    exit;
}
add_action('admin_post_ubytovani_vystavit_fakturu', function () {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Neautorizovaný přístup.', 'ubytovani'));
    }

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        wp_die(esc_html__('Neplatné nebo chybějící ID rezervace.', 'ubytovani'));
    }

    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
    require_once plugin_dir_path(__FILE__) . 'faktura-funkce.php';

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $rez = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$rez) {
        wp_die(esc_html__('Rezervace nebyla nalezena.', 'ubytovani'));
    }

    // Generování HTML obsahu faktury
    $html = ubytovani_vytvor_fakturu_html($rez, $id);

    // Vytvoření PDF pomocí DomPDF
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Lokalizovaný název souboru
    $filename = sprintf(__('faktura_rezervace_%d.pdf', 'ubytovani'), $id);

    ob_end_clean();
    $dompdf->stream($filename, ['Attachment' => 1]);
    exit;
});
