<?php

function ubytovani_nastaveni_cen_stranka() {
    if (isset($_POST['ubytovani_ceny_ulozit'])) {
        update_option('ubytovani_poplatek_osoba', floatval($_POST['poplatek_osoba']));
        update_option('ubytovani_max_osob', intval($_POST['ubytovani_max_osob']));

        for ($i = 1; $i <= 10; $i++) {
            $klic = 'ubytovani_cena_' . $i . '_osoba';
            if (isset($_POST[$klic])) {
                update_option($klic, floatval($_POST[$klic]));
            }
        }

        echo '<div class="updated"><p>' . __('Ceny byly uloženy.', 'ubytovani') . '</p></div>';
    }

    $poplatek = get_option('ubytovani_poplatek_osoba', 0);
    $max_osob = get_option('ubytovani_max_osob', 2);
    ?>
    <div class="wrap">
        <h1><?php _e('Nastavení cen ubytování', 'ubytovani'); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="ubytovani_max_osob"><?php _e('Max. počet osob', 'ubytovani'); ?></label></th>
                    <td>
                        <select name="ubytovani_max_osob" id="ubytovani_max_osob">
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo '<option value="' . $i . '"' . selected($max_osob, $i, false) . '>' . $i . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="poplatek_osoba"><?php _e('Turistický poplatek (Kč / osoba / noc)', 'ubytovani'); ?></label></th>
                    <td><input type="number" step="1" min="0" name="poplatek_osoba" value="<?php echo esc_attr($poplatek); ?>" class="small-text"></td>
                </tr>

                <?php
                $zobrazit_do = intval($max_osob);
                for ($i = 1; $i <= $zobrazit_do; $i++) {
                    $klic = 'ubytovani_cena_' . $i . '_osoba';
                    $cena = get_option($klic, '');
                    echo '<tr class="radek-osoba">';
                    echo '<th><label for="' . $klic . '">' . sprintf(__('Cena pro %d %s (Kč / noc)', 'ubytovani'), $i, $i === 1 ? __('osobu', 'ubytovani') : __('osoby', 'ubytovani')) . '</label></th>';
                    echo '<td><input type="number" step="1" min="0" name="' . $klic . '" value="' . esc_attr($cena) . '" class="small-text"></td>';
                    echo '</tr>';
                }
                ?>
            </table>
            <p class="submit">
                <input type="submit" name="ubytovani_ceny_ulozit" class="button-primary" value="<?php esc_attr_e('Uložit změny', 'ubytovani'); ?>">
            </p>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectInput = document.querySelector('[name="ubytovani_max_osob"]');
        const rows = document.querySelectorAll('.radek-osoba');

        function zobrazRadky() {
            const max = parseInt(selectInput.value) || 1;
            rows.forEach((row, index) => {
                if (index < max) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        selectInput.addEventListener('input', zobrazRadky);
        zobrazRadky(); // inicializace při načtení
    });
    </script>
<?php
}
