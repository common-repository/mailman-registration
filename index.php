<?php
/*
Plugin Name: Mailman Registration
Plugin URI: http://www.heikoch.de
Description: Mailman Registration
Version: 0.0.2
Author: heikoch
URI: http://www.heikoch.de
License: 
*/
/*
English
This plugin allows prospective customers to log on to a Mailman list.
Operation:
The plugin creates a txt file where the E-mail addresses are stored in the associated widget or shortcode form in the upload folder of WordPress.
This txt file can then be used to write to the interested parties through a cron job on the mailing list.
Comment:
This plugin is only for users who also have access to the Mailman and cron job administration of the hosting system. Without access to it it does not work.

German
Dieses Plugin ermöglicht Interessenten sich an einer Mailman Liste anzumelden.
Arbeitsweise:
Das Plugin erzeugt im upload Ordner von Wordpress eine TXT-Datei wo dann die E-Mail Adressen aus dem zugehörigem Widget oder Shortcode Formular oder gespeichert werden.
Diese TXT-Datei kann dann dazu genutzt werden um die Interessenten über einen Cron-Job in die Mailingliste einzutragen. 

Bemerkung:
Dieses Plugin ist ist nur für User die auch Zugriff auf die Mailman und Cron-Job Administration des Hosting-Systems haben. Ohne Zugriff darauf funktioniert es nicht.
*/
load_plugin_textdomain('mm-registration', false, dirname(plugin_basename(__FILE__)) . '/languages');
$option_name = 'mm_registration_path' ;
if ('insert' == $_POST['action']) {
    if ( get_option( $option_name ) !== false ) {
        update_option($option_name,esc_html($_POST[$option_name]));
    } else {
        $deprecated = null;
        $autoload = 'no';
        add_option( $option_name,esc_html($_POST[$option_name]), $deprecated, $autoload );
    }
    if (!is_file($_POST[$option_name])) {
        $handle = fopen($_POST[$option_name], "x");
        fclose($handle);
    }
}
if ('reset' == $_POST['action']) {
    $mm_registration_path = get_option('mm_registration_path');
    $handle = fopen($mm_registration_path, "w+");
    fclose($handle);
}

function mm_registration_option_page() { 
    $mm_registration_path = get_option('mm_registration_path');
    $upload_dir = wp_upload_dir();
    $pwd=substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'),0,16);
?>
<div class="wrap">
    <h2>MM-Registration Optionen</h2>
    <?php if (is_file($mm_registration_path)) { ?>
    <p><?php _e("The following file is registered and writable", 'mm-registration'); ?>: <br />
    <code><?php echo $mm_registration_path; ?></code>
    </p>
    <?php $lines = file($mm_registration_path); ?>
    <?php if (count($lines)==0) { ?>
    <form name="form0" method="post" action="<?=$location ?>">
        <input name="mm_registration_path" type="hidden" value="" />
        <input type="submit" name="submit" value="<?php _e("Reset", 'mm-registration'); ?>" />
        <input name="action" value="insert" type="hidden" />
    </form>
    <?php } ?>
    <p><?php _e("Proposal for cron job command", 'mm-registration'); ?>:<br />
    <code>/usr/sbin/add_members -r <?php echo $mm_registration_path; ?> nameofmailinglist && echo ""><?php echo $mm_registration_path; ?></code>
    </p>
    <p><?php _e("Current contents of", 'mm-registration'); ?> <?php echo $mm_registration_path; ?>:<br />
<?php 
    echo "<pre>\n";
    foreach ($lines as $line_num => $line) {
        echo $line;
    }
    echo "</pre>\n";
?>
    </p>
    <?php if (count($lines)!=0) { ?>
    <form name="form1" method="post" action="<?=$location ?>">
        <input name="mm_registration_path" type="hidden" value="" />
        <input type="submit" name="submit" value="<?php _e("Empty file", 'mm-registration'); ?>" />
        <input name="action" value="reset" type="hidden" />
    </form>
    <?php } ?>
    <?php } else { ?>
    <p><?php _e("This plugin requires a writable TXT file", 'mm-registration'); ?><br />
    <?php _e("Suggestion", 'mm-registration'); ?>:<br />
    <code><?php echo $upload_dir['basedir']."/<strong>".$pwd.".txt";?></strong></code><br />
    <a href=""><?php _e("Creating a new proposal", 'mm-registration'); ?>!</a>
    </p>
    <form name="form2" method="post" action="<?=$location ?>">
        <input name="mm_registration_path" type="hidden" value="<?php echo $upload_dir['basedir']."/".$pwd.".txt"; ?>" />
        <input type="submit" name="submit" value="<?php _e("Accept proposal", 'mm-registration'); ?>" />
        <input name="action" value="insert" type="hidden" />
    </form>
    <?php } ?>
    <h3>Shortcode</h3>
    <p>With this Sample-Shortcode can you place a MM-Pigistration Form in your posts or pages.<br />
    <code>[mm_registration msg_thank_you="your_msg_thank_you" msg_error="your_msg_error" msg_email_not_valid="your_msg_email_not_valid"]</code>
    </p>
</div>
<?php }
function mm_registration_description_add_menu() {
	add_options_page('MM Registration', 'MM Registration', 9, __FILE__, 'mm_registration_option_page');
}
add_action('admin_menu', 'mm_registration_description_add_menu');

function mm_registration_js(){
    wp_enqueue_script(
            'mm_registration',
            plugins_url( '/js/mm_registration.js' , __FILE__ ),
            array('jquery')
    );
}

function mm_registration_ajax(){
    $email = $_POST['email'];
    $mm_registration_path = get_option('mm_registration_path');
    if (is_writable($mm_registration_path)) {
        if (!$handle = fopen($mm_registration_path, "a")) {
            $error="Kann die Datei ".$mm_registration_path." nicht öffnen";
            exit;
        }
        if (!fwrite($handle, $email."\n")) {
            $error="Kann in die Datei ".$mm_registration_path." nicht schreiben";
            exit;
        }
        $msg="Emailadresse ".$email." erfolgreich angemeldet!";
        fclose($handle);
    } else {
        $error="Die Datei ".$mm_registration_path." ist nicht schreibbar";
    }
    if ($error) {
        echo $error;
    } else {
        echo $msg;
    }
}

class mm_registration_widget extends WP_Widget {  
    public function mm_registration_widget() {
        parent::WP_Widget(false, 'Mailman-Registration Widget');  
    }
    public function widget($args, $instance) {
        $args['title'] = $instance['title'];
        $args['msg_thank_you'] = $instance['msg_thank_you'];
        $args['msg_email_not_valid'] = $instance['msg_email_not_valid'];
        $args['msg_error'] = $instance['msg_error'];
        mm_registration($args);
    }
    public function update($new_instance, $old_instance) {
        return $new_instance;
    }
    public function form($instance) {
        $title = esc_attr($instance['title']);
        $msg_thank_you = esc_attr($instance['msg_thank_you']);
        $msg_email_not_valid = esc_attr($instance['msg_email_not_valid']);
        $msg_error = esc_attr($instance['msg_error']); ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget <?php _e('Title', 'mm-registration'); ?></label>
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br />
         
            <label for="<?php echo $this->get_field_id('msg_thank_you'); ?>"><?php _e('Thank you', 'mm-registration'); ?> MSG:</label>
            <input id="<?php echo $this->get_field_id('msg_thank_you'); ?>" name="<?php echo $this->get_field_name('msg_thank_you'); ?>" type="text" value="<?php echo $msg_thank_you; ?>" /><br />
            
            <label for="<?php echo $this->get_field_id('msg_email_not_valid'); ?>"><?php _e('E-Mail not valid', 'mm-registration'); ?> MSG:</label>
            <input id="<?php echo $this->get_field_id('msg_email_not_valid'); ?>" name="<?php echo $this->get_field_name('msg_email_not_valid'); ?>" type="text" value="<?php echo $msg_email_not_valid; ?>" /><br />

            <label for="<?php echo $this->get_field_id('msg_error'); ?>"><?php _e('Error', 'mm-registration'); ?> MSG:</label>
            <input id="<?php echo $this->get_field_id('msg_error'); ?>" name="<?php echo $this->get_field_name('msg_error'); ?>" type="text" value="<?php echo $msg_error; ?>" /><br />
        </p>
<?php  
    }
}

function mm_registration($args) {
    extract($args);
    echo $before_widget;
    echo $before_title.$args['title'].$after_title;
    echo "<form id=\"mmnewsletter\">\n";
    echo "<input type=\"text\" size=\"20\" name=\"email\" id=\"email\" />\n";
    echo "<input id=\"mmnewsletter_submit\" type=\"button\" value=\"". __('Registration', 'mm-registration')."\" />\n";
    echo "<input name=\"action\" type=\"hidden\" value=\"addmmnewsletter\" />\n";
    echo "</form>";
    echo "<div id=\"mm_registration_out\">&nbsp;<br />&nbsp;</div>";
    echo $after_widget; ?>
<script type='text/javascript'>
jQuery(document).ready(function(){
    jQuery('#mmnewsletter_submit').click(function(){
        var email = jQuery.trim(jQuery('#email').val());
        var regex = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        if(!regex.test(email)) {
            jQuery('#mm_registration_out').html("<span class='error'><?php echo $args['msg_email_not_valid']; ?></span>");
            return false;
        } else {
            doAjaxRequest();
        }
     });
});
function doAjaxRequest(){
     // here is where the request will happen
     var request = jQuery.ajax({
          url: '/wp-admin/admin-ajax.php',
          type: "POST",
          data:{
               'email': jQuery.trim(jQuery('#email').val()),
               'action':'mm_registration_ajax'
          },
          dataType: "html"
    });
    request.done(function(msg) {
        jQuery('#mm_registration_out').html("<span class='msg'><?php echo $args['msg_thank_you']; ?></span>");
    });
    request.fail(function( jqXHR, textStatus ) {
        jQuery('#mm_registration_out').html("<span class='error'><?php echo $args['msg_error']; ?></span>");
    });
}
</script>
<?php }
function mm_registration_register_widgets() {
	register_widget( 'mm_registration_widget' );
}
add_action( 'widgets_init', 'mm_registration_register_widgets' );

function mm_registration_shortcode_func($atts) {
extract( shortcode_atts( array(
    'msg_thank_you' => '',
    'msg_error' => '',
    'msg_email_not_valid' => ''
), $atts ) );
$out ="<form id=\"mmnewsletter_shortcode\" name=\"mmnewsletter_shortcode\">\n";
$out.="<input type=\"text\" name=\"email\" id=\"email\" />\n";
$out.="<input id=\"mmnewsletter_shortcode_submit\" type=\"button\" class=\"button\" value=\"".__('Registration', 'mm-registration')."\" />\n";
$out.="<input name=\"action\" type=\"hidden\" value=\"addmmnewsletter\" />\n";
$out.="</form>\n";
$out.="<div id=\"mm_registration_shortcode_out\">&nbsp;</div>\n"; 
$out.="<script type=\"text/javascript\">
    jQuery(document).ready(function(){
    jQuery('#mmnewsletter_shortcode_submit').click(function(){
        var email = jQuery.trim(jQuery('#email').val());
        var regex = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        if(!regex.test(email)) { 
            jQuery('#mm_registration_shortcode_out').html('<span class=\"error\">".$msg_email_not_valid."</span>');
            return false;
        } else {
            doAjaxRequest();
        }
     });
});
function doAjaxRequest(){
     // here is where the request will happen
     var request = jQuery.ajax({
          url: '/wp-admin/admin-ajax.php',
          type: 'POST',
          data:{
               'email': jQuery.trim(jQuery('#email').val()),
               'action':'mm_registration_ajax'
          },
          dataType: 'html'
    });
    request.done(function(msg) {
        jQuery('#mm_registration_shortcode_out').html('<span class=\"msg\">".$msg_thank_you."</span>');
    });
    request.fail(function( jqXHR, textStatus ) {
        jQuery('#mm_registration_shortcode_out').html('<span class=\"error\">".$msg_error."</span>');
    });
}
</script>\n";
return $out;
}
add_shortcode( 'mm_registration', 'mm_registration_shortcode_func' );

add_action( 'wp_ajax_nopriv_mm_registration_ajax', 'mm_registration_ajax' );  
add_action( 'wp_ajax_mm_registration_ajax', 'mm_registration_ajax' ); 
?>