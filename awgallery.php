<?php
/*
Plugin Name: AW Gallery
Plugin URI: http://www.agentiaweb.ro
Description: With this plugin you can create multiple galleries, all filed under "Gallery" page. Very easy to use, just install, and start adding galleries in your Gallery section in admin. Pictures are added in each gallery with the help of WordPress's Media Uploader.
Version: 1.0
Author: Agentia WEB
Author URI: http://www.agentiaweb.ro
License: GPL2
*/

/*  Copyright 2012 AGENTIA WEB (email : CATALIN@AGENTIAWEB.RO)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
class AW_GALLERY {
	private $default_options = array(
		'images_per_page'		=>	9,
		'img_size'				=>	array(100,100),
		'img_crop'				=>	true,
		'image_link_class'		=>	'thickbox',
		'galleries_per_page'	=>	9,
		'gallery_img_size'		=>	array(100,100),
		'gallery_img_crop'		=>	true,
		'permalink'				=>	'',
	);
	public $options = array();
	public function __construct(){
		register_activation_hook( __FILE__, array(&$this, 'on_activate') );
		register_deactivation_hook( __FILE__, array(&$this, 'on_deactivate') );
		add_shortcode('awgallery-show',array(&$this, 'show_gallery'));
		add_shortcode('awgallery-all',array(&$this, 'show_all_galleries'));
		add_action('init',array(&$this, 'install_awgallery'));
		add_filter('rewrite_rules_array', array(&$this, 'insert_rewrite_rules'));
		add_filter('query_vars', array(&$this, 'insert_query_vars'));
		add_action('wp_loaded', array(&$this, 'flush_rules'));
		add_action('admin_head',array(&$this, 'install_admin'));
		add_action('admin_menu', array(&$this, 'register_submenu_setttings'));
		add_action('wp_enqueue_scripts',array(&$this, 'do_enqueues'));
		add_action('save_post', array(&$this, 'on_save'));
		add_action('wp_head', array(&$this, 'add_current_page'));
		add_filter('upload_mimes', array(&$this, 'custom_mime_types'));
	}
	/*	Install AWGallery
	*/
	public function install_awgallery() {
		#---	Set options
		$this->options['images_per_page']		=	get_option('awgallery_images_per_page');
		$this->options['img_size']				=	get_option('awgallery_img_size');
		$this->options['img_crop']				=	get_option('awgallery_img_crop');
		$this->options['image_link_class']		=	get_option('awgallery_image_link_class');
		$this->options['galleries_per_page']	=	get_option('awgallery_galleries_per_page');
		$this->options['gallery_img_size']		=	get_option('awgallery_gallery_img_size');
		$this->options['gallery_img_crop']		=	get_option('awgallery_gallery_img_crop');
		$this->options['permalink'] 			=	get_permalink(get_option('awgallery_page_id'));
		#---	Add a new post type
		if(!post_type_exists('awgallery')) {
			$labels = array(
				'name'					=>	__('Gallery'),
				'singular_name'			=>	__('Gallery'),
				'add_new'				=>	__('Add gallery'),
				'add_new_item'			=>	__('Add gallery'),
				'edit_item'				=>	__('Update gallery'),
				'new_item'				=>	__('New gallery'),
				'view_item'				=>	__('View gallery'),
				'search_items'			=>	__('Search galleries'),
				'not_found'				=>	__('Nothing found'),
				'not_found_in_trash'	=>	__('Nothing found in Trash'),
				'parent_item_colon'		=>	''
			);
			$args = array(
				'labels'				=>	$labels,
				'public'				=>	true,
				'publicly_queryable'	=>	true,
				'show_ui'				=>	true,
				'rewrite'				=>	array('slug'=>'gallery'),
				'capability_type'		=>	'post',
				'hierarchical'			=>	false,
				'menu_position'			=>	101,
				'supports'				=>	array('title','editor','thumbnail')
			  ); 
			register_post_type( 'awgallery' , $args );
		}
		#---	Add custom sizes
		if ( function_exists( 'add_theme_support' ) ) {
			if(!current_theme_supports('post-thumbnails')) {
				add_theme_support( 'post-thumbnails' );
			}
		}
		if ( function_exists( 'add_image_size' ) ) { 
			add_image_size('awgallery-thumbnail', $this->options['img_size'][0], $this->options['img_size'][1], $this->options['img_crop'] );
			add_image_size('awgallery-gallery', $this->options['gallery_img_size'][0], $this->options['gallery_img_size'][1], $this->options['gallery_img_crop'] );
		}
	}
	/*	Install admin panel
	*/
	public function install_admin() {
	?>
		<style type="text/css" media="screen">
			#menu-posts-awgallery .wp-menu-image {
				background: url(<?php echo plugins_url('menu_icon.png', __FILE__) ?>) no-repeat 1px -26px !important;
			}
			#menu-posts-awgallery:hover .wp-menu-image, #menu-posts-awgallery.wp-has-current-submenu .wp-menu-image {
				background-position:1px 3px !important;
			}
		</style>
	<?php 
	} 
	/*	Set 'Gallery' page as current page when browsing a gallery
	*/
	public function add_current_page() {
		$menu_id = get_option('awgallery_page_id');
		global $wp_query;
		if($wp_query->query_vars['pagename']=="gallery" || $wp_query->query_vars['post_type']=="awgallery"):
	?>
    	<script type="text/javascript">
			jQuery(document).ready(function($){
				$('.page-item-'+<?php echo $menu_id;?>).addClass('current_page_item');
			});
		</script>
    <?php
		endif;
	}
	/*	Enqueue scripts and styles
	*/
	public function do_enqueues(){
		wp_enqueue_script( 'jquery' );
		$css_file = 'awgallery.css';
		if(file_exists(TEMPLATEPATH .'/'. $css_file)){
			$css_location = TEMPLATEPATH .'/'. $css_file;
		}elseif(file_exists(TEMPLATEPATH .'/css/'. $css_file)){
			$css_location = TEMPLATEPATH .'/css/'. $css_file;
		}else{
			$css_location = plugins_url('default-styles/'.$css_file,__FILE__);
		}
		wp_register_style( 'awgallery', $css_location);
    	wp_enqueue_style( 'awgallery' );
		wp_enqueue_style('thickbox');
		wp_enqueue_script('thickbox');
	}
	/*	Add gallery shortcodes to all posts from plugin's custom post types
	*/
	public function on_save($post_id){
		if (!wp_is_post_revision($post_id) && 'awgallery'==$_POST['post_type']) {
			remove_action('save_post', array(&$this,'on_save'));
			$_p['ID'] = $post_id;
			$_p['post_content'] = '[awgallery-show]';
			wp_update_post( $_p );
			add_action('save_post', 'set_private_categories');
		}
	}
	/*	Set plugin option
	*/
	public function set_option(){
		if(func_num_args()==1 && is_array(func_get_args(0))) {
			// array x=>y
			$array = reset(func_get_args(0));
		}elseif(func_num_args()==2){
			// $option => $value
			$args = func_get_args();
			$array = array($args[0]=>$args[1]);
		}
		$this->options = array_merge($this->options,$array);
	}
	/*	Reset plugin option to default value
	*/
	public function reset_option($option){
		unset($this->options[$option]);
		$this->options = array_merge($this->default_options,$this->options);
	}
	/*	Show all galleries
	*/
	public function show_all_galleries(){
		$page = (get_query_var('paged')) ? get_query_var('paged') : 1; 
		$args = array(
					'post_type'			=>	'awgallery',
					'posts_per_page'	=>	($this->options['galleries_per_page']>0) ? $this->options['galleries_per_page'] : $this->options['images_per_page'],
					'order'				=>	'desc'
				);
		$galleries = new WP_Query($args);
		if($galleries->have_posts()):
			echo '<ul class="awgallery">';
			while($galleries->have_posts()):
				$galleries->the_post();
				echo '<li>';
				echo '<a href="'.get_permalink().'">';
				if(has_post_thumbnail()){
					the_post_thumbnail('awgallery-gallery');
				}else{
					$args = array(
							'post_parent'		=>	$post->ID,
							'post_status'		=>	'inherit',
							'post_type' 		=>	'attachment',
							'posts_per_page'	=>	1,
							'orderby'			=>	'rand'
						);
					$attachments = new WP_Query($args);
					$image_attributes = wp_get_attachment_image_src( $attachments->posts[0]->ID, 'awgallery-gallery' );
					echo '<img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'">';
				}
				the_title();
				echo '</a>';
				echo '</li>';
			endwhile;
			echo '</ul>';
		endif;
		wp_reset_postdata();
	}
	/*	Show gallery
	*/
	public function show_gallery(){
		global $post, $wp_query, $wp_rewrite, $query_string;
		$paged = (get_query_var('showpage')) ? get_query_var('showpage') : 1; 
		$range = 2;
		$showitems = $this->options['images_per_page'];
		$args = array(
				'post_parent'		=>	$post->ID,
				'post_status'		=>	'inherit',
				'post_type' 		=>	'attachment',
				'paged'				=>	$paged,
				'posts_per_page'	=>	$this->options['images_per_page']
			);
		$attachments = new WP_Query($args);
		$pages = $attachments->max_num_pages;
		if ($attachments->have_posts()) {
			echo '<ul class="awgallery">';
			foreach($attachments->posts as $attachment) {
				$image_full_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' );
				$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'awgallery-thumbnail' );
				echo '<li>
						<a href="'.$image_full_attributes[0].'" title="'.$attachment->post_title.'" class="'.$this->options['image_link_class'].'">
							<img src="'.$image_attributes[0].'">
						</a>
					  </li>';
			}
			echo '</ul>';
		}
		$page_url = get_permalink($post->ID);
		echo "<div class='awpagination'>";
		if($paged > 2 ) echo "<a href='".$page_url."'>&laquo;</a>";

		for ($i=1; $i <= $pages; $i++)
		{
			if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
			{
				if(1 == $i)
					echo ($paged == $i) ? "<span class='current'>".$i."</span>":"<a href='".$page_url."' class='inactive' >".$i."</a>";
				else
					echo ($paged == $i) ? "<span class='current'>".$i."</span>":"<a href='".$page_url."/showpage/".$i."/' class='inactive' >".$i."</a>";
			}
		}

		if ($paged < $pages-1 ) echo "<a href='".$page_url."/showpage/".$pages."/'>&raquo;</a>";
		echo "</div>";
	}
	/*	Restrict mime types for plugin's custom post types
	*/
	public function custom_mime_types($mime_types) {
		if('awgallery' == get_post_type($_POST['post_id'])){
		$mime_types = array();
		$mime_types['jpg|jpeg|jpe'] = 'image/jpeg';
		$mime_types['png'] = 'image/png';
		return $mime_types;
		}
	}
	/*	Activate plugin
	*/
	public function on_activate(){
		global $wpdb;

		$the_page_title = __('Gallery');
		$the_page_name = __('gallery');
	
		delete_option("awgallery_page_title");
		add_option("awgallery_page_title", $the_page_title, '', 'yes');
		delete_option("awgallery_page_name");
		add_option("awgallery_page_name", $the_page_name, '', 'yes');
		delete_option("awgallery_page_id");
		add_option("awgallery_page_id", '0', '', 'yes');
		
		$the_page = get_page_by_title($the_page_title);
		if ( ! $the_page ) {
			$_p = array();
			$_p['post_title'] = $the_page_title;
			$_p['post_content'] = "[awgallery-all]";
			$_p['post_status'] = 'publish';
			$_p['post_type'] = 'page';
			$_p['comment_status'] = 'closed';
			$_p['ping_status'] = 'closed';
			$_p['post_category'] = array(1);
			$the_page_id = wp_insert_post($_p);
		}
		else {
			$the_page_id = $the_page->ID;
			$the_page->post_status = 'publish';
			$the_page_id = wp_update_post($the_page);
		}
		
		delete_option('awgallery_page_id');
		add_option('awgallery_page_id', $the_page_id);
		
		add_option('awgallery_images_per_page', 9);
		add_option('awgallery_img_size', array(100,100));
		add_option('awgallery_img_crop', true);
		add_option('awgallery_image_link_class', 'thickbox');
		add_option('awgallery_galleries_per_page', 9);
		add_option('awgallery_gallery_img_size', array(100,100));
		add_option('awgallery_gallery_img_crop', true);
	}
	/*	Deactivate plugin
	*/
	public function on_deactivate(){
		global $wpdb;
	
		$the_page_title = get_option( "awgallery_page_title" );
		$the_page_name = get_option( "awgallery_page_name" );
	
		$the_page_id = get_option( 'awgallery_page_id' );
		if( $the_page_id ) {
			wp_delete_post( $the_page_id );
		}
	
		delete_option("awgallery_page_title");
		delete_option("awgallery_page_name");
		delete_option("awgallery_page_id");
	}
	/*	Pretty debug
	*/
	public function preprint($s, $return=false) { 
		$x = "<pre style='width:960px;'>"; 
		$x .= print_r($s, 1); 
		$x .= "</pre>"; 
		if ($return) return $x; 
		else print $x; 
	} 
	/*	Flush rewrite rules if our rule is not registered
	*/
	public function flush_rules(){
		$rules = get_option( 'rewrite_rules' );
	
		if ( ! isset( $rules['gallery/(.?.+?)/showpage/?([0-9]{1,})/?$'] ) ) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
	}
	/*	Function for debug
	*/
	public function insert_rewrite_rules( $rules )
	{
		$newrules = array();
		$newrules['gallery/(.?.+?)/showpage/?([0-9]{1,})/?$'] = 'index.php?awgallery=$matches[1]&showpage=$matches[2]';
		return $newrules + $rules;
	}
	/*	Adding query vars
	*/
	public function insert_query_vars( $vars )
	{
		array_push($vars, 'showpage');
		return $vars;
	}
	/*	Add "settings" submenu
	*/
	public function register_submenu_setttings() {
		add_submenu_page('edit.php?post_type=awgallery','Settings','Settings','manage_options','awgallery-settings',array($this , 'admin_settings_page'));
	}
	/*	Include settings page for rendering
	*/
	public function admin_settings_page() {
		include 'admin/settings.php';
	
	}
}
$aw_gal = new AW_GALLERY();
?>