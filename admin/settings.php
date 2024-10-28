<?php
#---	Update options if form is submitted
$updated = false;
if($_POST['submit'] && wp_verify_nonce($_POST['awgallery_settings-nonce'],'awgallery_settings-update')):
	$_POST['img_size_crop'] = (true==$_POST['img_size_crop'])?1:0;
	$_POST['gallery_img_size_crop'] = (true==$_POST['gallery_img_size_crop'])?1:0;
	update_option('awgallery_images_per_page',$_POST['awgallery_settings-images_per_page']);
	update_option('awgallery_img_size',array($_POST['img_size_w'],$_POST['img_size_h']));
	update_option('awgallery_img_crop', $_POST['img_size_crop']);
	update_option('awgallery_image_link_class',$_POST['awgallery_settings-image_link_class']);
	update_option('awgallery_galleries_per_page',$_POST['awgallery_settings-galleries_per_page']);
	update_option('awgallery_gallery_img_size',array($_POST['gallery_img_size_w'],$_POST['gallery_img_size_h']));
	update_option('awgallery_gallery_img_crop',$_POST['gallery_img_size_crop']);
	$updated = true;
endif;
#---	Get options from database
$options['images_per_page']			=	get_option('awgallery_images_per_page');
$options['img_size']				=	get_option('awgallery_img_size');
$options['img_crop']				=	get_option('awgallery_img_crop');
$options['image_link_class']		=	get_option('awgallery_image_link_class');
$options['galleries_per_page']		=	get_option('awgallery_galleries_per_page');
$options['gallery_img_size']		=	get_option('awgallery_gallery_img_size');
$options['gallery_img_crop']		=	get_option('awgallery_gallery_img_crop');
$options['permalink'] 				=	get_permalink(get_option('awgallery_page_id'));
?>
<div class="wrap">
  <div id="icon-options-general" class="icon32"><br/>
  </div>
  <?php    echo "<h2>" . __( 'AWGallery Settings') . "</h2>"; ?>
  <br/>
  <?php
  if(true == $updated):
  	?>
    <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
    <?php
  endif;
  ?>
  <form name="awgallery_settings-form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <?php wp_nonce_field('awgallery_settings-update','awgallery_settings-nonce'); ?>
    <h3>
      <?php _e("Images display: " ); ?>
    </h3>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><?php _e("Images per page: " ); ?></th>
          <td><input type="text" name="awgallery_settings-images_per_page" value="<?php echo $options['images_per_page']; ?>" size="20">
            <p class="description">
              <?php _e("Default:"); ?>
              9</p></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Image size"); ?></th>
          <td><fieldset>
              <label for="img_size_w">
                <?php _e("Width"); ?>
              </label>
              <input name="img_size_w" type="number" step="1" min="0" id="img_size_w" value="<?php echo $options['img_size'][0]; ?>" class="small-text">
              <label for="img_size_h">
                <?php _e("Height"); ?>
              </label>
              <input name="img_size_h" type="number" step="1" min="0" id="img_size_h" value="<?php echo $options['img_size'][1]; ?>" class="small-text">
              <input name="img_size_crop" type="checkbox" id="img_size_crop" value="1" <?php echo (true == $options['img_crop']) ? 'checked="checked"' : '';?>>
              <label for="img_size_crop">
                <?php _e("Crop"); ?>
              </label>
            </fieldset></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Images link class: " ); ?></th>
          <td><input type="text" name="awgallery_settings-image_link_class" value="<?php echo $options['image_link_class']; ?>" size="20">
            <p class="description">
              <?php _e("Default:"); ?>
              thickbox</p></td>
        </tr>
      </tbody>
    </table>
    <br/>
    <h3>
      <?php _e("Gallery display: " ); ?>
    </h3>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><?php _e("Galleries per page: " ); ?></th>
          <td><input type="text" name="awgallery_settings-galleries_per_page" value="<?php echo $options['galleries_per_page']; ?>" size="20">
            <p class="description">
              <?php _e("Leave blank for same number as images per page"); ?>
            </p></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Gallery cover size"); ?></th>
          <td><fieldset>
              <label for="gallery_img_size_w">
                <?php _e("Width"); ?>
              </label>
              <input name="gallery_img_size_w" type="number" step="1" min="0" id="gallery_img_size_w" value="<?php echo $options['gallery_img_size'][0]; ?>" class="small-text">
              <label for="gallery_img_size_h">
                <?php _e("Height"); ?>
              </label>
              <input name="gallery_img_size_h" type="number" step="1" min="0" id="gallery_img_size_h" value="<?php echo $options['gallery_img_size'][1]; ?>" class="small-text">
              <span>
              <input name="gallery_img_size_crop" type="checkbox" id="gallery_img_size_crop" value="1" <?php echo (true == $options['gallery_img_crop']) ? 'checked="checked"' : '';?>>
              <label for="gallery_img_size_crop">
                <?php _e("Crop"); ?>
              </label>
              </span>
            </fieldset></td>
        </tr>
      </tbody>
    </table>
    <br/>
    <p><strong><?php _e("Note: if you change any image size, you must rebuild thumbnails."); ?></strong></p>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes">
    </p>
  </form>
</div>
