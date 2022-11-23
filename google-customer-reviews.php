<?php
 /**
 * Plugin Name:       Google Review
 * Plugin URI:        http://www.webmasteryagency.com
 * Description:       Permite que google interactue con el cliente para solicitar una calificacion y lo asigna a tu ID de comerciante para que lo puedas mostrar desde el front de tu web
 * Version:           1.1.3
 * Requires at least: 5.2
 * Requires PHP:      7.2.2
 * Author:            Jose Pinto
 * Author URI:        http://www.webmasteryagency.com
 * License:           GPL v3 or later
 * Domain Path: /lang
 * Text Domain _JPinto
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'init', 'ecr_woocheck' );
function ecr_woocheck () {
  if (class_exists( 'WooCommerce' )) {
    if( get_option( 'ecr_merch_id' ) ) {
      add_action('woocommerce_thankyou', 'ecr_gcr_scripts');
      if( !get_option('disable_gcr_view_order') ) {
        add_action('woocommerce_view_order', 'ecr_gcr_scripts');
      }
    }else{
      add_action( 'admin_notices', 'ecr_gcr_missing_key_notice' );
    }
  }else{
    add_action( 'admin_notices', 'ecr_gcr_missing_wc_notice' );
  }
}


// Admin Error Messages

function ecr_gcr_missing_wc_notice() {
  ?>
  <div class="error notice">
      <p><?php _e( 'You need to install and activate WooCommerce in order to use Google Customer Reviews for WooCommerce!', 'ecr-google-customer-reviews' ); ?></p>
  </div>
  <?php
}

function ecr_gcr_missing_key_notice() {
  ?>
  <div class="update-nag notice">
      <p><?php _e( 'Por Favor <a href="options-general.php?page=ecr_gcr">Introduzca su Google Merchant ID</a> para usar Reseñas de Clientes en Google para WooCommerce!', 'ecr-google-customer-reviews' ); ?></p>
  </div>
  <?php
}

// Admin Settings Menu

add_action( 'admin_menu', 'ecr_gcr_menu' );
function ecr_gcr_menu(){
  add_options_page( 'Google Customer Reviews for WooCommerce',
                'Google Customer Reviews', 
                'manage_options', 
                'ecr_gcr', 
                'ecr_gcr_page' );
  add_action( 'admin_init', 'update_ecr_gcr' );
}

// Register Settings

function update_ecr_gcr() {
  register_setting( 'ecr_gcr_settings', 'ecr_merch_id' );
  register_setting( 'ecr_gcr_settings', 'ecr_gcr_lang' );
  register_setting( 'ecr_gcr_settings', 'ecr_delivery_days' );
  register_setting( 'ecr_gcr_settings', 'ecr_optin_style' );
  register_setting( 'ecr_gcr_settings', 'disable_gcr_view_order' );
  register_setting( 'ecr_gcr_settings', 'ecr_badge_enable' );
  register_setting( 'ecr_gcr_settings', 'ecr_badge_isshop' );
  register_setting( 'ecr_gcr_settings', 'ecr_badge_style' );
  register_setting( 'ecr_gcr_settings', 'ecr_gtin_field' );
  register_setting( 'ecr_gcr_settings', 'ecr_display_gtin_meta' );
  
}

// Admin Settings Page

function ecr_gcr_page(){
?>
<div class="wrap">
  <h1>Google Customer Reviews for WooCommerce</h1>
  <p>Pegue su ID de comerciante de Google a continuación y haga clic en "Guardar cambios" para habilitar la integración de Reseñas de clientes de Google.</p>
  <form method="post" action="options.php">
    <?php settings_fields( 'ecr_gcr_settings' ); ?>
    <?php do_settings_sections( 'ecr_gcr_settings' ); ?>
    <h2>Configuración del comerciante</h2>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Google Merchant ID:</th>
      <td><input type="text" name="ecr_merch_id" value="<?php echo get_option( 'ecr_merch_id' ); ?>" data-lpignore="true" />
      <p class="description"><a href="https://merchants.google.com" target="_blank">Haga clic aquí para obtener su ID de comerciante de Google &raquo;</a><br/>Además, asegúrese de tener <a href="https://merchants.google.com/mc/programs" target="_blank">Habilitado el programa Reseñas de clientes</a> dentro de su cuenta de Google Merchant.</p></td>
      </tr>
      <tr valign="top">
      <th scope="row">Idioma:</th>
      <td>
      <select name="ecr_gcr_lang" value="<?php $lang = get_option( 'ecr_gcr_lang' ); echo $lang; ?>">
      <?php
      $languages = array(
        '' => 'Auto-detect',
        'af' => 'Afrikaans',
        'ar-AE' => 'Arabic (United Arab Emirates)',
        'cs' => 'Czech',
        'da' => 'Danish',
        'de' => 'German',
        'en_AU' => 'English (Australia)',
        'en_GB' => 'English (United Kingdom)',
        'en_US' => 'English (United States)',
        'es' => 'Spanish',
        'es-419' => 'Spanish (Latin America)',
        'fil' => 'Filipino',
        'fr' => 'French',
        'ga' => 'Irish',
        'id' => 'Indonesian',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ms' => 'Malay',
        'nl' => 'Dutch',
        'no' => 'Norwegian',
        'pl' => 'Polish',
        'pt_BR' => 'Portuguese (Brazil)',
        'pt_PT' => 'Portuguese (Portugal)',
        'ru' => 'Russian',
        'sv' => 'Swedish',
        'tr' => 'Turkish',
        'zh-CN' => 'Chinese (China)',
        'zh-TW' => 'Chinese (Taiwan)'
      );
      foreach($languages as $code => $label) {
        echo '<option value="'.$code.'" ';
        if($lang==$code)echo 'selected';
        echo '>'.$label.'</option>';
      }
      ?>
      </select>
      </td>
      </tr>
    </table>
    <hr />
    <h2>Configuracion del PopUp para la encuesta</h2>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Posición del PopUp:</th>
      <td><select name="ecr_optin_style" value="<?php $style = get_option( 'ecr_optin_style' ); echo $style; ?>">
        <option value="CENTER_DIALOG" <?php if($style=='CENTER_DIALOG')echo 'selected';?>>Center</option>
        <option value="TOP_LEFT_DIALOG" <?php if($style=='TOP_LEFT_DIALOG')echo 'selected';?>>Top Left</option>
        <option value="TOP_RIGHT_DIALOG" <?php if($style=='TOP_RIGHT_DIALOG')echo 'selected';?>>Top Right</option>
        <option value="BOTTOM_LEFT_DIALOG" <?php if($style=='BOTTOM_LEFT_DIALOG')echo 'selected';?>>Bottom Left</option>
        <option value="BOTTOM_RIGHT_DIALOG" <?php if($style=='BOTTOM_RIGHT_DIALOG')echo 'selected';?>>Bottom Right</option>
        <option value="BOTTOM_TRAY" <?php if($style=='BOTTOM_TRAY')echo 'selected';?>>Bottom Tray</option>
      </select></td>
      </tr>
      <tr valign="top">
      <th scope="row">Desactivar PopUp en mi cuenta > Ver pedido:</th>
      <td><input type="checkbox" name="disable_gcr_view_order" value="true" <?php if(get_option('disable_gcr_view_order')=='true')echo 'checked'; ?>/>
      <p class="description">El PopUp de la encuesta se mostrará en la página "Gracias" después de realizar un pedido. Pero, si también desea que el PopUp se muestre cuando el usuario vea su pedido en la sección "Mi cuenta", deje esto sin marcar.</p></td>
      </tr>
      <tr valign="top">
      <th scope="row">Entrega estimada (Dias):</th>
      <td><input type="number" name="ecr_delivery_days" value="<?php echo get_option( 'ecr_delivery_days' ); ?>"/>
      <p class="description">Google quiere saber cuántos días tardará el cliente en recibir el producto. Agregarán algunos días más para asegurarse de que el cliente haya tenido tiempo de usar el producto y luego enviarán la encuesta de revisión al cliente.</p></td>
      </tr>
    </table>
    <hr />
    <h2>Configuración de revisión de productos (<em>Opcional</em>)</h2>
    <p>Estos ajustes son opcionales. Si vende productos que tienen un GTIN como UPC, EAN o ISBN, esto le permite recopilar reseñas de productos además de sus reseñas de vendedor. Sin GTIN, aún recopilará reseñas de vendedores.</p>
    <table class="form-table">
      </tr>
      <tr valign="top">
        <th scope="row">GTIN:<p class="description">(Global Trade Item Number)</p></th>
        <?php 
        function generate_product_meta_keys(){
          global $wpdb;
          $query = "
            SELECT DISTINCT($wpdb->postmeta.meta_key), $wpdb->postmeta.meta_value 
            FROM $wpdb->posts 
            LEFT JOIN $wpdb->postmeta 
            ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
            WHERE ($wpdb->posts.post_type = 'product' 
            OR $wpdb->posts.post_type = 'product_variation') 
            AND $wpdb->postmeta.meta_key != '' 
            AND $wpdb->postmeta.meta_key NOT LIKE '_oembed_%' 
            GROUP BY $wpdb->postmeta.meta_key 
            ORDER BY $wpdb->posts.post_type, $wpdb->postmeta.meta_key
          ";
          $meta_keys = $wpdb->get_results($query);
          //set_transient('product_meta_keys', $meta_keys, 60*60*24); # create 1 Day Expiration
          return $meta_keys;
        }
        function get_product_meta_keys(){
          //$cache = get_transient('product_meta_keys');
          //$meta_keys = $cache ? $cache : generate_product_meta_keys();
          // Uncomment the below line to bypass the transient cache
          $meta_keys = generate_product_meta_keys();
          return $meta_keys;
        }
        $meta_keys = get_product_meta_keys();
        $gtin_field = get_ecr_gtin_field();
        ?>
        <td>
        <?php //print_r($meta_keys); ?>
        <select name="ecr_gtin_field" value="<?php echo $gtin_field; ?>">
          <option value="_gtin" <?php if($gtin_field == '_gtin') echo 'selected';?>>- DEFAULT (_gtin) -</option>
          <option value="NO_GTIN" <?php if($gtin_field == 'NO_GTIN') echo 'selected';?>>- NONE -</option>
          <?php
          if(!empty($meta_keys) && is_array($meta_keys)) {
            foreach($meta_keys as $r) { 
              echo '<option value="'.$r->meta_key.'" '.(($gtin_field==$r->meta_key) ? 'selected' : '').'>'.$r->meta_key.' ('. ( function_exists('mb_strimwidth') ? mb_strimwidth(sanitize_text_field($r->meta_value), 0, 20, "...") : substr(sanitize_text_field($r->meta_value), 0, 20) ) .')</option>';
            }
          }
          ?>
        </select>
        <p class="description">Esto solo es necesario si desea recopilar reseñas de productos. Con esto configurado en "- NINGUNO -", la ventana emergente de la encuesta aún recopilará reseñas de comerciantes.</p><p class="description">El valor predeterminado es '_gtin'. Sin embargo, si tiene otro complemento que administra el campo GTIN para sus productos, elija ese campo del menú desplegable.</p></td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <h4>¿Cómo agregar GTIN a los productos para habilitar Reseñas de productos?</h4>
        </th>
        <td>
          <p class="description">Edite cada producto en WooCommerce. En el panel "Datos del producto", haga clic en la pestaña "Inventario". Debería ver un nuevo campo con la etiqueta "GTIN". Ingrese su UPC, EAN o ISBN para el producto y haga clic en "Actualizar".</p>
        </td>
      </tr>
      <tr valign="top">
      <th scope="row">Mostrar GTIN en el meta del producto en el front-end:</th>
      <td><input type="checkbox" name="ecr_display_gtin_meta" value="true" <?php if(get_option('ecr_display_gtin_meta')=='true')echo 'checked'; ?>/>
      <p class="description">Si está marcado, el GTIN se mostrará en la página única del producto junto con la lista de categorías y etiquetas.</p></td>
      </tr>
    </table>
    <hr />
    <h2>Configuración de la insignia de calificación</h2>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Habilitar insignia de calificación:</th>
      <td><input type="checkbox" name="ecr_badge_enable" value="true" <?php if(get_option('ecr_badge_enable')=='true')echo 'checked'; ?>/>
      <p class="description">Puede mantener la insignia desactivada hasta que esté listo para mostrarla. Por lo general, Google requiere al menos 150 reseñas durante el último año antes de que las calificaciones del vendedor aparezcan en la insignia de reseñas de clientes de Google.</p></td>
      </tr>
      <tr valign="top">
      <th scope="row">Solo mostrar insignia en la tienda:</th>
      <td><input type="checkbox" name="ecr_badge_isshop" value="true" <?php if(get_option('ecr_badge_isshop')=='true')echo 'checked'; ?>/>
      <p class="description">Si está marcada, la insignia solo se mostrará si woocommerce esta activado.</p></td>
      </tr>
      <tr valign="top">
      <th scope="row">Posición de la insignia de calificación:</th>
      <td><select name="ecr_badge_style" value="<?php $style = get_option( 'ecr_badge_style' ); echo $style; ?>">
        <option value="none" <?php if($style=='none')echo 'selected';?>>None</option>
        <option value="BOTTOM_LEFT" <?php if($style=='BOTTOM_LEFT')echo 'selected';?>>Bottom Left</option>
        <option value="BOTTOM_RIGHT" <?php if($style=='BOTTOM_RIGHT')echo 'selected';?>>Bottom Right</option>
      </select></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
<?php
}

function ecr_gcr_scripts($order_id) {
  global $woo_order_id;
  $woo_order_id = $order_id;
  add_action( 'wp_footer', 'ecr_gcr_scripts_footer', 999, 1 );
}
  
function ecr_gcr_scripts_footer() {
  global $woo_order_id;
  $order = new WC_Order( $woo_order_id );
  $items = $order->get_items();
  $gtins = [];
  $gtin_field = get_ecr_gtin_field();
  if($gtin_field && $gtin_field != 'NO_GTIN') {
    foreach($items as $item) {
      $product_id = version_compare( WC_VERSION, '3.0', '<' ) ? $item['product_id'] : $item->get_product_id();
      $variation_id = version_compare( WC_VERSION, '3.0', '<' ) ? $item['variation_id'] : $item->get_variation_id();
        // Check if product has variation.
        if ($variation_id) { 
          $item_id = $item['variation_id'];
        } else {
          $item_id = $item['product_id'];
        }
      $gtin = get_ecr_gtin_value( $item_id, false );
      //echo '<!--'.$item_id.':'.$gtin.'-->';
      if($gtin) {
        if(is_array($gtin)) $gtin = array_shift($gtin);
        $gtins[] = ['gtin' => sanitize_text_field($gtin)];
      }
    }
  }
  ?><!-- BEGIN GCR Opt-in Module Code -->
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn"
  async defer>
</script>

<script>
  window.renderOptIn = function() { 
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render({
        "merchant_id": <?php echo get_option('ecr_merch_id'); ?>,
        "order_id": "<?php echo $order->get_id(); ?>",
        "email": "<?php echo is_callable( array( $order, 'get_billing_email' ) ) ? $order->get_billing_email() : $order->billing_email; ?>",
        "delivery_country": "<?php echo is_callable( array( $order, 'get_billing_country' ) ) ? $order->get_billing_country() : $order->billing_country; ?>",
        "estimated_delivery_date": "<?php $order_date = is_callable( array( $order, 'get_date_created' ) ) ? $order->get_date_created() : $order->order_date;
        echo date('Y-m-d', strtotime($order_date.' + '. apply_filters('gcr_delivery_days', (int)get_option('ecr_delivery_days'), $order ).' days') ); ?>",
        "opt_in_style": "<?php echo get_option( 'ecr_optin_style' ); ?>",
        <?php if($gtins) { echo '"products": ' . json_encode($gtins); } ?> 
      });
    });
  }
</script>
<!-- END GCR Opt-in Module Code -->

<!-- BEGIN GCR Language Code -->
<script>
  window.___gcfg = {
    lang: '<?php echo get_option( 'ecr_gcr_lang' ); ?>'
  };
</script>
<!-- END GCR Language Code -->
<?php 
}

add_action( 'wp_footer', 'gcr_badge' );
function gcr_badge() {
  $show_badge = apply_filters('ecr_show_gcr_badge', true);
  if(get_option('ecr_badge_enable') && $show_badge){
    if(get_option('ecr_badge_isshop') && !is_woocommerce()){
      //do nothing
    }elseif($show_badge){
      $style = get_option('ecr_badge_style');
      if($style != 'none') {
      ?>
    <!-- BEGIN GCR Badge Code -->
    <script src="https://apis.google.com/js/platform.js?onload=renderBadge"
      async defer>
    </script>

    <script>
      window.renderBadge = function() {
        var ratingBadgeContainer = document.createElement("div");
        document.body.appendChild(ratingBadgeContainer);
        window.gapi.load('ratingbadge', function() {
          window.gapi.ratingbadge.render(
            ratingBadgeContainer, {
              "merchant_id": <?php echo get_option('ecr_merch_id'); ?>,
              "position": "<?php echo $style; ?>"
            });
        });
      }
    </script>
    <!-- END GCR Badge Code -->

    <!-- BEGIN GCR Language Code -->
    <script>
      window.___gcfg = {
        lang: '<?php echo get_option( 'ecr_gcr_lang' ); ?>'
      };
    </script>
    <!-- END GCR Language Code -->
    <?php
      }
    }
  }
}

/** 
 * Adding Custom GTIN Meta Field
 * Save meta data to DB
 */
// add GTIN input field
remove_action('woocommerce_product_options_inventory_product_data', 'woocom_simple_product_gtin_field', 10);
add_action('woocommerce_product_options_inventory_product_data','ecr_gcr_product_gtin_field');
function ecr_gcr_product_gtin_field(){
  global $woocommerce, $post;
  $gtin_field = get_ecr_gtin_field();
  if(in_array($gtin_field, ['NO_GTIN', '_sku'])) return;
  $product = new WC_Product(get_the_ID());
  echo '<div id="gtin_attr" class="options_group">';
  //add GTIN field for simple product
  woocommerce_wp_text_input( 
    array(	
      'id' => $gtin_field,
      'label' => 'GTIN',
      'desc_tip' => 'true',
      'description' => 'Enter the Global Trade Item Number (UPC,EAN,ISBN)')
  );
  echo '</div>';
}
// save simple product GTIN
add_action('woocommerce_process_product_meta','ecr_gcr_product_gtin_save');
function ecr_gcr_product_gtin_save($post_id){
  $gtin_field = get_ecr_gtin_field();
  if($gtin_field == 'NO_GTIN') return;
  $gtin_post = $_POST[$gtin_field];
  // save the gtin
  if(isset($gtin_post)){
    update_post_meta($post_id, $gtin_field, esc_attr($gtin_post));
  }
  // remove if GTIN meta is empty
  $gtin_data = get_post_meta($post_id, $gtin_field, true);
  if (empty($gtin_data)){
    delete_post_meta($post_id, $gtin_field, '');
  }
}

// Add Variation Custom fields

//Display Fields in admin on product edit screen for variations
add_action( 'woocommerce_product_after_variable_attributes', 'ecr_gcr_woo_variable_fields', 10, 3 );
function ecr_gcr_woo_variable_fields( $loop, $variation_data, $variation ) {
  $gtin_field = get_ecr_gtin_field();
  if($gtin_field == 'NO_GTIN') return;
  $product = new WC_Product(get_the_ID());
  echo '<div class="variation-custom-fields">';
      // Text Field
      woocommerce_wp_text_input(
        array(
          'id' => $gtin_field.'_variation['.$loop.']',
          'label' => 'GTIN',
          'desc_tip' => 'true',
          'description' => 'Enter the Global Trade Item Number (UPC,EAN,ISBN)',
          'value' => get_ecr_gtin_value($variation->ID)
        )
      );
  echo "</div>"; 
}

//Save variation fields values
add_action( 'woocommerce_save_product_variation', 'ecr_gcr_save_variation_fields', 10, 2 );
function ecr_gcr_save_variation_fields( $variation_id, $i) {
    $gtin_field = get_ecr_gtin_field();
    if($gtin_field == 'NO_GTIN') return;
    // Check for variation gtin array
    if(!is_array($_POST[$gtin_field.'_variation'])) return;
    $gtin_post = stripslashes( $_POST[$gtin_field.'_variation'][$i] );
    // save the gtin
    if(isset($gtin_post)){
      update_post_meta( $variation_id, $gtin_field, esc_attr( $gtin_post ) );
    }
    // remove if GTIN meta is empty
    $gtin_data = get_post_meta($variation_id, $gtin_field, true);
    if (empty($gtin_data)){
      delete_post_meta($variation_id, $gtin_field, '');
    }
}

//Display GTIN in the product meta on the front-end
add_action( 'woocommerce_product_meta_end', 'ecr_gcr_display_gtin_meta' );
function ecr_gcr_display_gtin_meta() {
  global $post;
  $display = get_option( 'ecr_display_gtin_meta' );
  if( $display ) {
    $gtin_field = get_ecr_gtin_field();
    if($gtin_field == 'NO_GTIN') return;
    $gtin = get_post_meta( $post->ID, $gtin_field, true );
    if( $gtin && !is_array($gtin) ) {
      echo '<span class="ecr-gtin">' . esc_html__( 'GTIN: ', 'ecr-google-customer-reviews' ) . '<span>' . $gtin . '</span></span>';
    }
  }
}

function get_ecr_gtin_field() {
  $gtin_field = get_option( 'ecr_gtin_field' );
  if(!$gtin_field) $gtin_field = '_gtin';
  return $gtin_field;
}

// Function to migrate old meta keys to new meta keys
function get_ecr_gtin_value( $post_id, $single = true ) {
  $gtin_field = get_option( 'ecr_gtin_field' );
  $post_type = get_post_type( $post_id );
  $gtin_value = '';
  if($post_type == 'product' || $post_type == 'product_variation') {
    $gtin_value = get_post_meta($post_id, $gtin_field, $single);
    if(empty($gtin_value) && $post_type == 'product_variation') {
      $gtin_value = get_post_meta($post_id, $gtin_field.'_variation', $single);
      // Copy the _gtin_variation to _gtin
      update_post_meta( $post_id, $gtin_field, $gtin_value );
    }
  }
  return $gtin_value;
}

// Hook into get_post_meta to fallback to _gtin_variation if _gtin is empty
add_filter( 'get_post_metadata', 'gtin_variation_postmeta_fallback', 10, 4 );
function gtin_variation_postmeta_fallback($metadata, $object_id, $meta_key, $single){
  // Here is the catch, add additional controls if needed (post_type, etc)
  $meta_needed = get_ecr_gtin_field();
  if(isset($meta_key) && $meta_needed == $meta_key) {
      remove_filter( 'get_post_metadata', 'gtin_variation_postmeta_fallback', 10 );
      $current_meta = get_post_meta( $object_id, $meta_needed, true );
      // Do what you need to with the meta value - translate, append, etc
      if(empty($current_meta)) {
        $current_meta = get_post_meta( $object_id, $meta_needed .'_variation', true );
        // Copy the _gtin_variation to _gtin
        update_post_meta( $object_id, $meta_needed, $current_meta );
      }
      add_filter('get_post_metadata', 'gtin_variation_postmeta_fallback', 10, 4);
      return $current_meta;
  }
  // Return original if the check does not pass
  return $metadata;
}

// Testing shortcode for checking post meta for products and variations [gcr_products_gtins]
add_shortcode( 'gcr_products_gtins', 'gcr_products_gtins' );
function gcr_products_gtins( $atts ){
  $q = new WP_Query(array(
    'post_type' => ['product', 'product_variation'],
    'posts_per_page' => '-1',
  ));
  ob_start();
  if($q->have_posts()) {
    while($q->have_posts()) : $q->the_post();
      the_title();
      echo ' :: '. get_post_meta(get_the_ID(), '_gtin', true);
      echo '<br>';
    endwhile;
    wp_reset_postdata();
  }
  return ob_get_clean();
}