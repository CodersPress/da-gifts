<?php

class DA_Gifts {
	var $id;
	var $gift_name;
	var $gift_image;

	function da_gifts( $id = null ) {
		global $wpdb;
		if ( $id ) {
			$this->id = $id;
			$this->populate( $this->id );
		}
	}

function populate() {
		global $wpdb;
		if ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}da_gifts WHERE id = %d", $this->id ) ) ) {
			$this->gift_name = $row->gift_name;
			$this->gift_image = $row->gift_image;
		}
	}


	function save() {
		global $wpdb;
		/* Call a before save action here */
		do_action( 'da_gifts_data_before_save', $this );

		if ( $this->id ) {
			// Update
			echo $this->gift_name;
			echo $this->gift_id;
			$result = $wpdb->query( $wpdb->prepare( 
					"UPDATE {$wpdb->base_prefix}da_gifts SET 
						gift_name = %s,
						gift_image = %s
					WHERE id = %d",
						$this->gift_name,
						$this->gift_image,
						$this->id 
					) );
		} else {
			// Save
			$result = $wpdb->query( $wpdb->prepare( 
					"INSERT INTO {$wpdb->base_prefix}da_gifts ( 
						gift_name,
						gift_image
					) VALUES ( 
						%d, %d
					)", 
						$this->gift_name,
						$this->gift_image
					) );
		}

		if ( !$result )
			return false;

		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}	

		/* Add an after save action here */
		do_action( 'da_gifts_data_after_save', $this ); 
		return $result;
	}

	function delete() {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->base_prefix}da_gifts WHERE id = %d", $this->id ) );
	}

}

function da_gifts_allgift() {
	global $wpdb;
	$allgift = $wpdb->get_results("SELECT * FROM {$wpdb->base_prefix}da_gifts ");
	return $allgift;
}

function da_gifts_newgift($giftname, $giftimage) {
	global $wpdb;
	$insertgift = $wpdb->prepare("INSERT INTO {$wpdb->base_prefix}da_gifts (gift_name, gift_image) VALUES (%s, %s)",$giftname, $giftimage);
	$newgift = $wpdb->query( $insertgift );
	return $newgift;
}	

function da_gifts_counter() {
	global $wpdb;
	$add_count = $wpdb->prepare("UPDATE {$wpdb->base_prefix}da_gifts SET count = count + 1  WHERE gift_image  = %s", $_POST['gift'] );
	$gift_counter = $wpdb->query( $add_count );
	return $gift_counter;
}

add_action('init', 'sendGift');

	function sendGift(){ global $userdata, $CORE;

		// SEND GIFT FORM
		if(isset($_POST['action']) && $_POST['action'] == "sendThisgift" && $_POST['gift'] && $userdata->ID){ 

		$CORE->Language();

	 	// SAVE MESSAGE
		$Message = "
		<div class='text-center'><img src='".site_url()."/wp-content/plugins/da-gifts/includes/images/".$_POST['gift']."' alt='gift' /></div>
		<p><b><a href='".get_author_posts_url( $userdata->ID )."'>".$userdata->nickname."</a></b> ".$CORE->_e(array('dating','2'))."</p>
		".$CORE->_e(array('single','30')).": <a href='".get_permalink($_POST['pid'])."'>".get_permalink($_POST['pid'])."</a>\r\n"; 
	 
		// SENDER			 
		if(!$userdata->ID){ $userid = 1; }else{ $userid = $userdata->ID; }

		// SEND INTERNAL MESSAGE TO RECIPIENT
		$user_info = get_userdata($_POST['user_id']);		
		$my_post = array();
		$my_post['post_title'] 		= $userdata->nickname." ".$CORE->_e(array('dating','2'));
		$my_post['post_content'] 	= $Message;
		$my_post['post_excerpt'] 	= "";
		$my_post['post_status'] 	= "publish";
		$my_post['post_type'] 		= "wlt_message";
		$my_post['post_author'] 	= $userid;
		$POSTID = wp_insert_post( $my_post );
		
		// ADD SOME EXTRA CUSTOM FIELDS
		add_post_meta($POSTID, "username", $user_info->user_login );	
		add_post_meta($POSTID, "userID", $user_info->ID);	
		add_post_meta($POSTID, "status", "unread" );
		add_post_meta($POSTID, "ref", get_permalink($_POST['pid']) );

		// SEND GIFT NOTIFY EMAIL
		$_POST['message'] = $Message;
		$_POST['name'] = $userdata->nickname." ".$CORE->_e(array('dating','2'));
        $post_author_id = get_post_field( 'post_author', $_POST['pid'] );
        $CORE->SENDEMAIL($post_author_id,'contact');

		// ADD LOG ENTRY
		$CORE->ADDLOG("<a href='(ulink)'>".$userdata->user_nicename.'</a> used the send gift feature: <a href="(plink)"><b>['.get_the_title($_POST['pid']).']</b></a>.', $userdata->ID, $_POST['pid'] ,'label-info');
		 
		// UPDATE USER	
		$GLOBALS['error_message'] = $CORE->_e(array('dating','4'));

        //UPDATE IMAGE SENT COUNT
        da_gifts_counter();

        }
	
	}
?>