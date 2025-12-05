<?php
if (!defined('ABSPATH')) exit;

class ABH_Logic {

    /**
     * Obtiene feriados de la API de Chile y cachea por 24hrs
     */
    private static function get_holidays_api() {
        $year = date('Y');
        $transient_key = 'abh_holidays_' . $year;
        
        $holidays = get_transient($transient_key);

        if (false === $holidays) {
            $response = wp_remote_get("https://apis.digital.gob.cl/fl/feriados/{$year}");
            
            if (is_wp_error($response)) {
                return []; 
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            $holidays = [];
            if (!empty($data)) {
                foreach ($data as $h) {
                    $holidays[] = $h['fecha']; 
                }
            }
            set_transient($transient_key, $holidays, 24 * HOUR_IN_SECONDS);
        }
        
        return $holidays;
    }

    /**
     * Determina el estado actual del negocio
     */
    public static function get_current_status() {
        // Configura tu zona horaria si es necesario, o usa la de WP
        date_default_timezone_set('America/Santiago'); 
        
        $now = current_time('timestamp');
        $today_date = date('Y-m-d', $now);
        $current_time = date('H:i', $now);
        $day_keyword = strtolower(date('l', $now));

        // 1. Verificar Feriados
        $holidays = self::get_holidays_api();
        if (in_array($today_date, $holidays)) {
            return ['status' => 'closed', 'msg' => 'Cerrado (Feriado)', 'class' => 'holiday'];
        }

        // 2. Verificar Cierre Manual
        if (get_option("abh_{$day_keyword}_closed")) {
            return ['status' => 'closed', 'msg' => 'Cerrado hoy', 'class' => 'closed'];
        }

        // 3. Verificar Rango Horario
        $start = get_option("abh_{$day_keyword}_start");
        $end   = get_option("abh_{$day_keyword}_end");

        if ($start && $end) {
            if ($current_time >= $start && $current_time <= $end) {
                return ['status' => 'open', 'msg' => 'Abierto', 'class' => 'open'];
            }
        }

        return ['status' => 'closed', 'msg' => 'Cerrado', 'class' => 'closed'];
    }
}