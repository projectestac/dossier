<?php
/*
Plugin Name: WordPress phpinfo()
Plugin URI: http://whoischris/wordpress-phpinfo.zip
Description:  This simple plugin adds an option to an adminstrator's Tools menu which displays standard phpinfo() feedback details to the user.
Author: Chris Flannagan
Version: 15
Author URI: http://whoischris.com/
*/


/**
 * WordPress phpinfo() core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://whoischris/wordpress-phpinfo.zip
 *
 * @package 	WordPress phpinfo()
 * @copyright	Copyright (c) 2016, Chris Flannagan
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		WordPress phpinfo() 1.0
 *
 *
 */




/**
 * Creates the Class for WordPress phpinfo() 
 *
 * @author     Chris Flannagan <chris@champoosa.com>
 * @version    Release: @14.11@
 * @see        wp_enqueue_scripts()
 * @since      Class available since Release 14.11
 */
class thissimyurl_WPPHPInfo {
	
	 /**
	  * Standard Constructor
	  *
      * @access public
      * @static
	  * @uses http://codex.wordpress.org/Function_Reference/add_action
      * @since Method available since Release 14.11
	  */
    public function __construct() {
 
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
     	add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }
 

	/**
	  * admin_enqueue_scripts 
	  *
      * @access public
      * @static
	  * @uses http://codex.wordpress.org/Function_Reference/wp_enqueue_style
      * @since Method available since Release 14.12
      *
	  */
	function admin_enqueue_scripts() {
		
		if ( isset( $_GET['page'] ) ) {
			
			if ( 'thisismyurl_wpphpinfo' != $_GET['page'] )
		        return;

			wp_register_style( 'thisismyurl-wpphpinfo', plugin_dir_url( __FILE__ ) . 'css/thisismyurl-admin.css', false, '14.12' );
		    wp_enqueue_style( 'thisismyurl-wpphpinfo' );
			wp_enqueue_script( 'flannyjs', plugin_dir_url( __FILE__ ) . 'js/phpinfo-js.js', array( 'jquery' ) );

		}

	}

	/**
	  * admin_menu 
	  *
      * @access public
      * @static
	  * @uses http://codex.wordpress.org/Function_Reference/add_options_page
      * @since Method available since Release 14.12
      *
	  */
	function admin_menu() {
		//XTEC ************ AFEGIT - Made plugin only visible for superadmins.
		//2015.04.09 @vsaavedr
		if( is_xtec_super_admin() ) {
		//************ FI
		add_options_page( __( 'WordPress phpinfo()', 'thisismyurl_wpphpinfo' ), __( 'WordPress phpinfo()', 'thisismyurl_wpphpinfo' ), 'manage_options', 'thisismyurl_wpphpinfo', array( $this, 'thisismyurl_wpphpinfo_page' ) );
		//XTEC ************ AFEGIT - Made plugin only visible for superadmins.
		}
		//************ FI
	}
	

	function thisismyurl_wpphpinfo_page() {

		if( isset( $_REQUEST['sendtoemail'] ) ) {
			$to = $_REQUEST['sendtoemail'];
			$subject = 'WordPress User Submitted PHP Info()';
			$body = $this->phpinfo_output_noecho();
			$headers = array('Content-Type: text/html; charset=UTF-8');

			wp_mail( $to, $subject, $body, $headers );

			echo '<div id="emailsent"><h2>Email SENT! Be sure to tell receiver they may need to check their junk/spam folders for this information.</h2></div>';
		}

		?>

		<div id="bgpopup"></div>
		<div id="emailme">
			<form action="" method="post">
				<table>
					<tr>
						<td>Send to: </td>
						<td><input type="text" name="sendtoemail" /></td>
						<td><input type="submit" value="Send it!" /></td>
					</tr>
				</table>
			</form>
			<button id="closeemail" style="font-size:20px;font-weight:bold;background:#e968ff;color:#FFF;">
				Close This Window
			</button>
		</div>
		<div class="wrap">
			<div class="thisismyurl-icon32"><br /></div>
			<h2><?php _e( 'WordPress phpinfo()', 'thisismyurl_wpphpinfo' ); ?></h2>
			<p><?php _e( 'It is important for a non technical administrator to be able to diagnose server related problems in WordPress.', 'thisismyurl_wpphpinfo' ); ?></p>

			<h3><?php _e( 'General Settings', 'thisismyurl_wpphpinfo' ); ?></h3>
			<p><?php printf( __( 'The plugin has no settings, once activated it will work automattically. For further details, please view the <a href="%sreadme.txt">readme.txt</a> file included with this release.', 'thisismyurl_wpphpinfo' ), plugin_dir_url( __FILE__ ) ); ?></p>
			<p style="width: 100%;"><button id="email_phpinfo">Email This Information</button></p>
			<?php $this->phpinfo_output(); ?>
			<br style='clear:both;' />
			Plugin adopted by <a href='http://whoischris.com'>Chris Flannagan</a>.
		</div>
		<?php
	}

	function phpinfo_output() {

		ob_start();
		phpinfo(-1);
		$phpinfo_content = ob_get_contents();
		ob_end_clean();

		if ( ! empty( $phpinfo_content ) )
			$phpinfo_array = explode( '<table', $phpinfo_content );

		if ( ! empty( $phpinfo_array ) ) {
			unset( $phpinfo_array[0] );
			foreach ( $phpinfo_array as $phpinfo_element ) {

				$phpinfo_element = str_replace( '<tr', '<tr valign="top"', $phpinfo_element );

				echo '<table class="phpinfo" ' . $phpinfo_element;
				echo '<div style="clear:both"></div>';
			}

		}
	}

	function phpinfo_output_noecho() {
		$myreturn = '';
		ob_start();
		phpinfo(-1);
		$phpinfo_content = ob_get_contents();
		ob_end_clean();

		if ( ! empty( $phpinfo_content ) )
			$phpinfo_array = explode( '<table', $phpinfo_content );

		if ( ! empty( $phpinfo_array ) ) {
			unset( $phpinfo_array[0] );
			foreach ( $phpinfo_array as $phpinfo_element ) {

				$phpinfo_element = str_replace( '<tr', '<tr valign="top"', $phpinfo_element );

				$myreturn .= '<table class="phpinfo" ' . $phpinfo_element;
				$myreturn .= '<div style="clear:both"></div>';
			}

		}
		return $myreturn;
	}



}

$thissimyurl_WPPHPInfo = new thissimyurl_WPPHPInfo;










/**
  * plugin_action_links 
  *
  * @access public
  * @static
  * @since Method available since Release 14.12
  * @todo why can't this be called within the class?
  *
  */
function thisismyurl_wpphpinfo_plugin_action_links( $links, $file ) {

	static $this_plugin;

	if( ! $this_plugin )
		$this_plugin = plugin_basename( __FILE__ );

	if( $file == $this_plugin ){
		$links[] = '<a href="options-general.php?page=thisismyurl_wpphpinfo">' . __( 'phpinfo()', 'thisismyurl_wpphpinfo' ) . '</a>';
		$links[] = '<a href="http://whoischris/wordpress-phpinfo.zip">' . __( 'Author', 'thisismyurl_wpphpinfo' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'thisismyurl_wpphpinfo_plugin_action_links', 10, 2 );



function wordpressphpinfo(){
	return $thissimyurl_WPPHPInfo->phpinfo_output();
}