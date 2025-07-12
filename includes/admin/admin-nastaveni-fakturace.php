<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'ubytovani_zaregistrovat_nastaveni_fakturace');

function ubytovani_zaregistrovat_nastaveni_fakturace() {
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_email_jmeno');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_email_adresa');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_nazev');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_adresa');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_ico');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_dic');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_ucet');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_ucet_cz');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_firma_dph');
    register_setting('ubytovani_nastaveni_fakturace', 'ubytovani_smazat_data_pri_odinstalaci');

    add_settings_section(
    'ubytovani_fakturace_sekce',
    __('Fakturační údaje', 'ubytovani'),
    'ubytovani_vykreslit_nastaveni_fakturace',
    'ubytovani-fakturace'
);

}


function ubytovani_vykreslit_nastaveni_fakturace() {
    ?>
    <table class="form-table">
        <tr>
            <th><label for="ubytovani_email_jmeno"><?php _e('Jméno odesílatele e-mailů', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_email_jmeno" type="text" value="<?php echo esc_attr(get_option('ubytovani_email_jmeno', get_bloginfo('name'))); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_email_adresa"><?php _e('E-mailová adresa odesílatele', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_email_adresa" type="email" value="<?php echo esc_attr(get_option('ubytovani_email_adresa', get_option('admin_email'))); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_firma_nazev"><?php _e('Název firmy', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_firma_nazev" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_nazev')); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_firma_adresa"><?php _e('Adresa', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_firma_adresa" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_adresa')); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_firma_ico"><?php _e('IČO', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_firma_ico" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_ico')); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_firma_dic"><?php _e('DIČ', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_firma_dic" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_dic')); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_firma_ucet"><?php _e('Číslo účtu – IBAN', 'ubytovani'); ?></label></th>
            <td><input name="ubytovani_firma_ucet" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_ucet')); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ubytovani_firma_ucet_cz"><?php _e('Český formát účtu', 'ubytovani'); ?></label></th>
            <td>
                <input name="ubytovani_firma_ucet_cz" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_ucet_cz')); ?>" class="regular-text">
                <p class="description"><?php _e('Např. 123456789/0100 – zobrazí se ve faktuře místo IBANu.', 'ubytovani'); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php _e('Plátce DPH', 'ubytovani'); ?></th>
            <td><label><input type="checkbox" name="ubytovani_firma_dph" value="1" <?php checked(1, get_option('ubytovani_firma_dph')); ?>> <?php _e('Ano', 'ubytovani'); ?></label></td>
        </tr>
    </table>
    <?php
}

function ubytovani_stranka_fakturace_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Nastavení fakturace', 'ubytovani') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('ubytovani_nastaveni_fakturace');
    do_settings_sections('ubytovani-fakturace');
    submit_button(__('Uložit', 'ubytovani'));
    echo '</form>';
    echo '</div>';
}
