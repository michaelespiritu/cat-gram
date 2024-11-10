<?php

/**
 * Plugin Name: Cat Gram
 * Author:      Michael Espiritu
 * Description: Cat Image Generator. Visit https://api.thecatapi.com/v1/breeds for breed list and get the ID. Example: "bslo" for British Longhair
 * Version:     0.1.0
 * License:     GPL-2.0+
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class CatGram
{
  public function __construct()
  {
    //Shortcode [cat_gram]
    add_shortcode('cat_gram', [$this, 'cat_gram_shortcode']);

    // Add settings page
    add_action('admin_menu', [$this, 'add_settings_page']);

    // Register settings
    add_action('admin_init', [$this, 'register_settings']);
  }

  public function add_settings_page()
  {
    add_options_page(
      'Cat Gram Settings',
      'Cat Gram',
      'manage_options',
      'cat-gram-settings',
      [$this, 'settings_page_html']
    );
  }

  public function register_settings()
  {
    register_setting('cat_gram_option_group', 'cat_gram_api_key', [
      'sanitize_callback' => 'sanitize_text_field'
    ]);

    add_settings_section(
      'cat_gram_settings_section',
      'API Settings',
      null,
      'cat-gram-settings'
    );

    add_settings_field(
      'cat_gram_api_key',
      'Cat API Key',
      [$this, 'api_key_field_callback'],
      'cat-gram-settings',
      'cat_gram_settings_section'
    );
  }

  public function api_key_field_callback()
  {
    $api_key = get_option('cat_gram_api_key');
    echo '<input type="text" id="cat_gram_api_key" name="cat_gram_api_key" value="' . esc_attr($api_key) . '" />';
  }

  public function settings_page_html()
  {
?>
    <div class="wrap">
      <h1>Cat Gram Settings</h1>
      <form method="post" action="options.php">
        <?php
        settings_fields('cat_gram_option_group');
        do_settings_sections('cat-gram-settings');
        submit_button();
        ?>
      </form>
    </div>
<?php
  }

  // Handle the [cat_gram] shortcode
  public function cat_gram_shortcode($atts)
  {
    //attribute for shortcode [cat_gram breed="pers" class="cat-img"]
    //https://api.thecatapi.com/v1/breeds check for cat breed and get ID
    $atts = shortcode_atts([
      'breed' => 'pers', //default to persian if no breed given.
      'class' => '',
    ], $atts, 'cat_gram');

    //sanitize the breed attr
    $breed = sanitize_text_field($atts['breed']);

    //sanitize the class attr
    $class = sanitize_html_class($atts['class']);

    //if [cat_gram breed=""]
    if (empty($breed)) {
      $breed = 'pers';
    }

    //return the result of get_cat_image
    return $this->get_cat_image($breed, $class);
  }

  private function get_cat_image($breed, $class)
  {
    $api_key = get_option('cat_gram_api_key');

    // If API key is not set, return message
    if (empty($api_key)) {
      return '<p>Please set your Cat API key.</p>';
    }

    // encode the $breed attribute to be used as a query of API Url and assign it to a variable.
    $api_url = 'https://api.thecatapi.com/v1/images/search?breed_ids=' . urlencode($breed);

    //Send get request to cat api with proper api key
    $response = wp_remote_get($api_url, [
      'headers' => [
        'x-api-key' => $api_key
      ]
    ]);

    //if there is error, return immediately.
    if (is_wp_error($response)) {
      return '<p>Something went wrong. Please try again later.</p>';
    }

    //retrieve the response and json_decode it.
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    //If class attribute is present insert it to the image.
    $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';

    //Check if api response has image url
    if (!empty($data[0]->url)) {
      return '<img src="' . esc_url($data[0]->url) . '"' . $class_attr .
        ' alt="' . esc_attr($data[0]->breeds[0]->description ?? 'Cat Gram') . '" />';
    }

    return '<p>No cat photo found.</p>';
  }
}

new CatGram();
