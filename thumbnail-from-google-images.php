<?php
/*
Plugin Name: Thumbnails from Google Images
Plugin URI: http://wordpress.org/extend/plugins/thumbnail-from-google-images/
Description: Fastest way to choose thumbnail for your posts.
Version: 1.0
Author: Ondřej Dadok
Author URI: http://www.ondrejdadok.cz/thumbnails-from-google-images/
*/
add_theme_support('post-thumbnails');

// create custom plugin settings menu
add_action('admin_menu', 'gst_create_menu');

function gst_create_menu() {

	//add_options_page( 'Google Images Search Thumbnail', 'Thumbnail GIS', 'manage_options', 'gis_settings_page', 'gis_settings_page' );

}

add_action( 'admin_print_footer_scripts', 'remove_save_button' );
function remove_save_button()
{   

global $post;

if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') ) {
?>

<style type="text/css">
  .gst-title{
    color: #a0a0a0;
    float: right;
    font-size: 80%;
    line-height: 150% !important;
    text-align: right;
  }
  .gst-holder{
    height: auto;
    position: inherit;
  }
  .gst-holder img{
  	width: 100px;
  	height: auto;
    border-radius: 7px;
  }
  .gstthumbimg-cont{
    text-align: center;
    float: left;
    display: block;
    width: 100px;
    height: 70px;
    margin: 5px 5px 5px 5px;
    overflow: hidden;
    border-radius: 7px;
  }
  .gstthumbimg-cont:hover{
    border-radius: 7px;
    cursor: pointer;
  }


  #preview img{
    vertical-align: middle;
    margin: auto;
    padding: auto;
    max-height: 130px;
    width: auto;
  }
 .gstlabel{
    padding: 7px;
    font-size: 100%;
    opacity: 0.8;
    position: absolute;
    color: #fff;
    line-height: 450%;
    text-align: center;
    text-shadow: 1px 1px 1px #000;
    width: 80px;
  }
   #preview {
    text-align: center;
    display: none;
    background-color: #fff0a0;
    background-image: -webkit-linear-gradient(top, hsla(0,0%,100%,.5), hsla(0,0%,100%,0));
    background-image:    -moz-linear-gradient(top, hsla(0,0%,100%,.5), hsla(0,0%,100%,0));
    background-image:     -ms-linear-gradient(top, hsla(0,0%,100%,.5), hsla(0,0%,100%,0));
    background-image:      -o-linear-gradient(top, hsla(0,0%,100%,.5), hsla(0,0%,100%,0));
    background-image:         linear-gradient(top, hsla(0,0%,100%,.5), hsla(0,0%,100%,0));
    border-radius: 5px;
    box-shadow: inset 0 1px 1px hsla(0,0%,100%,.5),
                3px 3px 0 hsla(0,0%,0%,.1);
    color: #333;
    font: 16px/25px sans-serif;
    padding: 15px 25px;

    position: absolute;
    z-index: 4000000;

    text-shadow: 0 1px 1px hsla(0,0%,100%,.5);
}
 #preview:after,  #preview:before {
    border-bottom: 25px solid transparent;
    border-right: 25px solid #fff0a0;
    bottom: -25px;
    content: '';
    position: absolute;
    right: 25px;
}
 #preview:before {
    border-right: 25px solid hsla(0,0%,0%,.1);
    bottom: -28px;
    right: 22px;
}
.gst-holder-loading{
  display: none;
  text-align: center;
  width: 100%;
}
.gst-holder-loading img{
  vertical-align: middle;
  text-align: center;
  height: 40px;
  width: 180px;
  margin: auto;
  padding: auto;
}
</style>



<script>


jQuery(document).ready(function(){
 
     jQuery('#gst-holder-submit').click(function(){ 
     		     
             jQuery(".gst-holder").css("display", "none");
             jQuery(".gst-holder-loading").css("display", "block");


     	       var val = jQuery('#json_click_handler').val();
               doAjaxRequest(val);

    });

});

function fixedEncodeURIComponent(str){
     return encodeURIComponent(str).replace(/[!'()]/g, escape).replace(/\*/g, "%2A");
}

function doAjaxRequest(val){
     var val = fixedEncodeURIComponent(val);

     jQuery.ajax({
          url: '/wp-admin/admin-ajax.php',
          data:{
               'action':'do_ajax',
               'fn':'get_latest_posts',
               'val':val
               },
          dataType: 'JSON',
          success:function(data){
     
          	 jQuery(".gst-holder").css("height", "auto");
             jQuery(".gst-holder").css("float", "left");
     
             jQuery('.gst-holder').hide();
             jQuery(".gst-holder-loading").hide();
          	 
             jQuery('.gst-holder').html(data);

          	 jQuery('.gst-holder').fadeIn('slow');
     
          	 jQuery.getScript("/wp-content/plugins/thumbnails-from-google-images/js.js", function(data, textStatus, jqxhr) {
			   
         console.log(data); //data returned
			   console.log(textStatus); //success
			   console.log(jqxhr.status); //200
			   console.log('Load was performed.');
			});

          },
          error: function(errorThrown){
               alert('Thumbnails from Google Images timeout. Please try again later.');
               console.log(errorThrown);
          }
           
 
     });

   
}

</script>



<?php
  }
}




function search_file_inmedia( $file_url ){
  global $wpdb;
  $filename = basename( $file_url );

  $rows = $wpdb->get_var("
  SELECT     COUNT(*)
  FROM       wp_postmeta 
             WHERE wp_postmeta.meta_key = '_wp_attached_file'
             AND wp_postmeta.meta_value LIKE '%".like_escape($filename)."%'
  ");

	return $rows;

}

function search_file_inmedia_id( $file_url ){
  global $wpdb;
  $filename = basename( $file_url );

  $rows = $wpdb->get_row("
  SELECT     post_id
  FROM       wp_postmeta 
             WHERE wp_postmeta.meta_key = '_wp_attached_file'
             AND wp_postmeta.meta_value LIKE '%".like_escape($filename)."%'
  ");

	return $rows->post_id;
}

function save_image($url) {
		global $post;

		$filename = explode("/", $url);
		$filename = array_reverse($filename);

		$filename = $filename[0];

		if(search_file_inmedia( $filename ) > 0){

		set_post_thumbnail( $post, search_file_inmedia_id($filename) );
	
		}else{

    	//$youtube_url = get_post_meta( $post->ID, 'videobox', true );
	    //$youtubeid = youtubeid($youtube_url);
        //$thumb_url = 'http://img.youtube.com/vi/'. $youtubeid .'/0.jpg';
		$thumb_url = $url;


        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        set_time_limit(300);

        if ( ! empty($thumb_url) ) {
            // Download file to temp location
            $tmp = download_url( $thumb_url );

            // Set variables for storage
            // fix file filename for query strings
            preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $thumb_url, $matches);
            $file_array['name'] = basename($matches[0]);
            $file_array['tmp_name'] = $tmp;

            // If error storing temporarily, unlink
            if ( is_wp_error( $tmp ) ) {
                @unlink($file_array['tmp_name']);
                $file_array['tmp_name'] = '';
            }

            // do the validation and storage stuff
            $thumbid = media_handle_sideload( $file_array, $post->ID, $desc );
            // If error storing permanently, unlink
            if ( is_wp_error($thumbid) ) {
                @unlink($file_array['tmp_name']);
                return $thumbid;
            }
        }

        set_post_thumbnail( $post, $thumbid );

    	}
}




add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );






add_action( 'save_post', 'myplugin_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function myplugin_add_custom_box() {
    $screens = array( '', 'page', 'side');
    foreach ($screens as $screen) {
        add_meta_box(
            'myplugin_sectionid',
            __( 'Thumbnails from Google Images', 'myplugin_textdomain' ),
            'myplugin_inner_custom_box',
            $screen
        );
    }
}

add_action('wp_ajax_nopriv_do_ajax', 'our_ajax_function');
add_action('wp_ajax_do_ajax', 'our_ajax_function');

function our_ajax_function(){
 
   // the first part is a SWTICHBOARD that fires specific functions
   // according to the value of Query Var 'fn'
 
     switch($_REQUEST['fn']){
          case 'get_latest_posts':
               $output = ajax_get_latest_posts($_REQUEST['val']);
          break;
          default:
              $output = 'No function specified, check your jQuery.ajax() call';
          break;
 
     }
 
   // at this point, $output contains some sort of valuable data!
   // Now, convert $output to JSON and echo it to the browser 
   // That way, we can recapture it with jQuery and run our success function
 
          $output=json_encode($output);
         if(is_array($output)){
        print_r($output);   
         }
         else{
        echo $output;
         }
         die;
 
}

function ajax_get_latest_posts($val){
	global $post;

	for ($i = 1; $i <= 12; $i++) {
			 $image = GetRandomImageURL($val, $i, 100);

      if(get_post_meta( $post->ID, '_my_meta_value_key', true ) == $image){$selected = " selected";}else{$selected = "";}
        
      //if (@getimagesize($image)) {
          $imgs.= '<div class="gstthumbimg-cont"><img src="'.$image.'" class="gstthumbimg '.$selected.'"/></div>';
      //}

      unset($selected);

		    

	}

	return $imgs;

}


function GetRandomImageURL($topic='', $min=0, $max=100)
{
  // get random image from Google
  if ($topic=='') $topic='image';
  $ofs=mt_rand($min, $max);

  //WHEN WE NEED RAND RESULTS
  //$geturl='http://www.google.ca/images?q=' . $topic . '&start=' . $ofs . '&gbv=1';
  
  $geturl='http://www.google.ca/images?q=' . $topic . '&start=' . $min . '&gbv=1';
  
  $data=file_get_contents($geturl);
 
  $f1='<div id="center_col">';
  $f2='<a href="/imgres?imgurl=';
  $f3='&amp;imgrefurl=';
 
  $pos1=strpos($data, $f1)+strlen($f1);
  if ($pos1==FALSE) return FALSE;
  $pos2=strpos($data, $f2, $pos1)+strlen($f2);
  if ($pos2==FALSE) return FALSE;
  $pos3=strpos($data, $f3, $pos2);
  if ($pos3==FALSE) return FALSE;
  return substr($data, $pos2, $pos3-$pos2);
}
 

function PluginUrl() {

        //Try to use WP API if possible, introduced in WP 2.6
        if (function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));

        //Try to find manually... can't work if wp-content was renamed or is redirected
        $path = dirname(__FILE__);
        $path = str_replace("\\","/",$path);
        $path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
        return $path;
}



/* Prints the box content */
function myplugin_inner_custom_box( $post ) {
	global $post;
  

	?>
  <div id="preview"></div>
	<input type="text" id="json_click_handler" size="20" value="<?php echo $post->post_title; ?>"><input type="button" id="gst-holder-submit" value="Search Thumbnails" />
	 
  <table>
  <td>
  <?php


	
	$post_title = urlencode($post->post_title);

	echo '<div class="gst-holder-loading"><center><img src="'.PluginUrl().'/loading.gif" /></center></div><div class="gst-holder">';
	
	echo '</div>';

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  // The actual fields for data entry
  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
  $value = get_post_meta( $post->ID, '_my_meta_value_key', true );

  if (isset($_POST['data']))
  {
    $data = $_POST['data'];
  }else{
    $data = '';
  }


  echo '<input type="hidden" id="gst_myplugin_new_field" name="gst_myplugin_new_field" value="'.esc_attr($value).'" size="25" />';
  echo '<div id="gst-holder" style="">'.$data.'</div>';

  echo '</td></table>';

}

/* When the post is saved, saves our custom data */
function myplugin_save_postdata( $post_id ) {

if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') ) {

  // First we need to check if the current user is authorised to do this action. 
  if ( 'page' == $_POST['post_type']) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }


  // Thirdly we can save the value to the database

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $mydata = sanitize_text_field( $_POST['gst_myplugin_new_field'] );

  $value = get_post_meta( $post_ID, '_my_meta_value_key', true );

  add_post_meta($post_ID, '_my_meta_value_key', $mydata, true) or
    update_post_meta($post_ID, '_my_meta_value_key', $mydata);
  // or a custom table (see Further Reading section below)
   save_image($mydata);
 
 }

}