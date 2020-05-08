<?php
/*
Plugin Name:PlusCrea Weather
Plugin URI:
Description: You can put weather your website.
Version: 1.0
Author: Ali YAKAR
Author URI: http://pluscrea.net
License: GPLv2
*/


function get_weather_data($city)
{
    $apiKey = "746316564e678e4689fed169eec69fe0";
    //$cityId = "London";
    $googleApiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . $city . "&appid=" . $apiKey;

    //echo $googleApiUrl;


    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    curl_close($ch);
    $weather_data = json_decode($response);
    return $weather_data;
}


function ip_details($IPaddress)
{
    $json       = file_get_contents("http://ipinfo.io/{$IPaddress}");
    $details    = json_decode($json);
    return $details;
}

class P_Weather_Widget extends WP_Widget
{
    // Main constructor
    public function __construct()
    {
        parent::__construct(
            'P_weather_widget',
            __('Weather Widget', 'text_domain'),
            array(
                'customize_selective_refresh' => true,
            )
        );
    }


    // The widget form (for the backend )
    public function form($instance)
    {

        $IPaddress  =   $_SERVER['REMOTE_ADDR'];
        $details    =   ip_details("160.155.96.251");

        $city = $details->city;
        // Set widget defaults

        // Parse current settings with defaults
        extract(wp_parse_args((array) $instance, $defaults)); ?>


        <?php // Text Field 
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('city')); ?>"><?php _e('City:', 'text_domain'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('city')); ?>" name="<?php echo esc_attr($this->get_field_name('city')); ?>" type="text" value="<?php echo $city; ?>" />
        </p>

    <?php }
    // Update widget settings
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['city']    = isset($new_instance['city']) ? wp_strip_all_tags($new_instance['city']) : '';
        return $instance;
    }
    // Display the widget
    public function widget($args, $instance)
    {
        extract($args);

        // Check the widget options
        $city     = isset($instance['city']) ? $instance['city'] : '';
        // WordPress core before_widget hook (always include )
        echo $before_widget;

        $data = get_weather_data($city);
        $currentTime = time();
        // WordPress core after_widget hook (always include )
    ?>
        <div class="report-container">
            <h4><?php echo $data->name; ?> Weather Status</h4>
            <div class="time">
                <div><?php echo date("l g:i a", $currentTime); ?></div>
                <div><?php echo date("jS F, Y", $currentTime); ?></div>
                <div><?php echo ucwords($data->weather[0]->description); ?></div>
            </div>
            <div class="weather-forecast">
                <img src="http://openweathermap.org/img/w/<?php echo $data->weather[0]->icon; ?>.png" width="100px" height="100px" />
                <?php echo $data->main->temp_max; ?> °C <span class="min-temperature"><?php echo $data->main->temp_min; ?> °C </span>
            </div>
            <div class="time">
                <div>Humidity: <?php echo $data->main->humidity; ?> %</div>
                <div>Wind: <?php echo $data->wind->speed; ?> km/h</div>
            </div>
        </div>
<?php
        echo $after_widget;
    }
}
// Register the widget
function p_register_weather_widget()
{
    register_widget('P_Weather_Widget');
}
add_action('widgets_init', 'p_register_weather_widget');
