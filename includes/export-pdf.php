<?php
// Registrace podstránky v administraci


add_action('admin_menu', function () {
    add_submenu_page(
        'rezervace-ubytovani',                // nebo jiný hlavní slug
        __('Export PDF', 'ubytovani'),        // název stránky
        __('Export PDF', 'ubytovani'),        // název v menu
        'manage_options',                     // oprávnění
        'ubytovani-nastaveni-export',         // slug URL
        'ubytovani_export_pdf_formular'       // funkce pro výpis
    );
});

// Funkce pro zobrazení formuláře pro export
function ubytovani_export_pdf_formular() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Kniha hostů - Export ubytovaných do PDF', 'ubytovani'); ?></h1>
        
        <form method="get" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" target="_blank">
            <input type="hidden" name="action" value="ubytovani_kniha_hostu_pdf">

            <label for="rok"><?php esc_html_e('Vyber rok:', 'ubytovani'); ?></label>
            <select name="rok" id="rok">
                <?php
                $soucasny = date('Y');
                for ($r = $soucasny; $r >= $soucasny - 10; $r--) {
                    echo "<option value='" . esc_attr($r) . "'>" . esc_html($r) . "</option>";
                }
                ?>
            </select>

            <button type="submit" class="button-primary" style="margin-left: 10px;">
                <?php esc_html_e('Stáhnout PDF', 'ubytovani'); ?>
            </button>
        </form>

        <p style="margin-top:20px; font-size: 11px; color: #888;">
            <?php esc_html_e('Pokud se po kliknutí nic nestane, zkontrolujte blokování vyskakovacích oken ve vašem prohlížeči.', 'ubytovani'); ?>
        </p>
    </div>
    <?php
}

