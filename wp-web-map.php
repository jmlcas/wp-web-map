<?php
/*
 * Plugin Name:       WP Web Map
 * Description:       This plugin creates a submenu in Settings. Click and activate the sections you want to see on the "Sitemap" page that the plugin has already created. Add this page to the navigation menu of your choice.
 * Version:           1.0
 * Author:            labarta
 * Author URI:        https://labarta.es
 * License:           GPL-2.0+
 * Text Domain:       wp-web-map
 *  Domain Path:      /languages
*/


defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

/* Añadiendo estilos CSS */

function wpwmap_agregar_estilos () {
    wp_enqueue_style( 'wp-web-map', plugins_url('css/wp-web-map.css', __FILE__) );
}

add_action( 'wp_enqueue_scripts', 'wpwmap_agregar_estilos' );

/* Para cambiar idioma */

add_action( 'plugins_loaded', 'wpwmap_plugin_load_textdomain' );

function wpwmap_plugin_load_textdomain() {
  load_plugin_textdomain( 'wp-web-map', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

/* Creando página "Sitemap" */

if ( ! function_exists( 'wpwmap_crear_pagina' )) {
  
	if( null == get_page_by_title ( 'Sitemap' ) ) {
	  
function wpwmap_crear_pagina() {
            $post_data = array(
                'post_title'    => 'Sitemap',
                'post_content'  => '[pages][posts]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
            );
            wp_insert_post( $post_data, $error_obj );
        }

            add_action( 'admin_init', 'wpwmap_crear_pagina' );
         }
}

/* Modificando menú de administración */

add_action( 'admin_menu', 'wpwmap_chec_add_admin_menu' );
add_action( 'admin_init', 'wpwmap_chec_settings_init' );

function wpwmap_chec_add_admin_menu(  ) { 

add_options_page( 'wp web map', 'WP Web Map', 'manage_options', 'checking', 'wpwmap_checking_options_page' );

}


/* Creando el listado de páginas */

function wpwmap_custom_shortcode_0() {
?>
    <div class="wpwmap">
	
	<h2><?php _e('Pages:','wp-web-map');?></h2>
	<ul>
	    <?php
	    wp_list_pages( array(
	        'title_li'    => '',
	    ) );
	    ?>
	</ul>
    </div>
<?php
}

add_shortcode( 'pages', 'wpwmap_custom_shortcode_0' );

/* Creando el listado de entradas */

function wpwmap_custom_shortcode_1() {
?>

    <div class="wpwmap">
		
	<h2><?php _e('Published Posts:','wp-web-map');?></h2>
	<?php
	
	$args = array (
	   'orderby' =>'title',
	   'order' =>'asc',
	   'posts_per_page' => '-1',
	   'post_type' =>'post',
	   'post_status' => 'publish',
	    
	);
	
	$the_query = new WP_Query ($args);
	
	if ($the_query-> have_posts()) {
	
		echo '<ul>';
	
		while ( $the_query->have_posts()) {
	
			$the_query->the_post();
	
			echo '<li><a href="' . get_the_permalink(). '">' .get_the_title(). '</a></li>';
		}
		echo '</ul>';
	
	 }

	 if( is_page('Sitemap') ) { ?>
     <style>
	 div.comments-area {
     display: none;
		 }
		  else { 
		 }
     </style>
     <?php } 		
		?>	
			</div>

<?php
}

add_shortcode( 'posts', 'wpwmap_custom_shortcode_1' );

function wpwmap_chec_settings_init(  ) { 

register_setting( 'my_option', 'chec_settings' );

add_settings_section(
    'chec_checking_section', 
    __( 'Settings', 'wp-web-map' ), 
    'wpwmap_chec_settings_section_callback', 
    'checking'
);

add_settings_field( 
    'chec_checkbox_field_0', 
    __( 'Include the pages list', 'wp-web-map' ), 
    'wpwmap_chec_checkbox_field_0_render', 
    'checking', 
    'chec_checking_section' 
);

add_settings_field( 
    'chec_checkbox_field_1', 
    __( 'Include the posts list', 'wp-web-map' ), 
    'wpwmap_chec_checkbox_field_1_render', 
    'checking', 
    'chec_checking_section' 
);  

}


function wpwmap_chec_checkbox_field_0_render(  ) { 

$options = get_option( 'chec_settings' );
?>
<input type='checkbox' name='chec_settings[chec_checkbox_field_0]' <?php checked( $options['chec_checkbox_field_0'], true ); ?> value='1'>
<?php

}

function wpwmap_chec_checkbox_field_1_render(  ) { 

$options = get_option( 'chec_settings' );
?>
<input type='checkbox' name='chec_settings[chec_checkbox_field_1]' <?php checked( $options['chec_checkbox_field_1'], true ); ?> value='1'>
<?php

}

function wpwmap_chec_settings_section_callback(  ) { 

echo __( '<h4> This plugin creates a page called <span style="color:#E67E22">"Sitemap" </span> add this page to the navigation menu of your choice. Check the option of the list you want to show: </h4>', 'wp-web-map');

}

/* Eliminando el shortcode [pages] */

$options = get_option( 'chec_settings' );
if (! $options['chec_checkbox_field_0'] == '1' ) {
function wpwmap_remove_shortcode_0( $content ) {
  if ( is_page('sitemap') ) {
    remove_shortcode( 'pages');
	add_shortcode( 'pages', '__return_false' );
  }
  return $content;
}
add_filter( 'the_content', 'wpwmap_remove_shortcode_0' );
}


/* Eliminando el shortcode [posts] */

$options = get_option( 'chec_settings' );
if (! $options['chec_checkbox_field_1'] == '1' ) {
function wpwmap_remove_shortcode_1( $content ) {
  if ( is_page('sitemap') ) {
    remove_shortcode( 'posts');
	add_shortcode( 'posts', '__return_false' );
  }
  return $content;
}
add_filter( 'the_content', 'wpwmap_remove_shortcode_1' );
}


function wpwmap_checking_options_page(  ) { 

?>
<form action='options.php' method='post'>

    <h1>WP Web Map</h1>

    <?php
    settings_fields( 'my_option' );
    do_settings_sections( 'checking' );
    submit_button();
    ?>

</form>
<?php

}

