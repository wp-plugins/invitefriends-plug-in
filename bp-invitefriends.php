<?php 
# /* 
# Plugin Name: Invite Friends
# Plugin URI: 
# Description: Invite friends on buddypress social network from MSN, gmail, facebokk and twitter. It can easily be added to a page using the code [invitefriends]. 
# Version: 0.3.4a
# Author: Giovanni Caputo
# Author URI: 
# */ 

 /* Copyright 2008-2009 GIOVANNI CAPUTO (email: giovannicaputo86@gmail.com)

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

/* HOW TO USE:
     It can easily be added to a page using the code [invitefriends]. 
*/



require_once( 'bp-core.php' );

define ( 'BP_INVITE_FRIENDS', '0.2.0a' );

include_once( 'bp-invitefriends/bp-invitefriends-admin.php5' );


load_plugin_textdomain('invfri', site_url() . '/wp-content/mu-plugins/'.'bp-invitefriends/languages'); 

function invitefriends_nav() {
	global $bp;

	$profile_link = $bp['loggedin_domain'] . $bp['friends']['slug'] . '/';

	bp_core_add_subnav_item( $bp['friends']['slug'], __('InviteFriends'), __('Invite Friends'), $profile_link, 'wp_invitefriends' );
}
	add_action( 'wp', 'invitefriends_nav', 2 );

function wp_invitefriends() {
	add_action( 'bp_template_title', 'wp_invitefriends_title' );
	add_action( 'bp_template_content_header', 'wp_invitefriends_header' );
	add_action( 'bp_template_content', 'wp_invitefriends_content' );
	bp_catch_uri('plugin-template');
}

function wp_invitefriends_title() {
	_e('Invite contact');
}

function wp_invitefriends_header() {
	_e('Invite your contact');
}

function wp_invitefriends_content() {
    
	invitefriends_handler(null, null);
}


function invitefriends_add_js() {
	global $bp;
	wp_enqueue_script( 'filtrocontatti', site_url() . '/wp-content/mu-plugins/bp-invitefriends/js/filtrocontatti.js', false, '' );
}
add_action( 'template_redirect', 'invitefriends_add_js' );

function invitefriends_add_structure_css() {

	wp_enqueue_style( 'bp-invitefriends-structure', site_url() . '/wp-content/mu-plugins/bp-invitefriends/css/page.css' );	
}
add_action( 'bp_styles', 'invitefriends_add_structure_css' );


function invitefriends_handler($atts, $content=null) {   
  echo "<p>Plug-ins are developing!!!</p>";
  
  
  
  ?>
  <div id="invtFrinds">
  
 
  <?php 
 
  gestioneInvio();
  if (is_user_logged_in()){
     if (!isset ($_GET['appid'])){
       if (isset($_POST['op'])){
		switch ($_POST['op']){
			case "webMail":
				webMailHandler($_POST['webmailType']);
				break;
			case "selected":
				/*SELEZIONATE MAIL*/
				$mail=$_POST['mail'];
				//print_r($mail);
				addFriends($mail);  
				break;
		     case "manual":
			   $mail=str_replace(" ","", $_POST['email_addresses']);
			   $mail=explode(",", $mail );
			   addFriends($mail); 
			 break;
		}
	 
	   echo "</br>";
	   echo "<p></br><a href='".get_permalink()."'>";
	   echo __("Invites other friends");
	   echo "</a></p>";   
	}else init_form(); //form iniziale
   }else{
					include ("bp-invitefriends/mYahooApi.class.php");
					$Yahoo=new connectToYahooApi();
					$Yahoo->CreateLink();
    $Yahoo->seeYahooContact();
   }
  }else   echo __("<p>You need to be logged TO INVITE FRIENDS</p>");  // NON LOGGATO
  ?></div><?php
 }
 
 function webMailHandler($scelta){
 $salvata=get_option("wp_InviteFriends");
      $usr=$_POST['Email'];
	   $pwd=$_POST['Passwd'];
	   switch ($scelta) {
	   case "hotmail":
	      if ($salvata['HotmailMod']=='cURL'){
			include_once( 'bp-invitefriends/lib/msnm.class.php' );
			   $msn2 = new msn;
		      $listMail = $msn2->qGrab($usr, $pwd);
			  selectfriends($listMail);
			} else{
			   echo __("Messenger API don't work");
			}
		    //print_r($returned_emails);			
           break;
	   case "gmail":
            if ($salvata['GMailMod']=='cURL'){
			   include_once( 'bp-invitefriends/lib/msnm.class.php' );
			   $msn2 = new msn;
		      $listMail = $msn2->qGrab($usr, $pwd);
			  selectfriends($listMail);
			} else{
			    
				$oldPath = set_include_path(get_include_path().PATH_SEPARATOR .$salvata['ZendUrl']);
				require_once 'bp-invitefriends/lib/Gmail/library/Zend/Loader.php';
				Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
				Zend_Loader::loadClass('Zend_Gdata_Gapps');
				Zend_Loader::loadClass('Zend_Gdata_Query');
	
				// Using Client Login
				$client = Zend_Gdata_ClientLogin::getHttpClient($usr,$pwd, "cp");
				$gdata = new Zend_Gdata($client);
				$query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/default/full');
				$query->setMaxResults(10000);
				$feed = $gdata->getFeed($query);
				$xml = new SimpleXMLElement($feed->getXML());
				$entries = $xml->children('http://www.w3.org/2005/Atom');
				$cont=0;
				foreach ($entries->entry as $entry) {
						$defaults = $entry->children('http://schemas.google.com/g/2005');
						$a = $defaults->email->attributes();
					   $listMail[$cont++] = Array($a->address);
					   
				}
				selectfriends($listMail);
	
			}
			
           break;
	   case "yahoo":
            if ($salvata['YahooMod']=='cURL'){
			   include_once( 'bp-invitefriends/lib/importYahoo.class.php' );
			   $yahoo = new yahooGrabber($usr, $pwd);
			   $list = $yahoo->grabYahoo();
			   $cont=0;
			   foreach ($list as $c ) $listMail[$cont++]=Array($c);
			  selectfriends($listMail);
			} 

			break;
	   case "aol":
            if ($salvata['aolMod']=='cURL'){
			   include_once( 'bp-invitefriends/lib/importAol.class.php' );
			   $aol = new grabAol($usr, $pwd);
			   $list = $aol->getContactList();
			   //var_dump($list);
			   $cont=0;
			   foreach ($list as $c ) $listMail[$cont++]=Array($c);
			  selectfriends($listMail);
			} 
           break;
	   }
 }
 



function init_form(){
?>
    <?php $salvata=get_option("wp_InviteFriends");?>
    <div><p><?php echo _("We won't store your password or contact anyone without your permission.");?></p></div>
	  
       <form action="<?php get_permalink(); ?>" method="post" accept-charset="UTF-8" name="inviteFriendsForm" onSubmit="return checkUsPwd()">
            <ul id="tipologie">
                <li id="hotmail">
                    <input onclick="inputSelection(this,'<?php echo $salvata['HotmailMod'];?>')" name="webmailType" value="hotmail" checked="checked" id="hotmail-webmailType-emailParam-getContactsForm" class="hotmail" type="radio">
                    <label for="hotmail-webmailType-emailParam-getContactsForm">
                        <img src="<?php echo site_url() . '/wp-content/mu-plugins/'.'/bp-invitefriends/'?>images/logo_hotmail_109x14.gif" alt="Windows Live Mail" width="109" height="14">
                    </label>
                </li>
                <li id="gmail">
                    <input onclick="inputSelection(this,'<?php echo $salvata['GMailMod'];?>')" name="webmailType" value="gmail" id="gmail-webmailType-emailParam-getContactsForm" class="gmail" type="radio">
                    <label for="gmail-webmailType-emailParam-getContactsForm">
                        <img src="<?php echo site_url() . '/wp-content/mu-plugins/'.'/bp-invitefriends/'?>images/logo_gmail_50x23.gif" alt="Google Mail" width="50" height="23">
                    </label>
                </li>
                <li id="yahoo">
                    <input onclick="inputSelection(this,'<?php echo $salvata['YahooMod'];?>')"  name="webmailType" value="yahoo" id="yahoo-webmailType-emailParam-getContactsForm" class="yahoo" type="radio">
                    <label for="yahoo-webmailType-emailParam-getContactsForm">
                        <img src="<?php echo site_url() . '/wp-content/mu-plugins/'.'/bp-invitefriends/'?>images/logo_yahoo_80x23.gif" alt="Yahoo!" width="80" height="23">
                    </label>
                </li>
                <li id="aol">
                    <input  onclick="inputSelection(this,'<?php echo $salvata['YahooMod'];?>')" name="webmailType" value="aol" id="aol-webmailType-emailParam-getContactsForm" class="aol" type="radio">
                    <label for="aol-webmailType-emailParam-getContactsForm">
                        <img src="<?php echo site_url() . '/wp-content/mu-plugins/'.'/bp-invitefriends/'?>images/logo_aol_56x23.gif" alt="AOL" width="56" height="23">
                    </label>
                </li>
            </ul>
			<div id="usr_pwd">
            <ul id="dati">
                <li>
                    <label id="userName">
                        Mail:
                    </label>
                    <input name="Email" value="" class="inpt" type="text">
                </li>
                <li>
                    <label>
                        Password:
                    </label>
                    <input name="Passwd" value="" class="inpt" type="password">
                </li>
            </ul>
			   <input type="hidden" name="op" value="webMail">
            <p>
                <input id="btn" class="inputsubmit" name="btnUpContact" value="<?php echo __("Upload Contacts");?>" type="submit">
            </p>
			</div>
			
			
				<div id="yahooAPI"  style="display:none">
				<div id="">
				<?php 
					include ("bp-invitefriends/mYahooApi.class.php");
					$Yahoo=new connectToYahooApi();
					$Yahoo->CreateLink();
				?>
				</div>
				<div id=""> </div>
				</div>
			
			
       </form>
	   <div class="manual">
	   <form action="<?php get_permalink(); ?>" method="post" >
	       <label><?php echo __("Invite:"); ?></label>
		   <div><small style="color: gray;"><?php echo __("Enter your e-mail separated by commas");?></small>
		   </div>
		   <input type="hidden" name="op" value="manual">
		   <div class="module_options clearfix"><textarea name="email_addresses" class="textarea" rows="4" id="email_addresses"> </textarea>
		      <input class="inputsubmit" id="btn" name="manual" value="<?php echo __("Add");?>" type="submit">
			</div>
		</form>
		</div>
  



<?php
}

function gestioneInvio(){
	include_once( 'bp-friends/bp-friends-classes.php' );
	$salvata=get_option("wp_InviteFriends");
	$mailSender=$salvati['mail'];
	global $current_user;
	$iduser=$current_user->ID;
	
}


function selectfriends($listMail){
	global $current_user;
	$iduser=$current_user->ID;	
	$urlpag=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if (strpos($urlpag, '?')!== FALSE ) 	$urlpag = substr($urlpag, 0, strpos($urlpag, '?')); 
?>
		<div class="column right">
		   <div class="filter_form">
		    <label><?php echo _("Filter");?>:</label>
		     <input type="text" id="myfilter" onkeyup="filter()">
		  </div>
		  <form action="<?php echo  "http://".$urlpag."/";?>" method="post" accept-charset="UTF-8" onSubmit="return someSelected()">      
	        <div id="userlist" class="userlist" style="height:250px;">
		     <span id="friends_list">
			 <?php
			 if (!empty($listMail)){
			   foreach ($listMail as $cnt) {
			    $type=typeuser($iduser, $cnt[0]);
			    ?>
                 <span u_mail="<?php echo $cnt[0];?>">
				      <input id="<?php echo $cnt[0];?>"class="inputcheckbox"  name="mail[]" value="<?php echo $cnt[0];?>" type="checkbox" 
					  <?php if ($type=='friends') echo "DISABLED";?>
					  >
					  <label for="<?php echo $cnt[0];?>" id="<?php echo $type?>"><?php echo $cnt[0];?></label><br>
			    </span>
				<?php
               }
			 }else{echo _("Contact list is empty");}
			 ?>
		    </span>
			
		  </div>
		  <input id="btncheck" type="button" name="CheckAll" value="Check All" onClick="checkAll(true)">
          <input id="btncheck"  type="button" name="UnCheckAll" value="Uncheck All"onClick="checkAll(false)">
          <input type="hidden" name="op" value="selected">
		  <input class="inputsubmit" id="email_button" name="confermaMail" value="<?php echo __("Add");?>" type="submit">
		 </form>
		
		<div class="separator"></div><br>
		
     </div>
<?php 
}

function addFriends($destinatari){
    global $current_user;global $wpdb;
	$iduser=$current_user->ID;
	foreach ($destinatari as $mai) {
	  if (isemail($mai)){
		$type=typeuser($iduser, $mai);
		//echo "<br>".$mai." is ".$type."<br>";
		switch($type){
			case 'registred':
				echo __("Request of Friends: ").$mai."<br>";
				$sql = $wpdb->prepare( "SELECT id ". "FROM $wpdb->users " ."where user_email='".$mai."'");
				$result=$wpdb->get_results($sql);
				//echo "<br> id of ".$mai." is ".$result[0]->id. "<br>";
				friends_add_friend( $iduser, $result[0]->id);
			break;
			case 'notregistred':
				echo __("Request registration: ").$mai."<br>";
				send_mail($mai);
			break;
		}	
	  }else echo $mai.__(" is not a mail!");
	}
}


function isemail($email) {
    return preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
}


function send_mail($mail){
	global $current_user;
	$iduser=$current_user->ID;	
	$user_info = get_userdata($iduser);
	$nome=$user_info-> first_name;
	$cognome=$user_info-> last_name;
	$nick=$user_info-> nickname;
	$salvati=get_option("wp_InviteFriends");
	$sendermail=$salvati['mail'];
	
	$header = "From: <".$sendermail.">\n";
	$header .= "CC:".__("Sender")."<"." ".">\n";
	$header .= "X-Mailer:".__("Invite")."\n";
	// generiamo la stringa che funge da separatore
	$boundary = "==String_Boundary_x" .md5(time()). "x";
	// costruiamo le intestazioni che specificano
	// un messaggio costituito da più parti alternative
	$header .= "MIME-Version: 1.0\nContent-Type: multipart/alternative;\n boundary=\"$boundary\";\n\n";

	// questa parte del messaggio viene visualizzata
	// solo se il programma non sa interpretare
	// i MIME poiché è posta prima della stringa boundary
	$messaggio = __("Your mail client does not support MIME Type\n\n");
	// inizia la prima parte del messaggio in testo puro
	$messaggio .= "--$boundary\nContent-Type: text/plain; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 7bit\n\n";
	 $messaggio .= __("User ").$nome." ".$cognome." ( ". $nick. __(") suggest you to sign up. Go to :").get_bloginfo('url')."\n\n".__("\n\n");// inizia la seconda parte del messaggio in formato html
		  $messaggio .= "--$boundary\nContent-Type: text/html; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 7bit\n\n";
        	$messaggio .= "<html><body><p>User ".$nome." ".$cognome." (". $nick. ") suggest you to sign up :</p><p>Go on  <a href=\"".get_bloginfo('url')."\">".get_bloginfo('url')."</a></p>". bp_core_get_avatar($iduser)."</body></html>\n";
			$messaggio .= "--$boundary--\n";
		$subject = __("Invite to ").get_bloginfo('name'). __(" net.");
	if( mail($mail, $subject, $messaggio, $header) ) _e("e-mail sent successfully!")."<br>";
	else _e("error on sent e-mail")."<br>";
}



/*return if $mail is a mail of a friend of user, or if is only registred or if not registred 
*/
function typeuser($iduser, $mail){
	global $wpdb, $bp;
	include_once( 'bp-xprofile/bp-xprofile-classes.php' );
	$friendship = new BP_Friends_Friendship( $iduser, false, true );
	//echo "hai ".$friendship->total_friend_count( $iduser )."amici";
	$amici=$friendship->get_friend_user_ids($iduser, false, false);
	//echo "['profile']['table_name_data'] ".$bp['profile']['table_name_data'] ;
	//print_r($wpdb);	
	
	if ($amici){
	  foreach($amici as $amico){
	      $user_info = get_userdata($amico);
		  
		  if (($user_info->user_email)==$mail) {return 'friends';};
	  }
	}

	$sql = $wpdb->prepare( "SELECT id, user_email, display_name ". "FROM $wpdb->users " ."where user_email='".$mail."'");
	if ( !$values = $wpdb->get_results($sql) )
			return 'notregistred';
			else return 'registred';
	
}

/*----------------------------------------------------------------------------------------------------    AMMINISTRAZIONE  ---------------------------------------------------------------------------------*/
	add_action('admin_menu', 'add_invitefriendsPage');
	add_action('admin_head', 'admin_invitefriends_header');

/* -----------------------------------------------------------------------------------------------------------AMMINISTRAZIONE-----*/


add_shortcode('invitefriends', 'invitefriends_handler');
/*CSS Styling*/
function wpcf_css() {  	
   ?>
	   <link rel="stylesheet" type="text/css" media="screen" href="<?php echo site_url() . '/wp-content/mu-plugins'; ?>/bp-invitefriends/css/page.css" />
	<?php   
} 


function enqueue_test() {
  //wp_register_script( 'myjquery', get_bloginfo('wpurl') . '/wp-content/plugins/invitefriends/js/jquery.js', false, '' );
  // wp_enqueue_script('myjquery');
   wp_enqueue_script('filtrocontatti', site_url() . '/wp-content/mu-plugins/bp-invitefriends/js/filtrocontatti.js', false, ''); 
}

add_action('wp_head', 'wpcf_css');
add_action ('init', 'enqueue_test');



















?>