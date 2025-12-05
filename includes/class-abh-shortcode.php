<?php
if (!defined('ABSPATH'))
    exit;

class ABH_Shortcode
{

    public function __construct()
    {
        add_shortcode('abh_hours', array($this, 'render'));
    }

    public function render($atts)
    {
        $status = ABH_Logic::get_current_status();

        $days_map = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo'
        ];

        // Obtenemos la zona horaria configurada en WP o la forzamos a Santiago para mostrar
        // (Como en Logic forzamos a Santiago, mostraremos eso)
        $timezone_label = "Santiago de Chile";

        ob_start();
        ?>
        <div class="abh-widget-card">
            <h4 class="abh-title">Horarios de Atención</h4>

            <div class="abh-badge <?php echo $status['class']; ?>">
                <?php echo $status['msg']; ?>
            </div>

            <ul class="abh-list">
                <?php
                $current_day = strtolower(date('l'));
                foreach ($days_map as $key => $label):
                    $is_closed = get_option("abh_{$key}_closed");

                    // Turno 1
                    $start1 = get_option("abh_{$key}_start");
                    $end1 = get_option("abh_{$key}_end");

                    // Turno 2
                    $start2 = get_option("abh_{$key}_start_2");
                    $end2 = get_option("abh_{$key}_end_2");

                    $time_html = '';

                    if ($is_closed) {
                        $time_html = '<span class="closed-text">Cerrado</span>';
                    } elseif ($start1 && $end1) {
                        // Formato Turno 1
                        $time_html .= date("H:i", strtotime($start1)) . " - " . date("H:i", strtotime($end1));

                        // Formato Turno 2 (si existe)
                        if ($start2 && $end2) {
                            $time_html .= "<br>" . date("H:i", strtotime($start2)) . " - " . date("H:i", strtotime($end2));
                        }
                    } else {
                        $time_html = '<span class="closed-text">Cerrado</span>';
                    }

                    $today_class = ($key === $current_day) ? 'is-today' : '';
                    ?>
                    <li class="<?php echo $today_class; ?>">
                        <span class="day-label"><?php echo $label; ?></span>
                        <span class="time-label"><?php echo $time_html; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($msg = get_option('abh_custom_msg')): ?>
                <div class="abh-msg"><?php echo esc_html($msg); ?></div>
            <?php endif; ?>

            <div class="abh-timezone">
                <small><i class="dashicons dashicons-globe"></i> Hora: <?php echo $timezone_label; ?></small>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}