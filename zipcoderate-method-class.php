<?php

if ( ! class_exists( 'ZipRate_FlatShipRateZipcode_Method' ) ) {
    class ZipRate_FlatShipRateZipcode_Method extends WC_Shipping_Method {
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id                 = 'zipcoderate'; 
            $this->method_title       = __( 'Shipping Rate by Zipcodes', 'zipcoderate' );  
            $this->method_description = __( 'You can set your shipping rate by uses choose zipcode', 'zipcoderate' ); 



            $this->init();

     

            $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
            $this->title =  $this->settings['title'] ?? __( 'Ship Rate Zipcode', 'zipcoderate' );
            $this->qty_multi =  $this->settings['qty_multi'] ?? '';

            add_action('woocommerce_update_options_shipping_methods', array(&$this, 'process_admin_options'));
        }

        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init() {
            // Load the settings API
            $this->init_form_fields(); 
            $this->init_settings(); 

            // Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        function admin_options() {
            ?>
            <div style="display:flex">
                <h2 style="margin-right:80px"><?php _e('Shipping Rate by Zipcode','woocommerce'); ?></h2>
                <table>
          <tr>
    <th>

<form method="post" action="">
    <input type="hidden" name="export_csv" value="1">
    <button type="submit" name="submitexport" class="button button-primary">Export Zipcodes</button>
</form>

        
    
</th>
            <td>                
            <a href="<?php echo plugin_dir_url(__FILE__) . 'sampleshipratezipcode.csv'; ?>" class="button button-primary" download>Download Sample CSV File</a>
</tr>
        </table>
        </div>
           
            <table class="form-table">

            <?php $this->generate_settings_html(); ?>
            <?php $this->cities_form_fiels(); ?>
            

            </table> 
            
            <?php


        }


        
        function getZipcodes(){
            global $wpdb;
            $table = $wpdb->prefix . "shiprate_zipcodes";
            return $wpdb->get_results("SELECT id, zip_code, rate FROM $table WHERE zip_code != 'Other Zipcode'", OBJECT);
        }

        function getOtherZipcodes(){
            global $wpdb;
            $table = $wpdb->prefix . "shiprate_zipcodes";
            return $wpdb->get_results("SELECT id, zip_code, rate FROM $table WHERE zip_code = 'Other Zipcode'", OBJECT);
        }

        function cities_form_fiels(){
            global $wpdb;

            if(isset( $_POST['zipcodes'] )){
                $this->update_zipcodes();
            }

            $cities = $this->getZipcodes();

            $othercities = $this->getOtherZipcodes();

            ?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="woocommerce_shiprate_zipcodes"><?php _e('Default Ship Rate', 'zipcoderate') ?></label>
                </th>
                <td id="">
                <?php 
                    if(count($othercities)) { 
                        foreach($othercities as $otherciti){
                    ?> 
                    
                        <div class="wcc_fee_row">
                        <input type="text" name="otherzipcode[<?php echo esc_attr($otherciti->id) ?>]" value="<?php echo esc_attr($otherciti->zip_code) ?>" class="input-text regular-input" placeholder="<?php _e('Other Zipcode', 'zipcoderate') ?>" readonly>
                        <span class="wccfee_currency"><?php echo get_woocommerce_currency_symbol() ?></span>
                        <input type="text" name="other_zipcode_fee[<?php echo esc_attr($otherciti->id) ?>]" value="<?php echo esc_attr($otherciti->rate) ?>" class="input-text regular-input wccfee_cities_fee" placeholder="<?php _e('0', 'zipcoderate') ?>">
                        
                        </div>
                        <?php }}else{?>
                        <div class="wcc_fee_row">
                        <input type="text" name="otherzipcode[]" value="Other Zipcode" class="input-text regular-input"readonly >
                        <span class="wccfee_currency"><?php echo get_woocommerce_currency_symbol() ?></span>
                        <input type="text" name="other_zipcode_fee[]" value="" class="input-text regular-input wccfee_cities_fee" placeholder="<?php _e('0', 'zipcoderate') ?>">
                        </div>
                        <?php } ?>
                       
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="woocommerce_shiprate_cities"><?php _e('Your Zipcodes', 'zipcoderate') ?></label>
                </th>
                <td id="wcc_fee_rows">
                <!-- <div class="row" style="display:flex;justify-content: space-around;border:1px solid #000;width:80%">
                        <div class="col-md-4" style="width:40%">City Name</div>
                        <div class="col-md-4" style="width:40%">Ship Rate</div>
                        <div class="col-md-4" style="width:20%">Action</div>
                    </div> -->
                    <?php 
                    if(count($cities)) { 
                        foreach($cities as $citi){
                    ?> 
                    <!-- <div class="row wcc_fee_row" style="display:flex;justify-content: space-around;border:1px solid #000;width:80%">
                        <div class="col-md-4" style="width:40%"> <input type="text" style="" name="zipcodes[<?php echo esc_attr($citi->id) ?>]" value="<?php echo esc_attr($citi->zip_code) ?>" class="input-text regular-input" placeholder="<?php _e('Zip Codes', 'zipcoderate') ?>"></div>
                        <div class="col-md-4" style="width:40%"> <span class="wccfee_currency"><?php echo get_woocommerce_currency_symbol() ?></span>
                        <input type="text" name="zipcodes_fee[<?php echo esc_attr($citi->id) ?>]" value="<?php echo esc_attr($citi->rate) ?>" class="input-text regular-input wccfee_cities_fee" placeholder="<?php _e('0', 'zipcoderate') ?>"></div>
                        <div class="col-md-4" style="width:20%"> <span class="dashicons dashicons-trash wccfee_delcity" data-id="<?php echo esc_attr($citi->id) ?>"></span></div>
                    </div> -->
                        <div class="wcc_fee_row" >
                        <input type="text"  name="zipcodes[<?php echo esc_attr($citi->id) ?>]" value="<?php echo esc_attr($citi->zip_code) ?>" class="input-text regular-input" placeholder="<?php _e('Zip Codes', 'zipcoderate') ?>">
                        <span class="wccfee_currency"><?php echo get_woocommerce_currency_symbol() ?></span>
                        <input type="text" name="zipcodes_fee[<?php echo esc_attr($citi->id) ?>]" value="<?php echo esc_attr($citi->rate) ?>" class="input-text regular-input wccfee_cities_fee" placeholder="<?php _e('0', 'zipcoderate') ?>">
                        <span class="dashicons dashicons-trash wccfee_delcity" data-id="<?php echo esc_attr($citi->id) ?>"></span>
                        </div>
                        
                    <?php }} else { ?>
                        <div class="wcc_fee_row">
                        <input type="text" name="zipcodes[]" value="" class="input-text regular-input" placeholder="<?php _e('Zip Codes', 'zipcoderate') ?>">
                        <span class="wccfee_currency"><?php echo get_woocommerce_currency_symbol() ?></span>
                        <input type="text" name="zipcodes_fee[]" value="" class="input-text regular-input wccfee_cities_fee" placeholder="<?php _e('0', 'zipcoderate') ?>">
                        <span class="dashicons dashicons-trash wccfee_delcity"></span>
                        </div>
                    <?php } ?>
                    
                </td>
            </tr>
                   
            <tr valign="top">
                <th style="padding-top:0"></th>
                <td style="padding-top:0" id="del_citites">
                                            
                    <button class="button-primary wccfee_addcity" type="button"><span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Zipcodes', 'zipcoderate') ?></button>
                </td>
            </tr>

            <tr>
                <th scope="row" class="titledesc">
                    <label for="woocommerce_import_cities"><?php _e('Import Zipcodes', 'zipcoderate') ?></label>
                </th>
                <td>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="import_file" id="ImportFile" accept=".csv">
                        <button class="button-primary" type="submit" name="importsubmit" id="ImportBtn"> Import</button>
                    </form>
                </td>
            <tr>

            <style>
            .wcc_fee_row { display: flex; margin-bottom: 5px; }
            .wccfee_cities_fee { 
                width:80px !important; 
                margin: 0 6px !important; 
                padding-left: 20px !important; 
            }
            .wccfee_addcity .dashicons { margin: 4px 4px 0 0; }
            /*.wccfee_delcity:hover { color: red; }*/
            #wcc_fee_rows { padding-bottom: 5px; }
            .wccfee_delcity {
                margin-top: 4px;
                /*color: #d54e21;*/
                cursor: pointer;
            }
            .wccfee_currency {
                width: 0;
                position: relative;
                left: 14px;
                top: 6px;
            }
            </style>
            <?php
        }

        function update_zipcodes(){
            global $wpdb;

            $othercities = array_map( 'sanitize_text_field',$_POST['otherzipcode']);
            $othercityfees = array_map( 'sanitize_text_field', $_POST['other_zipcode_fee']);
           
            $table  = $wpdb->prefix . "shiprate_zipcodes";
            foreach($othercities as $id1 => $otherciti){

            $othercity = [
                'zip_code' => $otherciti,
                'rate' => $othercityfees[$id1]
            ];
        
            $check1 = $wpdb->get_results("SELECT id FROM $table where id = '$id1' ORDER BY id ASC", OBJECT);

            if($check1)
            $result1 = $wpdb->update($table, $othercity, ['id' => $id1]);
            else
            $result1 = $wpdb->insert($table, $othercity);
        }

            $cities =  array_map( 'sanitize_text_field', $_POST['zipcodes']);
            $fees   = array_map( 'sanitize_text_field', $_POST['zipcodes_fee'] );

            foreach($cities as $id => $citi){
                
                $city = [
                    'zip_code' => $citi,
                    'rate' => $fees[$id]
                ];
                $check = $wpdb->get_results("SELECT id FROM $table where id = '$id' ORDER BY id ASC", OBJECT);

                if($check)
                $result = $wpdb->update($table, $city, ['id' => $id]);
                else
                $result = $wpdb->insert($table, $city);
            }


            if(isset($_POST['delcity'])){
                $delcity = array_map( 'sanitize_text_field', $_POST['delcity']);
                foreach($delcity as $del){
                    $wpdb->delete( $table, ['id' => $del] );
                }
            }


        }

        /**
         * Define settings field for this shipping
         * @return void 
         */
        function init_form_fields() { 

            $this->form_fields = array(

                'enabled' => array(
                    'title' => __( 'Enable', 'zipcoderate' ),
                    'type' => 'checkbox',
                    'description' => __( 'Enable this Shipping Rates.', 'zipcoderate' ),
                    'default' => 'yes'
                ),

                'title' => array(
                    'title' => __( 'Shipping Title', 'zipcoderate' ),
                    'type' => 'text',
                    'description' => __( 'Shipping Title to be display on checkout page.', 'zipcoderate' ),
                    'default' => __( 'Custom Rate', 'zipcoderate' )
                ),
                'qty_multi' => array(
                    'title' => __( 'Multiply Qty Active', 'zipcoderate' ),
                    'type' => 'checkbox',
                    'description' => __( 'Price will be multiply to each product quantity.', 'zipcoderate' )
                ),

               


            );

        }

        /**
         * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        public function calculate_shipping( $package = array() ) {
            
            $weight = 0;
            $cost = 0;
            $address = $package["destination"]; // country, state, postcode, city, address, address_1, address_2

            

            $cost = $this->getZipcodeFee($address['postcode']);

            $othercost = $this->getOtherZipcodeFee($address['postcode']);
            
            // print_r($cost);
            // die;
            
            // print_r($cost);
            // die;
            
            if(isset($cost['rate'])){
                if($this->qty_multi == 'yes'){
                    $qty = WC()->cart->get_cart_contents_count();
                    $cost['rate'] = $cost['rate'] * $qty;
                    $label2 = " (Qty Multiply) ";
                }else{
                     $label2 = "";
                }
                
                
                $rate = array(
                    'id' => $this->id,
                    'label' => $this->title."</br>". $label2,
                    'cost' => $cost,
                   
                );
                $this->add_rate( $rate );
                
            }
            if($address['postcode'] == 'Other Zipcode'){

            if(isset($othercost['rate'])){
                if($this->qty_multi == 'yes'){
                    $qty = WC()->cart->get_cart_contents_count();
                    $othercost['rate'] = $othercost['rate'] * $qty;
                    $label2 = " (Qty Multiply) ";
                }else{
                     $label2 = "";
                }
                
                
                $rate = array(
                    'id' => $this->id,
                    'label' => $this->title."</br>". $label2,
                    'cost' => $othercost,
                   
                );
                $this->add_rate( $rate );
                
            }
        }
            
        }

        public function getZipcodeFee($city_name){
            global $wpdb;
            $table = $wpdb->prefix . "shiprate_zipcodes";
            return $wpdb->get_row("SELECT rate FROM $table where zip_code = '$city_name'", ARRAY_A);
        }

        public function getOtherZipcodeFee($city_name){
            global $wpdb;
            $table = $wpdb->prefix . "shiprate_zipcodes";
            return $wpdb->get_row("SELECT rate FROM $table where zip_code = 'Other Zipcode'", ARRAY_A);
        }
    }
}
