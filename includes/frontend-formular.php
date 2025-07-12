<?php
function ubytovani_zobrazit_formular($atts = []) {
    
    $atts = shortcode_atts([
    'pokoj' => 1,
], $atts);

$pokoj_id = intval($atts['pokoj']);

if (!function_exists('ubytovani_ziskej_stav_licence') || (ubytovani_ziskej_stav_licence() !== 'aktivni' && $pokoj_id > 1)) {
    return '<p style="color:red;">' . __('Tento formulář je dostupný pouze v plné verzi pluginu. Aktuálně lze používat pouze pokoj č. 1.', 'ubytovani') . '</p>';
}


    ob_start();

    $cena1 = get_option('ubytovani_cena_1_osoba', 800);
    $cena2 = get_option('ubytovani_cena_2_osoby', 1200);
    $poplatek = get_option('ubytovani_poplatek_osoba', 30);
?>
<div id="ubytovani-form-wrapper"
     data-pokoj="<?php echo $pokoj_id; ?>"
     <?php for ($i = 1; $i <= 10; $i++): ?>
         <?php $cena = get_option('ubytovani_cena_' . $i . '_osoba', 0); ?>
         data-cena<?php echo $i; ?>="<?php echo esc_attr($cena); ?>"
     <?php endfor; ?>
     data-poplatek="<?php echo esc_attr($poplatek); ?>">


    <form id="formular-ubytovani" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
       <input type="hidden" name="dekovna_url" value="<?php echo esc_url(get_permalink(get_option('ubytovani_dekovna_stranka'))); ?>">
      <?php
        ?>
        <input type="hidden" name="pokoj_id" value="<?php echo $pokoj_id; ?>">
        
        <button type="button" class="reset-vyber full" data-pokoj="<?php echo $pokoj_id; ?>"><?php _e('Reset výběru', 'ubytovani'); ?></button>
        <input type="hidden" name="action" value="nova_rezervace">
        
        <label><?php _e('Datum příjezdu:', 'ubytovani'); ?>
            <input type="date" name="datum_prijezdu" id="datum_prijezdu" readonly required>
        </label>

        <label><?php _e('Datum odjezdu:', 'ubytovani'); ?>
            <input type="date" name="datum_odjezdu" id="datum_odjezdu" readonly required>
        </label>

        <label><?php _e('Počet nocí:', 'ubytovani'); ?>
            <input type="number" name="pocet_noci" readonly>
        </label>

        <div class="firma-checkbox-wrapper">
    <span><?php _e('Rezervace na firmu', 'ubytovani'); ?></span>
    <input type="checkbox" id="firma-check">
</div>
<input type="hidden" name="firma" id="firma-hidden" value="">


        <label><?php _e('Jméno:', 'ubytovani'); ?>
            <input type="text" name="jmeno" required>
        </label>

        <label><?php _e('Příjmení:', 'ubytovani'); ?>
            <input type="text" name="prijmeni" required>
        </label>

        <div id="firma-fields" class="full skryte">
            <label><?php _e('Název firmy:', 'ubytovani'); ?>
                <input type="text" name="firma">
            </label>

            <label><?php _e('IČO:', 'ubytovani'); ?>
                <input type="text" name="ico">
            </label>

            <label><?php _e('DIČ:', 'ubytovani'); ?>
                <input type="text" name="dic">
            </label>
        </div>

        <label><?php _e('Adresa:', 'ubytovani'); ?>
            <input type="text" name="adresa" required>
        </label>

        <label><?php _e('Stát:', 'ubytovani'); ?>
            <select name="stat" id="stat" required>
                <option value="" disabled selected hidden><?php _e('-- Vyberte stát --', 'ubytovani'); ?></option>
                <option value="Česká republika"><?php echo esc_html__('Česká republika', 'ubytovani'); ?></option>
                <option value="Slovensko"><?php echo esc_html__('Slovensko', 'ubytovani'); ?></option>
                <option value="Polsko"><?php echo esc_html__('Polsko', 'ubytovani'); ?></option>
                <option value="Německo"><?php echo esc_html__('Německo', 'ubytovani'); ?></option>
                <option value="Rakousko"><?php echo esc_html__('Rakousko', 'ubytovani'); ?></option>
                <option value="Jiná"><?php echo esc_html__('Jiná', 'ubytovani'); ?></option>

            </select>
        </label>

        <label><?php _e('Telefon:', 'ubytovani'); ?>
            <input type="tel" name="telefon" required>
        </label>

        <label><?php _e('E-mail:', 'ubytovani'); ?>
            <input type="email" name="email" required>
        </label>

        <label><?php _e('Číslo občanského průkazu:', 'ubytovani'); ?>
            <input type="text" name="cislo_op" required>
        </label>

        <div id="datum-narozeni-blok" class="full skryte">
            <label><?php _e('Datum narození:', 'ubytovani'); ?>
                <input type="date" name="datum_narozeni" id="datum_narozeni">
            </label>
        </div>

        <?php
$data = get_option('ubytovani_pokoje_nastaveni', []);
$max_osob = isset($data[$pokoj_id]) ? intval($data[$pokoj_id]) : 2;

?>
        <label><?php _e('Počet osob:', 'ubytovani'); ?>
        <?php
            $data = get_option('ubytovani_pokoje_nastaveni', []);
            $max_osob = isset($data[$pokoj_id]) ? intval($data[$pokoj_id]) : 2;

            ?>
            <select name="pocet_osob" id="pocet_osob" required>
                <option value="" disabled selected><?php _e('-- Vyberte --', 'ubytovani'); ?></option>
                <?php for ($i = 1; $i <= $max_osob; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i . ' ' . ($i === 1 ? __('osoba', 'ubytovani') : __('osob', 'ubytovani')); ?></option>
                <?php endfor; ?>
            </select>
        </label>

        <label class="full cena-label">
            <?php _e('Cena ubytování (Kč):', 'ubytovani'); ?> <span id="zobrazena-cena">–</span>
            <input type="hidden" name="cena">
        </label>

        <label class="full"><?php _e('Poznámka:', 'ubytovani'); ?>
            <textarea name="poznamka" rows="3"></textarea>
        </label>
            <button type="submit" class="full"><?php _e('Odeslat', 'ubytovani'); ?></button>
    </form>

    
<div id="rezervace-hlaseni" class="hlaseni skryte">
    <span id="rezervace-text"></span>
    <button id="rezervace-ok" type="button"><?php _e('OK', 'ubytovani'); ?></button>
</div>

    </div><!-- konec wrapperu -->

    <?php
    return ob_get_clean();
}

add_shortcode('ubytovani_formular', 'ubytovani_zobrazit_formular');

add_shortcode('rezervace-odeslana', 'ubytovani_dekovna_stranka_shortcode');

function ubytovani_dekovna_stranka_shortcode() {
    if (!isset($_GET['rezervace']) || !is_numeric($_GET['rezervace'])) {
        return '<p>' . __('Rezervace nebyla nalezena.', 'ubytovani') . '</p>';
    }

    global $wpdb;
    $id = intval($_GET['rezervace']);
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $rez = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$rez) {
        return '<p>' . __('Rezervace nebyla nalezena.', 'ubytovani') . '</p>';
    }

    ob_start();
    ?>
    <div class="rezervace-rekapitulace">
        <h2><?php _e('Vaše rezervace byla přijata', 'ubytovani'); ?></h2>
        <ul>
            <li><strong><?php _e('Jméno:', 'ubytovani'); ?></strong> <?php echo esc_html($rez->jmeno . ' ' . $rez->prijmeni); ?></li>
            <li><strong><?php _e('Termín:', 'ubytovani'); ?></strong> <?php echo date('j. n. Y', strtotime($rez->datum_prijezdu)) . ' – ' . date('j. n. Y', strtotime($rez->datum_odjezdu)); ?></li>
            <li><strong><?php _e('Počet osob:', 'ubytovani'); ?></strong> <?php echo esc_html($rez->pocet_osob); ?></li>
            <li><strong><?php _e('Cena:', 'ubytovani'); ?></strong> <?php echo number_format($rez->cena, 0, ',', ' ') . ' Kč'; ?></li>
            <li><strong><?php _e('Stav:', 'ubytovani'); ?></strong> <?php echo esc_html(str_replace('_', ' ', $rez->stav)); ?></li>
        </ul>
        <p><?php _e('Děkujeme. Vaše rezervace nyní čeká na schválení.', 'ubytovani'); ?></p>
    </div>
    <?php
    return ob_get_clean();
}


