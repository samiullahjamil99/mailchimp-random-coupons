<?php
function mrc_settings_init() {
    // Register a new setting for "wporg" page.
    register_setting( 'mrc', 'mrc_options' );

    // Register a new section in the "wporg" page.
    add_settings_section(
        'mrc_section_mailchimp',
        __( 'Mailchimp Settings.', 'mrc' ), 'mrc_section_mailchimp_callback',
        'mrc'
    );

    // Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
    add_settings_field(
        'mrc_field_apikey', // As of WP 4.6 this value is used only internally.
                                // Use $args' label_for to populate the id inside the callback.
            __( 'API Key', 'mrc' ),
        'mrc_field_apikey_cb',
        'mrc',
        'mrc_section_mailchimp',
        array(
            'label_for'         => 'mrc_field_apikey',
        )
    );

    $lists = MRC_API()->get_lists();
    foreach($lists as $list):
    add_settings_field(
      'mrc_field_discount_amounts_'.$list->id,
      __('Discount Percentage for List: <br><u>'.$list->name.'</u>','mrc'),
      'mrc_fields_discount_amounts_cb',
      'mrc',
      'mrc_section_mailchimp',
      array(
        'label_for' => 'list_'.$list->id,
      ),
    );
    endforeach;
}

add_action( 'admin_init', 'mrc_settings_init' );

function mrc_section_mailchimp_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter your mailchimp API key.', 'mrc' ); ?></p>
    <?php
}

function mrc_field_apikey_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'mrc_options' );
    ?>
    <input type="password" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="mrc_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $options[$args['label_for']]; ?>">
    <?php
}

function mrc_fields_discount_amounts_cb($args) {
  $options = get_option( 'mrc_options' );
  ?>
  <input type="number" min="0" max="100" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="mrc_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $options[$args['label_for']]; ?>">
  <?php
}

function mailchimp_coupons_settings_page() {
    add_menu_page(
        'Mailchimp Coupons',
        'Mailchimp Coupons Settings',
        'manage_options',
        'mailchimp-coupons',
        'mailchimp_coupons_settings_page_html'
    );
}

add_action( 'admin_menu', 'mailchimp_coupons_settings_page' );

function mailchimp_coupons_settings_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'mrc_messages', 'mrc_message', __( 'Settings Saved', 'mrc' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'mrc_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "wporg"
            settings_fields( 'mrc' );
            // output setting sections and their fields
            // (sections are registered for "wporg", each field is registered to a specific section)
            do_settings_sections( 'mrc' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}
