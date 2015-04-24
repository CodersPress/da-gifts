<?php
/**
 *
 * GNU General Public License, Free Software Foundation
 * <http://creativecommons.org/licenses/GPL/2.0/>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

add_action( 'init', 'DAG_plugin_updater_init' );
function DAG_plugin_updater_init() {
	if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

	require ( dirname( __FILE__ ) . '/da-gifts-updater.php' );

	define( 'WP_DAG_FORCE_UPDATE', true );

		if ( is_admin() ) { 
			$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'da-gifts',
			'api_url' => 'https://api.github.com/repos/CodersPress/da-gifts',
			'raw_url' => 'https://raw.github.com/CodersPress/da-gifts/master',
			'github_url' => 'https://github.com/CodersPress/da-gifts',
			'zip_url' => 'https://github.com/CodersPress/da-gifts/archive/zipball/master',
			'sslverify' => true,
			'requires' => '3.8',
			'tested' => '4.2',
			'readme' => 'README.md',
			'access_token' => '',
		);
		new WP_DAG_Updater( $config );
	    }
    }
}

require ( dirname( __FILE__ ) . '/da-gifts-classes.php' );
function da_gifts_add_admin_menu() {
		add_menu_page( 'Dating Gifts', 'DA Gifts Extented', 'manage_options', 'da-gifts-settings', 'da_gifts_admin' );
		}

add_action( 'admin_menu', 'da_gifts_add_admin_menu' );

function da_gifts_admin($message = '', $type = 'error') {
	if ( $message != '' ) {
	echo wp_specialchars( attribute_escape( $message ) );
	}
	/* check submit from edit gift data */
	if ( isset( $_POST['submit'] ) && check_admin_referer('gifts-settings') ) {
		if (isset( $_POST['gift_id'])) {
		$gift = new DA_Gifts($_POST['gift_id']);
		$gift->gift_name = $_POST['gift_name'];
		$gift->save();
		$updated = true;
		} elseif (isset( $_FILES['file']) && check_admin_referer('gifts-settings')) { /* check submit from gift upload */ 
		$message = sprintf( 'Gift item was upload successfully! <br/>', $type);
		$dir = WP_PLUGIN_DIR.'/da-gifts/includes/images';
		if (file_exists($dir.'/'.$_FILES["file"]["name"])){
			echo "<div id='message' class='updated fade'><p>" . 'Gifts Image already exist!! - Try renaming the image file.' . "</p></div>";
		} else {
		move_uploaded_file($_FILES["file"]["tmp_name"], $dir.'/'.$_FILES["file"]["name"]);
		$giftname = explode(".", $_FILES["file"]["name"]);
		da_gifts_newgift($giftname[0], $_FILES["file"]["name"]);
		$uploaded = true;
		}
		}
	}

	if ( isset($_GET['mode']) && isset($_GET['gift_id'])) {
		$gift = new DA_Gifts($_GET['gift_id']);
		if ($_GET['mode'] == 'delete') {
			if ($gift->delete()){
            unlink(dirname( __FILE__ ) . "/images/$gift->gift_image");
            echo "<div id='message' class='updated fade'><p>" . __( 'Gift item was deleted successfully!' ) . "</p></div>";
			} else { echo "<div id='message' class='updated fade'><p>" . __( 'Error! Can not delete gift item, item not Found?' ) . "</p></div>";}
		unset($_GET['mode']);
		da_gifts_admin($message);
		} elseif ($_GET['mode'] == 'edit'){
        require ( dirname( __FILE__ ) . '/css/style.php' );
		echo '<h1>Gifts Item Admin</h1><br/>';
		echo '<div class="giftImages"><img src="' . site_url() . '/wp-content/plugins/da-gifts/includes/images/'. $gift->gift_image .'" /></div>';
		?>
		<form action="<?php echo site_url() . '/wp-admin/admin.php?page=da-gifts-settings' ?>" name="gifts-settings-form" id="gifts-settings-form" method="post">
			<table class="form-table">
				<tr valign="top">
					<td>
						<b><?php _e( 'Short Gift Description' ) ?></b>: <br><textarea name="gift_name" id="gift_name" value="" /><?php esc_attr_e( $gift->gift_name ); ?></textarea>
					</td>
				</tr>
                <tr>
					<td>
						<?php _e( 'Descriptions give your members a better idea of the gift Item. <br>For example <em><b>mouseover</b></em> a gift on the send block.' ) ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="gift_id" value="<?php echo $gift->id; ?>" />
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Save Settings' ) ?>"/>
			</p>

			<?php
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'gifts-settings' );
			?>
		</form>
<?php
		}
	} else {
 require ( dirname( __FILE__ ) . '/css/style.php' );
?>

<!--------------- start main config admin panel -------------->

	<div class="wrap">
		<h1><?php _e( 'Gifts Admin' ) ?></h1>
		<br />
		<?php if ( isset($updated) ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Description Updated!' ) . "</p></div>" ?><?php endif; ?>
		<?php if ( isset($uploaded) ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Gift Uploaded Successfully!' ) . "</p></div>" ?><?php endif; ?>
<form action="<?php echo site_url() . '/wp-admin/admin.php?page=da-gifts-settings' ?>" method="post" enctype="multipart/form-data" name="gift-upload-form" id="gift-upload-form" >			
<br/>
<label><?php _e('Select Gift Image to Upload *' ) ?><br />
<input type="file" name="file" id="file"/></label>
<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Upload' ) ?>"/>
			</p>
<input type="hidden" name="action" value="gifts_upload" />
<?php
/* This is very important, don't leave it out. */
wp_nonce_field( 'gifts-settings' );
?>
</form>
<br/>
			<?php 
			echo '<h3>Gift Item Editor :</h3>';
            echo '<div style="width:80%">';
			$allgift = da_gifts_allgift();
			foreach ($allgift as $giftitem) {
			echo '<div class="giftImages">';
			echo '<img src="'. site_url() .'/wp-content/plugins/da-gifts/includes/images/'. $giftitem->gift_image .'" /><br>';
			echo '<div style="text-align:left;"><b>Description:</b> '.$giftitem->gift_name.'</div><br>';
            echo '<div><b>Gift Sent:</b> '.$giftitem->count.', times.</div><br>';
			echo '<div>';
			echo '<a href="'. site_url() . '/wp-admin/admin.php?page=da-gifts-settings&gift_id='.$giftitem->id.'&mode=edit" /><img src="'. site_url() .'/wp-content/plugins/da-gifts/includes/images/admin/edit.png" alt="Edit Image Name" title="Edit Image Name"/></a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<a href="'. site_url() . '/wp-admin/admin.php?page=da-gifts-settings&gift_id='.$giftitem->id.'&mode=delete" /><img src="'. site_url() .'/wp-content/plugins/da-gifts/includes/images/admin/delete.png" alt="Permanently Delete" title="Permanently Delete"/></a>';
			echo '</div>';
			echo '</div>';
			}
			?></div>
	</div>
	<?php
	}
}

function da_gifts_upload_dir() {
	$dir = WP_PLUGIN_DIR.'/da-gifts/includes/images';
	$siteurl = trailingslashit( get_blog_option( 1, 'siteurl' ) );
	$url = str_replace(ABSPATH,$siteurl,$dir);
	$bdir = $dir;
	$burl = $url;
	$subdir = '/' . $user_id;
	$dir .= $subdir;
	$url .= $subdir;

	if ( !file_exists( $dir ) )
		@wp_mkdir_p( $dir );
	return apply_filters( 'da_gifts_upload_dir', array( 'path' => $dir, 'url' => $url, 'basedir' => $bdir, 'baseurl' => $burl, 'error' => false ) );
}

function gifts_extended(){
$allgift = da_gifts_allgift();
echo '<ul class="giftideas clearfix">';
foreach ($allgift as $giftitem) {
echo '<li class="gift'.$giftitem->id.'">';
echo '<a href=javascript:void(0); onclick=jQuery("#daGift").val("'.$giftitem->gift_image.'");jQuery(".giftideas,li").removeClass("selected");jQuery(".gift'.$giftitem->id.'").addClass("selected");><img src="'.site_url().'/wp-content/plugins/da-gifts/includes/images/'.$giftitem->gift_image.'" alt="'.$giftitem->gift_name.'" title="'.$giftitem->gift_name.'" class="img-responsive"/></a>';
echo '</li>';
 } 
echo '</ul>';
}
add_shortcode('GIFTSEXTENDED', 'gifts_extended');

function append_gifts(){
?>
<script>
jQuery(".giftideas").remove();
jQuery('#giftmodal > div:nth-child(1) > div:nth-child(1) > div:nth-child(3) > form:nth-child(1) > input:nth-child(1)').val('sendThisgift');
jQuery('<?php do_shortcode('[GIFTSEXTENDED]');?>').insertAfter("#giftmodal > div:nth-child(1) > div:nth-child(1) > div:nth-child(2) > p:nth-child(1)");
</script>
<?php } 
add_action('wp_footer', 'append_gifts', 100);
?>