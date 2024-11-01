<?php
/**
 * Plugin Name: Shipping Rate By Zipcodes
 * Plugin URI: https://wordpress.org/plugins/shipping-rate-by-zipcodes
 * Description: Set Custom Shipping Rates By Different Zipcodes For Woocommerce.
 * Version: 1.1.0
 *
 * @package     shipping_rate_by_zipcodes
 * @author      Trident Technolabs
 * @copyright   https://tridenttechnolabs.com
 * @license     GPLv2 or later
 */

 
 if ( ! defined( 'WPINC' ) ) die;

 /** @class Wc Zipcodes Fee */

   class  WShippingRateByZipcodes {
    /**
     * Ship Rate By Zipcodes Version.
     * @var string
     */
    public $version = '1.1.0';
 
     /**
      * Stores notices.
      * @var array
      */
     private static $notices = [];
 
     /**
      * Logger context.
      * @var array
      */
     public $context = ['source' => 'zipcoderate'];
 
     /** The single instance of the class. */
     protected static $_instance = null;
 
     /**
      * Returns the *Singleton* instance of this class.
      *
      * @return Singleton The *Singleton* instance.
      */
     public static function instance() {
         if ( is_null( self::$_instance ) ) {
             self::$_instance = new self();
         }
         return self::$_instance;
     }
 
     /**
      * Shipping Rate By Zipcodes Constructor.
      */
     private function __construct()
     {
         $this->defineConstants();
         $this->init_hooks();
     }
 
     private function init_hooks()
     {
         /**
          * Activation/Deactivation
          */
         register_activation_hook(ZIPCODERATE_PLUGIN_FILE, [$this, 'activation']);
         register_deactivation_hook(ZIPCODERATE_PLUGIN_FILE, [$this, 'deactivation']);
 
         /**
          * Enqueue Scripts
          */
         add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
         add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
         
 
         /**
          * Check if WooCommerce is active
          */        
          if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
         /**
          * Shipping method init
          */
         add_action( 'woocommerce_shipping_init', [$this, 'zipcoderate_shipping_method'] );
         add_filter( 'woocommerce_shipping_methods', [$this, 'add_zipcoderate_shipping_method'] );
 
        
        // add script in footer
        add_action('wp_footer', [$this, 'footer_script']);
        
         // add settings link to plugin list
         add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'zipcoderate_plugin_settings_link']);
 
 
         }
         
     }
 
    
 
     public function activation()
     {
         global $wpdb;
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 
         $charset_collate = $wpdb->get_charset_collate();
         $table_name = $wpdb->prefix . "shiprate_zipcodes";
         $query = "CREATE TABLE IF NOT EXISTS $table_name (
             `id` int(11) AUTO_INCREMENT,
             `zip_code` VARCHAR(255) NOT NULL,
             `rate` DECIMAL(10,2) NOT NULL,
             `status` VARCHAR(25) NOT NULL DEFAULT 1,
             `create_date` DATETIME NOT NULL,
             PRIMARY KEY (id)
         ) AUTO_INCREMENT=1 $charset_collate;";
         dbDelta( $query );
     }
 
     public function deactivation() 
     {
         // deactivatation code
     }
 
     /**
      * Define Wc City Fee Constants.
      */
     private function defineConstants()
     {
         
         $this->define('ZIPCODERATE_PLUGIN_FILE', __FILE__);
         $this->define('ZIPCODERATE_VERSION', $this->version);
         $this->define('ZIPCODERATE', 'zipcoderate');
         
     }
 
     /**
      * Define constant if not already set.
      *
      * @param string      $name  Constant name.
      * @param string|bool $value Constant value.
      */
     private function define( $name, $value )
     {
         if (!defined($name)) {
             define($name, $value);
         }
     }

 
     /**
      * Enquene Scripts
      */
     public function enqueueScripts()
     {
         wp_enqueue_script('jquery');

     }
 
     /**
      * Enquene Admin Scripts
      */
     public function enqueueAdminScripts()
     {
         wp_enqueue_script('jquery');
         wp_enqueue_script(ZIPCODERATE, plugins_url('/js/zipcoderate-admin.js', ZIPCODERATE_PLUGIN_FILE), ['jquery'], ZIPCODERATE_VERSION);
     }
     
 
     function zipcoderate_shipping_method() {
         include_once "zipcoderate-method-class.php";
     }
 
     function zipcoderate_plugin_settings_link( $actions ) {
 
         $mylinks = array(
             '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=zipcoderate' ) . '">Settings</a>',
          );
 
          return array_merge($mylinks,  $actions  );
     }

     function add_zipcoderate_shipping_method( $methods ) {
         $methods[] = 'ZipRate_FlatShipRateZipcode_Method';
         return $methods;
     }
  
 
     public function footer_script(){
        ?>
        <script>
            jQuery( function($) {
                $('#billing_postcode').change(function(){
                    console.log('updated');
                    jQuery('body').trigger('update_checkout');
                });
            });
        </script>
        <?php
        }
 }
 
 /**
  * Returns the main instance of WC.
  *
  * @since  2.1
  * @return WooCommerce
  */
 function zipcoderate_shipping() {
     return WShippingRateByZipcodes::instance();
 }
 
 // Global for backwards compatibility.
 $GLOBALS['zipcoderate'] = zipcoderate_shipping();


 if(isset($_POST['submitexport'])){

 if (isset($_POST['export_csv'])) {
     global $wpdb;

 $table_name = $wpdb->prefix . 'shiprate_zipcodes';
 $results = $wpdb->get_results("SELECT * FROM $table_name");

 $header_row = array('zip_code', 'rate');
 $rows = array($header_row);

 foreach ($results as $result) {
     $row = array($result->zip_code, $result->rate);
     array_push($rows, $row);
 }

 $csv_data = '';
 foreach ($rows as $row) {
     $csv_data .= implode(',', $row) . "\n";
 }

 
 
     header('Content-Type: text/csv');
     header('Content-Disposition: attachment; filename=export.csv');
     echo $csv_data;
     exit;
 }

}


 if (isset($_POST['importsubmit'])) {

    global $wpdb;
    // Check if a file is uploaded
    if (!empty($_FILES['import_file']['tmp_name'])) {
        // Get the uploaded file details
        $file = $_FILES['import_file']['tmp_name'];

        $handle = fopen($file, 'r');
if ($handle !== false) {
    // Skip the header row
    $header = fgetcsv($handle);

         // Loop through the CSV data
    while (($data = fgetcsv($handle)) !== false) {
        // Retrieve the necessary data from each row
        $cityName = $data[0];
        $rate = $data[1];
$zipcodeTable =  $wpdb->prefix.'shiprate_zipcodes';
        // Check if the city already exists in the table
        $existingCity = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $zipcodeTable WHERE zip_code = %s", $cityName)
        );

        // Perform the insert or update operation based on whether the city exists or not
        if ($existingCity) {
            // City exists, perform an update
            $wpdb->update(
                $zipcodeTable,
                array('rate' => $rate),
                array('zip_code' => $cityName)
            );
        } else {
            // City does not exist, perform an insert
            $wpdb->insert(
                $zipcodeTable,
                array('zip_code' => $cityName, 'rate' => $rate)
            );
        }
    }
    fclose($handle);
                // Refresh the current page using JavaScript
                echo '<script> window.location.href = window.location.href; </script>';
                exit;
        } else {
           // Show an error message if the file cannot be opened
            echo 'Error: Unable to open the file.';
            exit;
        }
    } else {
        // Show an error message if the file cannot be opened
        echo 'Error: Unable to open the file.';
        exit;
    }
}

 