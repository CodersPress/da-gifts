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

require ( dirname( __FILE__ ) . '/da-gifts-classes.php' );

function da_gifts_admin($message = '', $type = 'error') {
if ($_REQUEST['settings-updated']=='true') {
echo '<div id="message" class="updated fade"><p><strong>Option setting saved.</strong></p></div>';
}
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
<form method="post" action="options.php">
    <?php settings_fields("dag-settings-group");?>
    <?php do_settings_sections("dag-settings-group");?>
    <table class="widefat" style="width:80%;">
 <thead style="background:#2EA2CC;color:#fff;">
            <tr>
                <th style="color:#fff;">Members Sending Gifts Option</th>
                <th style="color:#fff;">Option</th>
                 <th style="color:#fff;">Save</th>
            </tr>
        </thead>
<tr>
<td>By default any registered members can send gifts. <br />This setting will <b>Restrict Sending Gifts</b> by allowing only those with a Membership package.</td>
<td>
            <select name="dag_memberShipOnly" />
            <option value="yes" <?php if ( get_option( 'dag_memberShipOnly')=='yes' ) echo 'selected="selected" '; ?>>Yes</option>
            <option value="no" <?php if ( get_option( 'dag_memberShipOnly')=='no' ) echo 'selected="selected" '; ?>>No</option>
            </select>
</td>
<td><input type="submit" class="button button-primary" name="submit" value="Save Setting"/></td>
 </tr>
</form>
</table>
<br /><br />
<table class="widefat" style="width:80%;">
 <thead style="background:#2EA2CC;color:#fff;">
            <tr>
                <th style="color:#fff;">Gifts Display Box Height</th>
                <th style="color:#fff;">Height in Pixels</th>
                 <th style="color:#fff;">Save</th>
            </tr>
        </thead>
<tr>
<td>Set the inner height of the display box(popup) that holds your gift images.</td>
<td>
<input type="text" size="2" name="dag_displayBox_Height" value="<?php echo get_option("dag_displayBox_Height");?>"/>px
</td>
<td><input type="submit" class="button button-primary" name="submit" value="Save Setting"/></td>
 </tr>
</form>
</table>
<br /><br />
<?php if ( isset($updated) ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Description Updated!' ) . "</p></div>" ?><?php endif; ?>
<?php if ( isset($uploaded) ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Gift Uploaded Successfully!' ) . "</p></div>" ?><?php endif; ?>
<table class="widefat" style="width:80%;">
<form action="<?php echo site_url() . '/wp-admin/admin.php?page=da-gifts-settings' ?>" method="post" enctype="multipart/form-data" name="gift-upload-form" id="gift-upload-form" >			
 <thead style="background:#2EA2CC;color:#fff;">
            <tr>
                <th style="color:#fff;">Add a new Gift Image</th>
                <th style="color:#fff;">Select a Gift</th>
                <th style="color:#fff;">Save</th>
            </tr>
        </thead>
<tr>
<td>As an Administrator you are free to upload all types of images and sizes.<br />I recommend keeping image sizes and quality to a minimum.<br> This will improve the member experience and help save bandwidth.</td>
<td><input type="file" name="file" id="file"/></td>
<td><input type="submit" class="button button-primary" name="submit" value="Upload"/>
<input type="hidden" name="action" value="gifts_upload" />
<?php wp_nonce_field( 'gifts-settings' );?>
</td>
 </tr></form>
</table>

<br /><br />

           <table class="widefat" style="max-width: 80%;">     
 <thead style="background:#2EA2CC;color:#fff;">
             <tr>
                <th style="color:#fff;">Gift Item Editor :</th>
            </tr>
        </thead>
<tr><td>
<?php 
			$allgift = da_gifts_allgift();
			foreach ($allgift as $giftitem) {
			echo '<div class="giftImages" style="max-width:280px;min-height:380px;">';
			echo '<img src="'. site_url() .'/wp-content/plugins/da-gifts/includes/images/'. $giftitem->gift_image .'" class="gift-img"/><br><br>';
			echo '<div style="text-align:left;"><b>Description:</b> '.$giftitem->gift_name.'</div><br>';
            echo '<div><b>Gift Sent:</b> '.$giftitem->count.', times.</div><br>';
			echo '<div>';
			echo '<a href="'. site_url() . '/wp-admin/admin.php?page=da-gifts-settings&gift_id='.$giftitem->id.'&mode=edit" /><img src="'. site_url() .'/wp-content/plugins/da-gifts/includes/images/admin/edit.png" alt="Edit Image Name" title="Edit Image Name"/></a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<a href="'. site_url() . '/wp-admin/admin.php?page=da-gifts-settings&gift_id='.$giftitem->id.'&mode=delete" /><img src="'. site_url() .'/wp-content/plugins/da-gifts/includes/images/admin/delete.png" alt="Permanently Delete" title="Permanently Delete"/></a>';
			echo '</div>';
			echo '</div>';
			} ?></td></tr>
	</table>
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
echo '<div id="giftsBox" style="height:'.get_option("dag_displayBox_Height"). 'px;overflow:auto;"><ul class="giftideas clearfix">';
foreach ($allgift as $giftitem) {
echo '<li class="gift'.$giftitem->id.'">';
echo '<a href=javascript:void(0); onclick=jQuery("#daGift").val("'.$giftitem->gift_image.'");jQuery(".giftideas,li").removeClass("selected");jQuery(".gift'.$giftitem->id.'").addClass("selected");><img src="'.site_url().'/wp-content/plugins/da-gifts/includes/images/'.$giftitem->gift_image.'" alt="'.$giftitem->gift_name.'" title="'.$giftitem->gift_name.'" class="img-responsive"/></a>';
echo '</li>';
 } 
echo '</ul></div>';
}
add_shortcode('GIFTSEXTENDED', 'gifts_extended');

function append_gifts(){global $CORE, $userdata;
?>
<script>
jQuery(".giftideas").remove();
jQuery('#giftmodal > div:nth-child(1) > div:nth-child(1) > div:nth-child(3) > form:nth-child(1) > input:nth-child(1)').val('sendThisgift');
jQuery('<?php do_shortcode('[GIFTSEXTENDED]');?>').insertAfter("#giftmodal > div:nth-child(1) > div:nth-child(1) > div:nth-child(2) > p:nth-child(1)");
</script>
<?php 
$dag_memberShipOnly = get_option("dag_memberShipOnly");
if ( !$userdata->wlt_membership && $dag_memberShipOnly == "yes"){ ?>
<script>
var NoMemberShip = "<?php echo strip_tags($CORE->_e(array('validate','25')));?>";
jQuery('#giftmodal > div:nth-child(1) > div:nth-child(1) > div:nth-child(3) > form:nth-child(1)').attr('onsubmit', 'alert(NoMemberShip); return false;');
</script>
<?php }
 } 
add_action('wp_footer', 'append_gifts', 100);
?>