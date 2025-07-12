<?php
add_filter('admin_footer_text', '__return_empty_string', 11);
add_filter('update_footer', '__return_empty_string', 11);

function ubytovani_formular_faktury() {
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

    $faktura_tab = $wpdb->prefix . 'ubytovani_faktury';
    $cislo = $wpdb->get_var($wpdb->prepare(
        "SELECT cislo_faktury FROM $faktura_tab WHERE rezervace_id = %d",
        $id
    ));

    $jmeno = esc_html($rez->jmeno . ' ' . $rez->prijmeni);
    $email = esc_html($rez->email);
    ?>
    <div class="wrap">
        <h1><?php echo sprintf(esc_html__('Vystavení faktury – Rezervace #%d', 'ubytovani'), $id); ?></h1>
        <p><strong><?php esc_html_e('Jméno:', 'ubytovani'); ?></strong> <?php echo $jmeno; ?>,
        <strong><?php esc_html_e('Email:', 'ubytovani'); ?></strong> <?php echo $email; ?></p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('faktura_odeslat_' . $id); ?>
            <input type="hidden" name="action" value="ubytovani_odeslat_fakturu">
            <input type="hidden" name="rezervace_id" value="<?php echo esc_attr($id); ?>">

            <h3><?php esc_html_e('Číslo faktury:', 'ubytovani'); ?></h3>
            <input type="text" name="cislo_faktury" value="<?php echo esc_attr($cislo); ?>" placeholder="<?php esc_attr_e('Např. UB15_2025', 'ubytovani'); ?>" style="width: 200px;">

            <h3><?php esc_html_e('Komu odeslat fakturu:', 'ubytovani'); ?></h3>
            <label><input type="checkbox" name="poslat_zakaznikovi" value="1" checked> <?php esc_html_e('Zákazník', 'ubytovani'); ?></label><br>
            <label><input type="checkbox" name="poslat_spravci" value="1"> <?php esc_html_e('Správce', 'ubytovani'); ?></label><br><br>

            <h3><?php esc_html_e('Zpráva pro příjemce (volitelné):', 'ubytovani'); ?></h3>
            <input type="text" name="zprava_prijemce" value="" placeholder="<?php esc_attr_e('Např. Děkujeme za rezervaci.', 'ubytovani'); ?>" class="regular-text"><br><br>

            <button type="submit" class="button button-primary"><?php esc_html_e('Odeslat fakturu', 'ubytovani'); ?></button>
        </form>
    </div>
    <?php
}

function ubytovani_vypis_zaznamu() {
    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    // Načti všechny pokoje, kde jsou záznamy
    $pokoje = $wpdb->get_col("SELECT DISTINCT pokoj_id FROM $tabulka ORDER BY pokoj_id ASC");
    if (empty($pokoje)) {
        echo '<p>' . esc_html__('Žádné rezervace nebyly nalezeny.', 'ubytovani') . '</p>';
        return;
    }

    foreach ($pokoje as $pokoj) {
        echo '<h2>' . sprintf(__('Pokoj č. %d', 'ubytovani'), intval($pokoj)) . '</h2>';

        $zaznamy = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $tabulka WHERE pokoj_id = %d ORDER BY datum_prijezdu ASC", intval($pokoj))
        );

        if (empty($zaznamy)) {
            echo '<p>' . esc_html__('Žádné záznamy pro tento pokoj.', 'ubytovani') . '</p>';
            continue;
        }

        echo '<table class="widefat fixed striped">';
        echo '<thead>
            <tr>
                <th>ID</th>
                <th>' . esc_html__('Datum příjezdu', 'ubytovani') . '</th>
                <th>' . esc_html__('Datum odjezdu', 'ubytovani') . '</th>
                <th>' . esc_html__('Jméno a příjmení', 'ubytovani') . '</th>
                <th>' . esc_html__('Počet nocí', 'ubytovani') . '</th>
                <th>' . esc_html__('Cena', 'ubytovani') . '</th>
                <th>' . esc_html__('Stav', 'ubytovani') . '</th>
                <th>' . esc_html__('Akce', 'ubytovani') . '</th>
            </tr>
        </thead>';
        echo '<tbody>';

        foreach ($zaznamy as $zaznam) {
    $today = date('Y-m-d');
    $prijezd = $zaznam->datum_prijezdu;
    $odjezd = $zaznam->datum_odjezdu;

    $tr_class = '';
    if ($odjezd < $today) {
        $tr_class = 'rez-minula';
    } elseif ($prijezd <= $today && $odjezd >= $today) {
        $tr_class = 'rez-aktualni';
    } elseif ($prijezd > $today) {
        $tr_class = 'rez-budouci';
    }

    echo '<tr class="' . esc_attr($tr_class) . '">';

            $detail_url = admin_url('admin.php?page=ubytovani-detail&id=' . $zaznam->id);
            $edit_url   = admin_url('admin.php?page=ubytovani-edit&id=' . $zaznam->id);
            $delete_url = wp_nonce_url(
                admin_url('admin-post.php?action=ubytovani_smazat&id=' . $zaznam->id),
                'smazat_zaznam'
            );
            $stav_url = wp_nonce_url(
                admin_url('admin-post.php?action=ubytovani_zmenit_stav&id=' . $zaznam->id),
                'zmenit_stav'
            );
            
            echo '<td>' . intval($zaznam->id) . '</td>';
            echo '<td>' . esc_html(date_i18n('j. n. Y', strtotime($zaznam->datum_prijezdu))) . '</td>';
            echo '<td>' . esc_html(date_i18n('j. n. Y', strtotime($zaznam->datum_odjezdu))) . '</td>';
            echo '<td>' . esc_html($zaznam->jmeno . ' ' . $zaznam->prijmeni) . '</td>';
            echo '<td>' . intval($zaznam->pocet_noci) . '</td>';
            echo '<td>' . number_format($zaznam->cena, 2, ',', ' ') . ' Kč</td>';

            if ($zaznam->stav === 'Schváleno') {
                echo '<td style="color: green;"><strong>✔ ' . esc_html__('Schváleno', 'ubytovani') . '</strong></td>';
            } else {
                echo '<td style="color: orange;"><strong>⏳ ' . esc_html__('Čeká na schválení', 'ubytovani') . '</strong></td>';
            }

            echo '<td>';
            echo '<a href="' . esc_url($stav_url) . '&novy=' . ($zaznam->stav === 'Schváleno' ? 'ceka' : 'schvaleno') . '" title="' . esc_attr__($zaznam->stav === 'Schváleno' ? 'Vrátit zpět' : 'Schválit', 'ubytovani') . '" style="margin-right:5px;">' . ($zaznam->stav === 'Schváleno' ? '🔄' : '✅') . '</a>';
            echo '<a href="' . esc_url($edit_url) . '" title="' . esc_attr__('Upravit', 'ubytovani') . '" style="margin-right:5px;">✏️</a>';
            echo '<a href="' . esc_url($detail_url) . '" title="' . esc_attr__('Zobrazit', 'ubytovani') . '" style="margin-right:5px;">🔍</a>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=ubytovani-faktura&id=' . $zaznam->id)) . '" title="' . esc_attr__('Vystavit fakturu', 'ubytovani') . '" style="margin-right:5px;">🧾</a>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=ubytovani-zaloha&id=' . $zaznam->id)) . '" title="' . esc_attr__('Odeslat výzvu k záloze', 'ubytovani') . '" style="margin-right:5px;">💰</a>';
            echo '<a href="' . esc_url($delete_url) . '" title="' . esc_attr__('Smazat', 'ubytovani') . '" onclick="return confirm(\'' . esc_js(__('Opravdu smazat tento záznam?', 'ubytovani')) . '\')">✖️</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table><br>';
    }
}

add_action('admin_post_ubytovani_smazat', 'ubytovani_smazat_zaznam');
function ubytovani_smazat_zaznam() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nedostatečná oprávnění.', 'ubytovani'));
    }

    if (!isset($_GET['id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'smazat_zaznam')) {
        wp_die(esc_html__('Neplatný požadavek.', 'ubytovani'));
    }

    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'ubytovani_zaznamy', ['id' => intval($_GET['id'])]);
    wp_redirect(admin_url('admin.php?page=ubytovani-zaznamy'));
    exit;
}

add_action('admin_post_ubytovani_zmenit_stav', 'ubytovani_zmenit_stav');
function ubytovani_zmenit_stav() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nedostatečná oprávnění.', 'ubytovani'));
    }

    if (!isset($_GET['id'], $_GET['novy']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'zmenit_stav')) {
        wp_die(esc_html__('Neplatný požadavek.', 'ubytovani'));
    }

    $stav = ($_GET['novy'] === 'schvaleno') ? __('Schváleno', 'ubytovani') : __('Čeká na schválení', 'ubytovani');

    global $wpdb;
    $wpdb->update($wpdb->prefix . 'ubytovani_zaznamy', ['stav' => $stav], ['id' => intval($_GET['id'])]);
    wp_redirect(admin_url('admin.php?page=ubytovani-zaznamy'));
    exit;
}

function ubytovani_turisticke_poplatky_stranka() {
    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $rok = date('Y');
    $ctvrtleti = ceil(date('n') / 3);

    if (isset($_GET['rok']) && isset($_GET['ctvrtleti'])) {
        $rok = intval($_GET['rok']);
        $ctvrtleti = intval($_GET['ctvrtleti']);
    }

    $od_mesic = ($ctvrtleti - 1) * 3 + 1;
    $do_mesic = $od_mesic + 2;
    $od = "$rok-" . str_pad($od_mesic, 2, '0', STR_PAD_LEFT) . "-01";
    $do = date('Y-m-t', strtotime("$rok-" . str_pad($do_mesic, 2, '0', STR_PAD_LEFT) . "-01"));

    $celkem = $wpdb->get_var($wpdb->prepare("
        SELECT SUM(poplatek)
        FROM $tabulka
        WHERE (firma IS NULL OR firma = '')
        AND datum_prijezdu >= %s AND datum_odjezdu <= %s
    ", $od, $do));

    echo '<div class="wrap"><h1>' . esc_html__('Turistické poplatky', 'ubytovani') . '</h1>';
    echo '<form method="get" style="margin-bottom: 20px;">';
    echo '<input type="hidden" name="page" value="ubytovani-poplatky">';
    echo esc_html__('Rok:', 'ubytovani') . ' <input type="number" name="rok" value="' . esc_attr($rok) . '" min="2020" max="2100"> ';
    echo esc_html__('Čtvrtletí:', 'ubytovani') . ' <select name="ctvrtleti">';
       for ($i = 1; $i <= 4; $i++) {
        echo '<option value="' . $i . '"' . selected($ctvrtleti, $i, false) . '>' . $i . '</option>';
    }
    echo '</select> ';
    echo '<button type="submit" class="button">' . esc_html__('Zobrazit', 'ubytovani') . '</button>';
    echo '</form>';

    if ($celkem === null) {
        echo '<p>' . esc_html__('Žádné údaje pro vybrané období.', 'ubytovani') . '</p>';
    } else {
        echo '<p><strong>' . esc_html__('Celkové turistické poplatky:', 'ubytovani') . '</strong> ' . number_format($celkem, 2, ',', ' ') . ' Kč</p>';
    }

    echo '</div>';
}
