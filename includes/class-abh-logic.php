<?php
if (!defined('ABSPATH'))
    exit;

class ABH_Logic
{

    private static function get_holidays_api()
    {
        $year = date('Y');
        $transient_key = 'abh_holidays_' . $year;
        $holidays = get_transient($transient_key);

        if (false === $holidays) {
            $response = wp_remote_get("https://apis.digital.gob.cl/fl/feriados/{$year}");
            if (is_wp_error($response))
                return [];

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

    public static function get_current_status()
    {
        date_default_timezone_set('America/Santiago');

        $now = current_time('timestamp');
        $today_date = date('Y-m-d', $now);
        $current_time = date('H:i', $now);
        $day_keyword = strtolower(date('l', $now));

        // 1. Feriados
        $holidays = self::get_holidays_api();
        if (in_array($today_date, $holidays)) {
            return ['status' => 'closed', 'msg' => 'Cerrado (Feriado)', 'class' => 'holiday'];
        }

        // 2. Cerrado Manual
        if (get_option("abh_{$day_keyword}_closed")) {
            return ['status' => 'closed', 'msg' => 'Cerrado hoy', 'class' => 'closed'];
        }

        // 3. Verificar Horarios (Turno 1 y Turno 2)
        $start1 = get_option("abh_{$day_keyword}_start");
        $end1 = get_option("abh_{$day_keyword}_end");

        $start2 = get_option("abh_{$day_keyword}_start_2");
        $end2 = get_option("abh_{$day_keyword}_end_2");

        $is_open = false;

        // Chequear Turno 1
        if ($start1 && $end1) {
            if ($current_time >= $start1 && $current_time <= $end1) {
                $is_open = true;
            }
        }

        // Chequear Turno 2 (solo si no está abierto ya)
        if (!$is_open && $start2 && $end2) {
            if ($current_time >= $start2 && $current_time <= $end2) {
                $is_open = true;
            }
        }

        if ($is_open) {
            return ['status' => 'open', 'msg' => 'Abierto', 'class' => 'open'];
        }

        return ['status' => 'closed', 'msg' => 'Cerrado', 'class' => 'closed'];
    }
}