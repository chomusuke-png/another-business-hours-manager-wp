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
            register_setting('abh_hours_group', "abh_{$day}_start");
            register_setting('abh_hours_group', "abh_{$day}_end");
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
            <form method="post" action="options.php"
                style="background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px;">
                <?php settings_fields('abh_hours_group'); ?>
                <table class="form-table">
                    <?php foreach ($days_labels as $key => $label): ?>
                        <tr>
                            <th scope="row"><?php echo $label; ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="abh_<?php echo $key; ?>_closed" value="1" <?php checked(1, get_option("abh_{$key}_closed"), true); ?>>
                                    Cerrado
                                </label> &nbsp;
                                <input type="time" name="abh_<?php echo $key; ?>_start"
                                    value="<?php echo esc_attr(get_option("abh_{$key}_start")); ?>"> a
                                <input type="time" name="abh_<?php echo $key; ?>_end"
                                    value="<?php echo esc_attr(get_option("abh_{$key}_end")); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th scope="row">Mensaje Extra</th>
                        <td><input type="text" name="abh_custom_msg" class="regular-text"
                                value="<?php echo esc_attr(get_option('abh_custom_msg')); ?>"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}