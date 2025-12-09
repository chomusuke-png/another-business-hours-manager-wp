<?php
if (!defined('ABSPATH')) exit;

class ABH_Shortcode {

    public function __construct() {
        add_shortcode('abh_hours', array($this, 'render'));
    }

    public function render($atts) {
        $status = ABH_Logic::get_current_status();
        
        // 1. Obtenemos la lista de feriados para comparar en la lista
        $holidays = ABH_Logic::get_holidays_api();

        $days_map = [
            'monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles',
            'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'
        ];
        
        $timezone_label = "Santiago de Chile"; 

        ob_start();
        ?>
        <div class="abh-widget-card">
            <h4 class="abh-title">Horarios de Atención</h4>
            
            <div class="abh-timezone-header">
                <small><i class="dashicons dashicons-globe"></i> Zona: <?php echo $timezone_label; ?></small>
            </div>
            
            <div class="abh-badge <?php echo $status['class']; ?>">
                <?php echo $status['msg']; ?>
            </div>

            <ul class="abh-list">
                <?php 
                $current_day_keyword = strtolower(date('l')); // hoy (ej: friday)
                
                foreach($days_map as $key => $label): 
                    
                    
                    // --- LÓGICA DE FECHAS ---
                    // Calculamos la fecha de este día de la semana actual
                    // 'monday this week', 'tuesday this week', etc.
                    $day_timestamp = strtotime($key . ' this week', current_time('timestamp'));
                    $day_date_str = date('Y-m-d', $day_timestamp);
                    
                    // Verificamos si este día específico es feriado
                    $is_holiday_today = in_array($day_date_str, $holidays);

                    // --- LÓGICA DE HORARIOS ---
                    $is_closed_manual = get_option("abh_{$key}_closed");
                    $start1 = get_option("abh_{$key}_start");
                    $end1   = get_option("abh_{$key}_end");
                    $start2 = get_option("abh_{$key}_start_2");
                    $end2   = get_option("abh_{$key}_end_2");
                    
                    $time_html = '';

                    // 1. Prioridad: ¿Es Feriado?
                    if ($is_holiday_today) {
                        $time_html = '<span class="holiday-text">Feriado</span>';
                    } 
                    // 2. ¿Está cerrado manualmente?
                    elseif ($is_closed_manual) {
                        $time_html = '<span class="closed-text">Cerrado</span>';
                    } 
                    // 3. Mostrar Horarios
                    elseif ($start1 && $end1) {
                        $time_html .= date("H:i", strtotime($start1)) . "-" . date("H:i", strtotime($end1));
                        
                        if ($start2 && $end2) {
                            $time_html .= ' <span class="separator">/</span> ' . date("H:i", strtotime($start2)) . "-" . date("H:i", strtotime($end2));
                        }
                    } else {
                        $time_html = '<span class="closed-text">Cerrado</span>';
                    }

                    $today_class = ($key === $current_day_keyword) ? 'is-today' : '';
                ?>
                    <li class="<?php echo $today_class; ?>">
                        <span class="day-label"><?php echo $label; ?></span>
                        <span class="time-label"><?php echo $time_html; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if($msg = get_option('abh_custom_msg')): ?>
                <div class="abh-msg"><?php echo esc_html($msg); ?></div>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }
}