<?php
function ubytovani_dekovna_stranka_shortcode() {
    $html = get_option('ubytovani_dekovny_text', '');
    ob_start();

    echo '<div class="ubytovani-dekovna">';
    echo wp_kses_post($html);

    if (isset($_GET['jmeno']) && isset($_GET['email'])) {
        echo '<h3>' . __('Rekapitulace rezervace', 'ubytovani') . '</h3>';
        echo '<ul>';
        foreach ($_GET as $k => $v) {
            echo '<li><strong>' . esc_html($k) . ':</strong> ' . esc_html($v) . '</li>';
        }
        echo '</ul>';
    }

    echo '</div>';
    return ob_get_clean();
}
add_shortcode('rezervace-odeslana', 'ubytovani_dekovna_stranka_shortcode');
