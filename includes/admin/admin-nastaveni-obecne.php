<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'ubytovani_zaregistrovat_nastaveni_obecne');
add_action('admin_init', 'ubytovani_kontrola_zkusebni_verze');


function ubytovani_kontrola_zkusebni_verze() {
    if (!function_exists('ubytovani_ziskej_stav_licence')) {
        return;
    }

    $stav = ubytovani_ziskej_stav_licence();

    if ($stav !== 'aktivni') {
        $nastaveni = get_option('ubytovani_pokoje_nastaveni', []);
        
        if (is_array($nastaveni) && count($nastaveni) > 1) {
            // ponechá jen pokoj 1
            $nastaveni = array_intersect_key($nastaveni, [1 => true]);
            update_option('ubytovani_pokoje_nastaveni', $nastaveni);
        }
    }
}


function ubytovani_zaregistrovat_nastaveni_obecne() {
    register_setting('ubytovani_nastaveni_obecne', 'ubytovani_dekovna_stranka');
    register_setting('ubytovani_nastaveni_obecne', 'ubytovani_licencni_email');

    add_settings_section(
        'ubytovani_obecne_sekce',
        __('Obecné nastavení', 'ubytovani'),
        '__return_null',
        'ubytovani-obecne'
    );

    add_settings_field(
        'ubytovani_licencni_email',
        __('E-mail pro ověření licence', 'ubytovani'),
        'ubytovani_pole_licencni_email_callback',
        'ubytovani-obecne',
        'ubytovani_obecne_sekce'
    );

    add_settings_field(
        'ubytovani_dekovna_stranka',
        __('URL stránky s děkovnou zprávou', 'ubytovani'),
        'ubytovani_pole_dekovna_stranka_callback',
        'ubytovani-obecne',
        'ubytovani_obecne_sekce'
    );

    register_setting('ubytovani_nastaveni_obecne', 'ubytovani_pocet_pokoju', [
        'sanitize_callback' => 'ubytovani_sanitize_pocet_pokoju'
    ]);

    add_settings_field(
        'ubytovani_pocet_pokoju',
        __('Počet pokojů', 'ubytovani'),
        'ubytovani_pole_pocet_pokoju_callback',
        'ubytovani-obecne',
        'ubytovani_obecne_sekce'
    );

    register_setting('ubytovani_nastaveni_obecne', 'ubytovani_smazat_data_pri_odinstalaci');

    add_settings_field(
        'ubytovani_smazat_data_pri_odinstalaci',
        __('Smazat data při odinstalaci', 'ubytovani'),
        'ubytovani_pole_smazat_data_callback',
        'ubytovani-obecne',
        'ubytovani_obecne_sekce'
    );
}

function ubytovani_pole_dekovna_stranka_callback() {
    $stranka_id = get_option('ubytovani_dekovna_stranka');
    wp_dropdown_pages([
        'name' => 'ubytovani_dekovna_stranka',
        'selected' => $stranka_id,
        'show_option_none' => __('– Vyberte stránku –', 'ubytovani'),
    ]);
}

function ubytovani_pole_smazat_data_callback() {
    $hodnota = get_option('ubytovani_smazat_data_pri_odinstalaci', 0);
    ?>
    <label>
        <input type="checkbox" name="ubytovani_smazat_data_pri_odinstalaci" value="1" <?php checked($hodnota, 1); ?> />
        <?php _e('Ano, chci trvale odstranit všechna data pluginu při jeho odinstalaci.', 'ubytovani'); ?>
    </label>
    <?php
}

function ubytovani_pole_licencni_email_callback() {
    $hodnota = get_option('ubytovani_licencni_email');
    if (!$hodnota) {
        $hodnota = get_option('admin_email'); // výchozí e-mail
    }

    echo '<input type="email" name="ubytovani_licencni_email" value="' . esc_attr($hodnota) . '" class="regular-text" />';
}

function ubytovani_pole_pocet_pokoju_callback() {
    $aktualni = intval(get_option('ubytovani_pocet_pokoju', 1));
    $stav = ubytovani_ziskej_stav_licence();

    if ($stav !== 'aktivni') {
        echo '<select name="ubytovani_pocet_pokoju" disabled>';
        echo "<option value='1' selected>1</option>";
        echo '</select>';
        echo '<input type="hidden" name="ubytovani_pocet_pokoju" value="1">';
        echo '<p class="description" style="color:red;">' . __('Pro nastavení více pokojů je vyžadována plná verze pluginu.', 'ubytovani') . '</p>';
    } else {
        echo '<select name="ubytovani_pocet_pokoju">';
        for ($i = 1; $i <= 20; $i++) {
            $selected = selected($aktualni, $i, false);
            echo "<option value='$i' $selected>$i</option>";
        }
        echo '</select>';
        echo '<p class="description">' . __('Zadejte celkový počet pokojů. Po uložení se zobrazí nastavení pro každý pokoj zvlášť.', 'ubytovani') . '</p>';
    }
}

function ubytovani_sanitize_pocet_pokoju($input) {
    $stav = ubytovani_ziskej_stav_licence();

    if ($stav !== 'aktivni') {
        return 1;
    }

    $input = intval($input);
    return max(1, min(20, $input));
}

function ubytovani_stranka_obecne_html() {
    ?>
    <div class="wrap">
        <h1><?php _e('Obecné nastavení', 'ubytovani'); ?></h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('ubytovani_nastaveni_obecne');
                do_settings_sections('ubytovani-obecne');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}
