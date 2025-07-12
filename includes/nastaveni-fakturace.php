<?php
add_action('admin_menu', function () {
  
});

function ubytovani_nastaveni_fakturace_stranka() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['ubytovani_fakturace_save'])) {
        check_admin_referer('ubytovani_fakturace_save_action');

        update_option('ubytovani_email_jmeno', sanitize_text_field($_POST['ubytovani_email_jmeno']));
        update_option('ubytovani_email_adresa', sanitize_email($_POST['ubytovani_email_adresa']));
        update_option('ubytovani_firma_nazev', sanitize_text_field($_POST['ubytovani_firma_nazev']));
        update_option('ubytovani_firma_adresa', sanitize_text_field($_POST['ubytovani_firma_adresa']));
        update_option('ubytovani_firma_ico', sanitize_text_field($_POST['ubytovani_firma_ico']));
        update_option('ubytovani_firma_dic', sanitize_text_field($_POST['ubytovani_firma_dic']));
        update_option('ubytovani_firma_ucet', sanitize_text_field($_POST['ubytovani_firma_ucet']));
        update_option('ubytovani_firma_ucet_cz', sanitize_text_field($_POST['ubytovani_firma_ucet_cz']));
        update_option('ubytovani_firma_dph', isset($_POST['ubytovani_firma_dph']) ? 1 : 0);
        update_option('ubytovani_smazat_data_pri_odinstalaci', isset($_POST['ubytovani_smazat_data_pri_odinstalaci']) ? 1 : 0);


        echo '<div class="notice notice-success is-dismissible"><p>' . __('Nastavení uloženo.', 'ubytovani') . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1><?php _e('Nastavení fakturace', 'ubytovani'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('ubytovani_fakturace_save_action'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ubytovani_email_jmeno"><?php _e('Jméno odesílatele e-mailů', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_email_jmeno" type="text" value="<?php echo esc_attr(get_option('ubytovani_email_jmeno', get_bloginfo('name'))); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ubytovani_email_adresa"><?php _e('E-mailová adresa odesílatele', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_email_adresa" type="email" value="<?php echo esc_attr(get_option('ubytovani_email_adresa', get_option('admin_email'))); ?>" class="regular-text"></td>
                </tr>

                <tr>
                    <th scope="row"><label for="ubytovani_firma_nazev"><?php _e('Název firmy', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_firma_nazev" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_nazev')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ubytovani_firma_adresa"><?php _e('Adresa', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_firma_adresa" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_adresa')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ubytovani_firma_ico"><?php _e('IČO', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_firma_ico" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_ico')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ubytovani_firma_dic"><?php _e('DIČ', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_firma_dic" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_dic')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ubytovani_firma_ucet"><?php _e('Číslo účtu – Mezinárodní formát IBAN', 'ubytovani'); ?></label></th>
                    <td><input name="ubytovani_firma_ucet" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_ucet')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ubytovani_firma_ucet_cz"><?php _e('Číslo účtu – český formát', 'ubytovani'); ?></label></th>
                    <td>
                        <input name="ubytovani_firma_ucet_cz" type="text" value="<?php echo esc_attr(get_option('ubytovani_firma_ucet_cz')); ?>" class="regular-text">
                        <p class="description"><?php _e('Např. 123456789/0100 – zobrazí se ve faktuře místo IBANu.', 'ubytovani'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Plátce DPH', 'ubytovani'); ?></th>
                    <td><label><input type="checkbox" name="ubytovani_firma_dph" value="1" <?php checked(1, get_option('ubytovani_firma_dph')); ?>> <?php _e('Ano', 'ubytovani'); ?></label></td>
                </tr>
                <tr valign="top">
    <th scope="row"><?php esc_html_e('Smazat data při odinstalaci', 'ubytovani'); ?></th>
    <td>
        <label>
            <input type="checkbox" name="ubytovani_smazat_data_pri_odinstalaci" value="1" <?php checked(get_option('ubytovani_smazat_data_pri_odinstalaci'), 1); ?> />
            <?php esc_html_e('Ano, chci trvale odstranit všechna data pluginu při jeho odinstalaci.', 'ubytovani'); ?>
        </label>
    </td>
</tr>

            </table>

            <?php submit_button(__('Uložit nastavení', 'ubytovani'), 'primary', 'ubytovani_fakturace_save'); ?>
        </form>
    </div>
    <?php
}
