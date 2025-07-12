<?php
// includes/admin/admin-zaznamy-edit.php

defined('ABSPATH') || exit;

function ubytovani_stranka_editace() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nemáte oprávnění.', 'ubytovani'));
    }

    global $wpdb;
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Chybí ID rezervace.', 'ubytovani') . '</p></div>';
        return;
    }

    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $zaznam = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$zaznam) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Rezervace nenalezena.', 'ubytovani') . '</p></div>';
        return;
    }

    ?>
    <div class="wrap">
        <h1><?php echo sprintf(esc_html__('Úprava rezervace č. %d', 'ubytovani'), $id); ?></h1>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ubytovani_ulozit_rezervaci_' . $id); ?>
            <input type="hidden" name="action" value="ubytovani_ulozit_rezervaci">
            <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">

            <table class="form-table">
                <tr>
                    <th><label for="jmeno"><?php esc_html_e('Jméno', 'ubytovani'); ?></label></th>
                    <td><input type="text" name="jmeno" value="<?php echo esc_attr($zaznam->jmeno); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="prijmeni"><?php esc_html_e('Příjmení', 'ubytovani'); ?></label></th>
                    <td><input type="text" name="prijmeni" value="<?php echo esc_attr($zaznam->prijmeni); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="email"><?php esc_html_e('Email', 'ubytovani'); ?></label></th>
                    <td><input type="email" name="email" value="<?php echo esc_attr($zaznam->email); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="adresa"><?php esc_html_e('Adresa', 'ubytovani'); ?></label></th>
                    <td><input type="text" name="adresa" value="<?php echo esc_attr($zaznam->adresa); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="firma"><?php esc_html_e('Název firmy', 'ubytovani'); ?></label></th>
                    <td><input type="text" name="firma" value="<?php echo esc_attr($zaznam->firma); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ico"><?php esc_html_e('IČO', 'ubytovani'); ?></label></th>
                    <td><input type="text" name="ico" value="<?php echo esc_attr($zaznam->ico); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="dic"><?php esc_html_e('DIČ', 'ubytovani'); ?></label></th>
                    <td><input type="text" name="dic" value="<?php echo esc_attr($zaznam->dic); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="datum_od"><?php esc_html_e('Datum příjezdu', 'ubytovani'); ?></label></th>
                    <td><input type="date" name="datum_prijezdu" value="<?php echo esc_attr($zaznam->datum_prijezdu); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="datum_do"><?php esc_html_e('Datum odjezdu', 'ubytovani'); ?></label></th>
                    <td><input type="date" name="datum_odjezdu" value="<?php echo esc_attr($zaznam->datum_odjezdu); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="pocet_noci"><?php esc_html_e('Počet nocí', 'ubytovani'); ?></label></th>
                    <td><input type="number" name="pocet_noci" value="<?php echo esc_attr($zaznam->pocet_noci); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="cena"><?php esc_html_e('Cena (Kč)', 'ubytovani'); ?></label></th>
                    <td><input type="number" step="0.01" name="cena" value="<?php echo esc_attr($zaznam->cena); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="poznamka"><?php esc_html_e('Poznámka', 'ubytovani'); ?></label></th>
                    <td><textarea name="poznamka" rows="4" class="large-text"><?php echo esc_textarea($zaznam->poznamka); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="stav"><?php esc_html_e('Stav rezervace', 'ubytovani'); ?></label></th>
                    <td>
                        <select name="stav">
                            <option value="Čeká na schválení" <?php selected($zaznam->stav, 'Čeká na schválení'); ?>><?php esc_html_e('Čeká na schválení', 'ubytovani'); ?></option>
                            <option value="Schváleno" <?php selected($zaznam->stav, 'Schváleno'); ?>><?php esc_html_e('Schváleno', 'ubytovani'); ?></option>
                            <option value="Blokováno" <?php selected($zaznam->stav, 'Blokováno'); ?>><?php esc_html_e('Blokováno', 'ubytovani'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <p><button type="submit" class="button button-primary"><?php esc_html_e('Uložit změny', 'ubytovani'); ?></button></p>
        </form>
    </div>
    <?php
}

add_action('admin_post_ubytovani_ulozit_rezervaci', 'ubytovani_ulozit_editaci');

function ubytovani_ulozit_editaci() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění.', 'ubytovani'));
    }

    if (
        !isset($_POST['_wpnonce'], $_POST['id']) ||
        !wp_verify_nonce($_POST['_wpnonce'], 'ubytovani_ulozit_rezervaci_' . intval($_POST['id']))
    ) {
        wp_die(__('Neplatný požadavek.', 'ubytovani'));
    }

    global $wpdb;
    $id = intval($_POST['id']);

    $wpdb->update(
        $wpdb->prefix . 'ubytovani_zaznamy',
        [
            'jmeno'          => sanitize_text_field($_POST['jmeno']),
            'prijmeni'       => sanitize_text_field($_POST['prijmeni']),
            'email'          => sanitize_email($_POST['email']),
            'adresa'         => sanitize_text_field($_POST['adresa']),
            'firma'          => sanitize_text_field($_POST['firma']),
            'ico'            => sanitize_text_field($_POST['ico']),
            'dic'            => sanitize_text_field($_POST['dic']),
            'datum_prijezdu' => sanitize_text_field($_POST['datum_prijezdu']),
            'datum_odjezdu'  => sanitize_text_field($_POST['datum_odjezdu']),
            'pocet_noci'     => intval($_POST['pocet_noci']),
            'cena'           => floatval($_POST['cena']),
            'poznamka'       => sanitize_textarea_field($_POST['poznamka']),
            'stav'           => sanitize_text_field($_POST['stav']),
        ],
        ['id' => $id]
    );

    wp_redirect(admin_url('admin.php?page=ubytovani-zaznamy&zmena=1'));
    exit;
}
