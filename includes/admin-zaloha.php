<?php
require_once plugin_dir_path(__FILE__) . '/faktura-funkce.php';
require_once plugin_dir_path(__FILE__) . '/../vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . '/faktura-db.php';

add_action('admin_menu', function () {
   
});

function ubytovani_formular_zalohy() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nemáte oprávnění.', 'ubytovani'));
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Chybí ID rezervace.', 'ubytovani') . '</p></div>';
        return;
    }

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $rez = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$rez) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Rezervace nenalezena.', 'ubytovani') . '</p></div>';
        return;
    }

    $jmeno = esc_html($rez->jmeno . ' ' . $rez->prijmeni);
    $email = esc_html($rez->email);
    ?>
    <div class="wrap">
        <h1><?php _e('Odeslat výzvu k úhradě zálohy', 'ubytovani'); ?></h1>
        <p><strong><?php echo esc_html__('Jméno:', 'ubytovani'); ?></strong> <?php echo $jmeno; ?>,
           <strong><?php echo esc_html__('E-mail:', 'ubytovani'); ?></strong> <?php echo $email; ?></p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('zalohy_odeslat_' . $id); ?>
            <input type="hidden" name="action" value="ubytovani_odeslat_zalohu">
            <input type="hidden" name="rezervace_id" value="<?php echo esc_attr($id); ?>">

            <h3><?php _e('Zadejte požadovanou výši zálohy (Kč):', 'ubytovani'); ?></h3>
            <input type="number" name="castka_zalohy" min="0" step="1" required placeholder="<?php esc_attr_e('Např. 1000', 'ubytovani'); ?>" style="width: 150px;">

            <p style="margin-top: 20px;">
                <button type="submit" class="button button-primary"><?php _e('Odeslat výzvu', 'ubytovani'); ?></button>
            </p>
        </form>
    </div>
    <?php
}

add_action('admin_post_ubytovani_odeslat_zalohu', 'ubytovani_odeslat_zalohu');

function ubytovani_odeslat_zalohu() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Neautorizovaný přístup.', 'ubytovani'));
    }

    if (!isset($_POST['rezervace_id']) || !wp_verify_nonce($_POST['_wpnonce'], 'zalohy_odeslat_' . $_POST['rezervace_id'])) {
        wp_die(esc_html__('Neplatný požadavek.', 'ubytovani'));
    }

    $id = intval($_POST['rezervace_id']);
    $castka = floatval($_POST['castka_zalohy']);

    if ($castka <= 0) {
        wp_die(esc_html__('Zadaná částka není platná.', 'ubytovani'));
    }

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $rez = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));
    if (!$rez) {
        wp_die(esc_html__('Rezervace nenalezena.', 'ubytovani'));
    }

    $cislo_faktury = __('ZAL', 'ubytovani') . $id;
    $qr = ubytovani_vygeneruj_qr_kod($castka, $cislo_faktury);
    $html = ubytovani_vytvor_fakturu_html(
        $rez,
        $cislo_faktury,
        __('Záloha za ubytování', 'ubytovani'),
        $castka,
        $qr,
        'zalohova'
    );

    require_once plugin_dir_path(__FILE__) . '/../libs/dompdf/autoload.inc.php';

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf = $dompdf->output();
    $filename = sanitize_file_name($cislo_faktury) . '.pdf';
    $tmp_file = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tmp_file, $pdf);

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $subject = __('Záloha za ubytování', 'ubytovani');
    $body = __('Dobrý den, v příloze naleznete výzvu k úhradě zálohy na rezervaci.', 'ubytovani');

    wp_mail($rez->email, $subject, $body, $headers, [$tmp_file]);

    @unlink($tmp_file);

    $redirect = admin_url('admin.php?page=ubytovani-zaznamy');
    $redirect = add_query_arg([
        'zaloha_odeslana' => 1,
        'id' => $id
    ], $redirect);

    wp_redirect($redirect);
    exit;
}
