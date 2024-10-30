<?php
/*
Plugin Name: LSD Lazy load images
Plugin URI: http://www.bas-matthee.nl
Description: Delay loading of images until they are in the visible area of a page to save bandwith/gain speed.
Author: Bas Matthee
Version: 1.0
Author URI: https://www.twitter.com/BasMatthee
*/

if (!is_admin()) {
    
    define('LSD_LL_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
    
    add_action('init', 'lsd_ll_buffer_start');
    add_action('wp_enqueue_scripts', 'init_lazyload');
    add_action('wp_footer', 'lsd_ll_buffer_end');
    
    function init_lazyload() {
        
        wp_register_script('lsd-lazyload', LSD_LL_URLPATH.'jquery.lazyload.js',array('jquery'));
        wp_enqueue_script('lsd-lazyload');
        
    }
    
    function filter_images( $content ) {
		
		$matches = array();
		preg_match_all( '/<img\s+.*?>/', $content, $matches );
		
		$search = array();
		$replace = array();
        
        foreach ( $matches[0] as $lsd_image ) {
            
    		$lsd_replace = preg_replace( '/<img(.*?)src=/i', '<img$1src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-original=', $lsd_image );
    		
    		if ( preg_match( '/class=["\']/i', $lsd_replace ) ) {
    			$lsd_replace = preg_replace( '/class=(["\'])(.*?)["\']/i', 'class=$1lazy $2$1', $lsd_replace );
    		} else {
    			$lsd_replace = preg_replace( '/<img/i', '<img class="lazy"', $lsd_replace );
    		}
    		
    		$lsd_replace .= '<noscript>' . $lsd_image . '</noscript>';
    		
    		array_push( $search, $lsd_image );
    		array_push( $replace, $lsd_replace );
    		
        }
        
		$content = str_replace( $search, $replace, $content );
	    
		return $content;
        
   }
   
   function lsd_ll_callback($buffer) {
      
      return $buffer;
      
   }
   
   function lsd_ll_buffer_start() { ob_start("lsd_ll_callback"); }
   
   function lsd_ll_buffer_end() { $content = ob_get_clean(); echo filter_images($content); }
   
}