<?php
// includes/admin/admin-blokace.php
defined('ABSPATH') || exit;

/**
 * Vykreslení stránky pro ruční blokování termínů.
 */
function ubytovani_blokace_render_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění.', 'ubytovani'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ubytovani_zaznamy';

    // VLOŽENÍ BLOKACE
    if (
        isset($_POST['ubytovani_blokace_nonce']) &&
        check_admin_referer('ubytovani_blokace_action', 'ubytovani_blokace_nonce')
    ) {
        $pokoj_id   = absint($_POST['pokoj_id']);
        $datum_od   = sanitize_text_field($_POST['datum_od']);
        $datum_do   = sanitize_text_field($_POST['datum_do']);
        $poznamka   = sanitize_textarea_field($_POST['poznamka']);

        $d_od = DateTime::createFromFormat('Y-m-d', $datum_od);
        $d_do = DateTime::createFromFormat('Y-m-d', $datum_do);

        if (!$d_od || !$d_do || $d_od > $d_do) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Zadané datum není platné.', 'ubytovani') . '</p></div>';
        } else {
            $pocet_noci = (int)$d_do->diff($d_od)->days;

            $insert_data = [
                'pokoj_id'       => $pokoj_id,
                'datum_prijezdu' => $datum_od,
                'datum_odjezdu'  => $datum_do,
                'pocet_noci'     => $pocet_noci,
                'jmeno'          => 'Blokace',
                'prijmeni'       => '',
                'email'          => 'blokace@interni',
                'telefon'        => '',
                'cena'           => 0,
                'stav'           => 'blokovano',
                'poznamka'       => $poznamka ?: 'blokace',
            ];

            $insert_format = [
                '%d','%s','%s','%d','%s','%s','%s','%s','%f','%s','%s'
            ];

            $inserted = $wpdb->insert($table, $insert_data, $insert_format);

            if ($inserted) {
                echo '<div class="notice notice-success"><p>' . esc_html__('Termín byl úspěšně zablokován.', 'ubytovani') . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__('Chyba zápisu do databáze', 'ubytovani') . ': ' . esc_html($wpdb->last_error) . '</p></div>';
            }
        }
    }

    // ODSTRANĚNÍ BLOKACE
    if (isset($_GET['ubytovani_blokace_delete'])) {
        $delete_id = absint($_GET['ubytovani_blokace_delete']);
        if ($delete_id) {
            $wpdb->delete($table, ['id' => $delete_id], ['%d']);
            echo '<div class="notice notice-success"><p>' . esc_html__('Blokace byla odstraněna.', 'ubytovani') . '</p></div>';
        }
    }

    // FORMULÁŘ PRO VLOŽENÍ
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Blokování termínů', 'ubytovani') . '</h1>';
    echo '<h2>' . esc_html__('Přidat blokaci', 'ubytovani') . '</h2>';
    echo '<form method="post">';
    wp_nonce_field('ubytovani_blokace_action', 'ubytovani_blokace_nonce');

    $max_pokoj = max(1, (int) get_option('ubytovani_pocet_pokoju', 10));

    echo '<table class="form-table"><tbody>';
    echo '<tr><th><label for="pokoj_id">' . __('Pokoj', 'ubytovani') . '</label></th><td>';
    echo '<select id="pokoj_id" name="pokoj_id">';
    for ($i = 1; $i <= $max_pokoj; $i++) {
        echo '<option value="' . $i . '">' . sprintf(__('Pokoj %d', 'ubytovani'), $i) . '</option>';
    }
    echo '</select></td></tr>';

    echo '<tr><th><label for="datum_od">' . __('Datum od', 'ubytovani') . '</label></th><td>';
    echo '<input type="date" id="datum_od" name="datum_od" required></td></tr>';

    echo '<tr><th><label for="datum_do">' . __('Datum do', 'ubytovani') . '</label></th><td>';
    echo '<input type="date" id="datum_do" name="datum_do" required></td></tr>';

    echo '<tr><th><label for="poznamka">' . __('Poznámka', 'ubytovani') . '</label></th><td>';
    echo '<input type="text" id="poznamka" name="poznamka" class="regular-text"></td></tr>';

    echo '</tbody></table>';

    submit_button(__('Zablokovat termín', 'ubytovani'));
    echo '</form>';

    // VÝPIS BLOKACÍ
    echo '<h2 style="margin-top:40px;">' . esc_html__('Aktuálně blokované termíny', 'ubytovani') . '</h2>';

    $blokace = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE email = %s ORDER BY datum_prijezdu DESC",
            'blokace@interni'
        )
    );

    if (empty($blokace)) {
        echo '<p>' . esc_html__('Žádné blokace nenalezeny.', 'ubytovani') . '</p>';
    } else {
        echo '<table class="widefat striped"><thead><tr>
                <th>ID</th><th>' . esc_html__('Pokoj', 'ubytovani') . '</th>
                <th>' . esc_html__('Od', 'ubytovani') . '</th><th>' . esc_html__('Do', 'ubytovani') . '</th>
                <th>' . esc_html__('Poznámka', 'ubytovani') . '</th><th>' . esc_html__('Akce', 'ubytovani') . '</th>
              </tr></thead><tbody>';

        foreach ($blokace as $b) {
            printf(
                '<tr>
                    <td>%d</td>
                    <td>%d</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td><a href="%s" class="button button-small">%s</a></td>
                 </tr>',
                $b->id,
                $b->pokoj_id,
                date_i18n('j. n. Y', strtotime($b->datum_prijezdu)),
                date_i18n('j. n. Y', strtotime($b->datum_odjezdu)),
                esc_html($b->poznamka),
                esc_url(add_query_arg([
                    'page' => 'ubytovani-blokace',
                    'ubytovani_blokace_delete' => $b->id
                ], admin_url('admin.php'))),
                esc_html__('Smazat', 'ubytovani')
            );
        }

        echo '</tbody></table>';
    }

    echo '</div>'; // .wrap
}