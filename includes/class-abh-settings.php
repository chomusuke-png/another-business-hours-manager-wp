<?php
if (!defined('ABSPATH'))
    exit;

class ABH_Settings
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_fields'));
    }

    public function add_menu()
    {
        add_menu_page(
            'Gestión de Horarios',
            'Business Hours',
            'manage_options',
            'abh_hours',
            array($this, 'render_page'),
            'dashicons-clock',
            100
        );
    }

    public function register_fields()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            // Turno 1
            register_setting('abh_hours_group', "abh_{$day}_start");
            register_setting('abh_hours_group', "abh_{$day}_end");

            // Turno 2 (NUEVO)
            register_setting('abh_hours_group', "abh_{$day}_start_2");
            register_setting('abh_hours_group', "abh_{$day}_end_2");

            register_setting('abh_hours_group', "abh_{$day}_closed");
        }
        register_setting('abh_hours_group', 'abh_custom_msg');
    }

    public function render_page()
    {
        $days_labels = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo'
        ];
        ?>
        <div class="wrap">
            <h1>Configuración de Horarios</h1>
            <p>Configura los turnos de atención. Si tienes horario continuado, deja el "Turno 2" vacío.</p>

            <form method="post" action="options.php"
                style="background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 800px;">
                <?php settings_fields('abh_hours_group'); ?>
                <table class="form-table">
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Estado</th>
                            <th>Turno Mañana</th>
                            <th>Turno Tarde (Opcional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days_labels as $key => $label): ?>
                            <tr>
                                <td style="vertical-align: middle;"><strong><?php echo $label; ?></strong></td>
                                <td style="vertical-align: middle;">
                                    <label>
                                        <input type="checkbox" name="abh_<?php echo $key; ?>_closed" value="1" <?php checked(1, get_option("abh_{$key}_closed"), true); ?>>
                                        Cerrado
                                    </label>
                                </td>
                                <td>
                                    <input type="time" name="abh_<?php echo $key; ?>_start"
                                        value="<?php echo esc_attr(get_option("abh_{$key}_start")); ?>"> a
                                    <input type="time" name="abh_<?php echo $key; ?>_end"
                                        value="<?php echo esc_attr(get_option("abh_{$key}_end")); ?>">
                                </td>
                                <td>
                                    <input type="time" name="abh_<?php echo $key; ?>_start_2"
                                        value="<?php echo esc_attr(get_option("abh_{$key}_start_2")); ?>"> a
                                    <input type="time" name="abh_<?php echo $key; ?>_end_2"
                                        value="<?php echo esc_attr(get_option("abh_{$key}_end_2")); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <hr>

                <p>
                    <label><strong>Mensaje Extra:</strong></label>
                    <input type="text" name="abh_custom_msg" class="regular-text"
                        value="<?php echo esc_attr(get_option('abh_custom_msg')); ?>"
                        placeholder="Ej: Horario de colación de 14:00 a 15:00">
                </p>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}