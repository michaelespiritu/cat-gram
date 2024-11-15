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
    // Add shortcode [cat_gram]
    add_shortcode('cat_gram', [$this, 'cat_gram_shortcode']);

    add_action('init', [$this, 'register_block']);

    // Add settings page
    add_action('admin_menu', [$this, 'add_settings_page']);

    // Register settings
    add_action('admin_init', [$this, 'register_settings']);

    // Add Settings Link in plugin action link
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
  }

  public function register_block()
  {
    // Enqueue block editor assets
    wp_register_script(
      'cat-gram-block',
      plugin_dir_url(__FILE__) . 'build/index.js',
      ['wp-blocks', 'wp-element'],
      filemtime(plugin_dir_path(__FILE__) . 'build/index.js'),
      true
    );

    wp_register_style(
      'cat-gram-block-editor',
      plugin_dir_url(__FILE__) . 'assets/css/block-editor.css',
      [],
      filemtime(plugin_dir_path(__FILE__) . 'assets/css/block-editor.css')
    );

    register_block_type('cat-gram/block', [
      'editor_script' => 'cat-gram-block',
      'editor_style'  => 'cat-gram-block-editor',
      'render_callback' => [$this, 'render_cat_gram_block'],
    ]);
  }

  public function render_cat_gram_block($attributes, $content, $block)
  {
    $breed_id = isset($attributes['breed']) ? sanitize_text_field($attributes['breed']) : 'pers';
    $class = isset($block->attributes['className']) ? sanitize_text_field($block->attributes['className']) : '';

    $cat_image_html = $this->get_cat_image($breed_id, $class);

    return '<div class="cat-gram-block">' . $cat_image_html . '</div>';
  }


  public function add_settings_link($links)
  {
    $settings_link = '<a href="options-general.php?page=cat-gram-settings">Settings</a>';
    array_unshift($links, $settings_link);

    return $links;
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
      'sanitize_callback' => 'sanitize_text_field',
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

    add_settings_section(
      'cat_breed_section',
      'Cat Breeds',
      [$this, 'breed_settings_callback'],
      'cat-gram-settings'
    );
  }

  // Display the API key input field
  public function api_key_field_callback()
  {
    $api_key = get_option('cat_gram_api_key');
    echo '<input type="text" id="cat_gram_api_key" name="cat_gram_api_key" value="' . esc_attr($api_key) . '" />';
  }

  // Display the breed settings callback
  public function breed_settings_callback()
  {
    $api_url = 'https://api.thecatapi.com/v1/breeds';

    // Send GET request to the Cat Breed API
    $response = wp_remote_get($api_url);

    // If there is an error, return immediately
    if (is_wp_error($response)) {
      return '<p>Sorry, there was an issue connecting to the cat breeds service. Please try again later.</p>';
    }

    // Retrieve the response body and decode the JSON.
    $body = wp_remote_retrieve_body($response);
    $breeds = json_decode($body);

    // Check if the API response contains breeds data.
    if (empty($breeds)) {
      return '<p>No breeds found. Please check back later.</p>';
    }

    // Search form for breed
    $output = '<input type="text" id="breed-search" placeholder="Search for a breed..." style="margin-bottom: 20px; padding: 5px; width: 100%;">';

    // Example shortcode
    $output .= '<div style="width: 100%; text-align: center;"><h2 id="breed-example">Example: <strong>[cat_gram breed="abys" class="cat-img"]</strong> for <span style="color: red;">Abyssinian</span> cat breed.</h2>';

    $output .= '<p>Click on each breed to generate shortcode.</p></div>';

    // Wrap the breeds in a flex container
    $output .= '<div class="cat-breeds-container" style="display: flex; flex-wrap: wrap; gap: 5px;">';

    foreach ($breeds as $breed) {
      $breed_name = esc_html($breed->name);
      $breed_id = esc_html($breed->id);

      // Add each breed name with its ID in a styled container
      $output .= "<div class='cat-breed-item' data-breed-id='{$breed_id}' data-breed-name='{$breed_name}' style='flex: 1 0 20%; min-width: 150px; padding: 2px 5px; border: 1px solid #ddd; border-radius: 5px; text-align: center; cursor: pointer;'>";
      $output .= "<p><strong>{$breed_name}</strong></p>";
      $output .= "<p>Breed ID: {$breed_id}</p>";
      $output .= '</div>';
    }

    $output .= '</div>'; // Closing breed container

    echo $output;

    $this->enqueue_breed_js();
  }

  private function enqueue_breed_js()
  {
    wp_enqueue_script('cat-gram-js', plugin_dir_url(__FILE__) . 'assets/js/script.js', null, true);
    wp_enqueue_style('cat-gram-css', plugin_dir_url(__FILE__) . 'assets/css/styles.css', null, 'all');
  }

  public function settings_page_html()
  {
?>
    <div class="wrap">
      <h1>Cat Gram Settings</h1>

      <!-- Tab Navigation -->
      <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" id="api-tab">API Key</a>
        <a href="#" class="nav-tab" id="breeds-tab">Cat Breeds</a>
      </h2>

      <form method="post" action="options.php">
        <?php
        // Security fields and the options group
        settings_fields('cat_gram_option_group');
        ?>

        <!-- Tab Contents -->
        <div id="api-key-content">
          <h3>API Key Settings</h3>
          <p><strong>Cat API Key:</strong></p>
          <input type="text" id="cat_gram_api_key" name="cat_gram_api_key" value="<?php echo esc_attr(get_option('cat_gram_api_key')); ?>" />
          <br /><br />
          <!-- Submit Button -->
          <p>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'cat-gram'); ?>" />
          </p>
        </div>

        <div id="cat-breeds-content" style="display:none;">
          <h3>Cat Breeds</h3>
          <p>List of Available Cat Breeds:</p>
          <div class="cat-breeds-container" style="display: flex; flex-wrap: wrap; gap: 5px;">
            <?php echo $this->breed_settings_callback(); ?>
          </div>
        </div>

      </form>
    </div>
<?php
  }

  // Handle the [cat_gram] shortcode
  public function cat_gram_shortcode($atts)
  {
    // Attribute for shortcode [cat_gram breed="pers" class="cat-img"]
    // https://api.thecatapi.com/v1/breeds check for cat breed and get ID
    $atts = shortcode_atts([
      'breed' => 'pers', // default to persian if no breed given.
      'class' => '',
    ], $atts, 'cat_gram');

    // Sanitize the breed attribute
    $breed = sanitize_text_field($atts['breed']);

    // Sanitize the class attribute
    $class = sanitize_html_class($atts['class']);

    // If [cat_gram breed=""]
    if (empty($breed)) {
      $breed = 'pers';
    }

    // Return the result of get_cat_image
    return $this->get_cat_image($breed, $class);
  }

  private function get_cat_image($breed, $class = null)
  {
    $api_key = get_option('cat_gram_api_key');

    // If API key is not set, return message
    if (empty($api_key)) {
      return '<p>Please set your Cat API key.</p>';
    }

    // Encode the $breed attribute to be used as a query of API Url and assign it to a variable.
    $api_url = 'https://api.thecatapi.com/v1/images/search?breed_ids=' . urlencode($breed);

    // Send get request to cat api with proper API key
    $response = wp_remote_get($api_url, [
      'headers' => [
        'x-api-key' => $api_key
      ]
    ]);

    // If there is an error, return immediately.
    if (is_wp_error($response)) {
      return '<p>Something went wrong. Please try again later.</p>';
    }

    // Retrieve the response and json_decode it.
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    // If class attribute is present, insert it to the image.
    $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';

    // Check if API response has image URL
    if (!empty($data[0]->url)) {
      return '<img src="' . esc_url($data[0]->url) . '"' . $class_attr . ' alt="' . esc_attr($data[0]->breeds[0]->description ?? 'Cat Gram') . '" />';
    }

    return '<p>No cat photo found.</p>';
  }
}

new CatGram();
