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

                    $start1 = get_option("abh_{$key}_start");
                    $end1 = get_option("abh_{$key}_end");

                    $start2 = get_option("abh_{$key}_start_2");
                    $end2 = get_option("abh_{$key}_end_2");

                    $time_html = '';

                    if ($is_closed) {
                        $time_html = 'Cerrado';
                    } elseif ($start1 && $end1) {
                        // Turno 1
                        $time_html .= date("H:i", strtotime($start1)) . " - " . date("H:i", strtotime($end1));

                        // Turno 2 (Si existe)
                        if ($start2 && $end2) {
                            $time_html .= "<br>" . date("H:i", strtotime($start2)) . " - " . date("H:i", strtotime($end2));
                        }
                    } else {
                        $time_html = 'Cerrado';
                    }

                    $today_class = ($key === $current_day) ? 'is-today' : '';
                    ?>
                    <li class="<?php echo $today_class; ?>">
                        <span class="day-label"><?php echo $label; ?></span>
                        <span class="time-label" style="text-align: right;"><?php echo $time_html; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($msg = get_option('abh_custom_msg')): ?>
                <div class="abh-msg"><?php echo esc_html($msg); ?></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}