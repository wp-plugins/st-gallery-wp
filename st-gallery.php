<?php
/*
Plugin: ST Galleria
Plugin URL: http://beautiful-templates.com
Description: Create gallery from your image post with Galleria library & Skitter.
Version: 1.0.1
Author: Beautiful Templates
Author URI: http://beautiful-templates.com
License:  GPL2
*/
ob_start(); 
include 'classes/st_file.php';
class StGallery{

	public $options;

	function __construct(){
		add_shortcode( 'st-gallery',  array( $this, 'st_gallery_shortcode' ) );
		add_action( 'wp_ajax_remove_gallery', array( $this, 'remove_gallery_callback' ) );
		$this->options = get_option('st_gallery_wp');
	}
	

	/*
	 * relative URLs images
	 */
	public function relativeURL($url){
		 return str_replace(get_home_url(), '', $url);
	}
	
	/**
	 * Message
	 */
	 public function st_message(){
	 	if (isset($_GET['message'])){
			$mid = $_GET['message'];
			switch ($mid) {
				case '1':
					$message = __('Gallery added.' , 'st-gallery');
					break;
				case '2':
					$message = __('Gallery updated.' , 'st-gallery');
					break;
				case '3':
					$message = __('Gallery Removed.' , 'st-gallery');
					break;
				case '4':
					$message = __('Gallery Imported.' , 'st-gallery');
					break;
				case '5':
					$message = __('Please select import file!' , 'st-gallery');
					break;
				case '6':
					$message = __('Sample Gallery Imported.' , 'st-gallery');
					break;
				default:
					$message = __('ST Gallery WP' , 'st-gallery');
					break;
			}
			echo '<div id="message" class="updated below-h2">';
			echo '<p>'.$message.'</p>';
			echo '</div>';
		}
	 }
	/**
	 * Get first images in post
	 */
	public function st_get_first_img(){
		global $post;
		$first_img = '';
		ob_start();
		ob_end_clean();
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		$first_img = $matches[1][0];		 
		return $first_img;
	}
	
	/*
	 * Import sample gallery 
	 */
	 public function importSampleGallery(){
	 	$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];
		$plugin_dir = plugin_dir_path( __FILE__ ).'sample_gallery_data';
		$gallery_zip = 'gallery.zip';
		opendir($upload_path);
		opendir($plugin_dir);
		$ST_File = new ST_File;
		
		if (file_exists($plugin_dir.'/'.$gallery_zip)) {
			$rs = $ST_File->st_unzip($plugin_dir.'/'.$gallery_zip, $upload_path , false);
			if ( $rs != FALSE){
				$unzipFile = $upload_path.'/'.$rs.'/uploads';
				$encode_options = file_get_contents($upload_path.'/'.$rs.'/import.json');
				$ST_File -> st_copy_all($unzipFile,$upload_path);
				$ST_File->st_unlink($upload_path.'/'.$rs);
			}else{
				echo 'Not Import';
			}
	    }
		
	 	$data_import = json_decode($encode_options, true);
		if (empty($this->options)){
			$new_data_import = $data_import;
		}else{
			$old_options_value = $this->options;
			foreach ($old_options_value as $old_id => $old_gallery) { 
				foreach ($data_import as $import_id => $import_gallery) {
					if ($old_id==$import_id){ 
						unset($data_import[$old_id]);
						$new_id = uniqid(); 
						$data_import[$new_id] = $import_gallery;  
					}
				}
			}
			$new_data_import = array_merge($old_options_value, $data_import);
		}
		
		update_option('st_gallery_wp', $new_data_import);
		echo '<meta http-equiv="refresh" content="0; URL=admin.php?page=st_gallery&message=6">';
	 }
	 
	 
	/*
	 * Show all Gallery
	 */
	public function allGallery(){
		if (isset($_GET['action']) && ($_GET['action'] == 'import')){
			$this -> importSampleGallery();
		}
		?>
		<div class="wrap st_gallery_wp">
			<h2><?php _e('ST Gallery WP', 'st-gallery'); ?> 
				<a href="?page=st_gallery_add" class="add-new-h2"><div class="dashicons dashicons-plus"></div><?php _e('Add New', 'st-gallery'); ?></a> 
				<a href="?page=st_gallery&action=import" class="add-new-h2"><div class="dashicons dashicons-update"></div><?php _e('Import sample gallery', 'st-gallery'); ?></a>
			</h2> 
			<?php
				$this->st_message();
			?>
			<div class="st-left">
				<div class="st-allGallery">
					<div class="st-row listTitle">
						<div class="col id"><?php _e('ID', 'st-gallery'); ?></div>
						<div class="col name"><?php _e('Name', 'st-gallery'); ?></div>
						<div class="col shortcode"><?php _e('Shortcode', 'st-gallery'); ?></div>
						<div class="col actions"><?php _e('Actions', 'st-gallery'); ?></div>
					</div>
					<?php 
					if ($this->options){
						foreach ($this->options as $key => $value) { ?>
					<div class="st-row" id="<?=$key ?>">
						<div class="col id"><?=$key ?></div>
						<div class="col name"><a href="?page=st_gallery&action=edit&id=<?=$key ?>"><?=$value['name'] ?></a></div>
						<div class="col shortcode"><input size="30" class="shortcode" type="text" value='[st-gallery id="<?=$key ?>"]' onmouseover='this.select()'></div>
						<div class="col actions">
							<span class="action edit"><a href="?page=st_gallery&action=edit&id=<?=$key ?>"><?php _e('Edit', 'st-gallery'); ?></a></span>
							<span class="action remove" id="<?=$key ?>"><?php _e('Remove', 'st-gallery'); ?></span>
						</div>
					</div>
					<?php }
					} else{
						?>
							<div class="st_no_gallery"><?php _e('No gallery! Please add new or import the gallery!', 'st-gallery'); ?></div>
						<?php
					}
					?>
				</div>
				<div id="remove-dialog-confirm" title="<?php _e('Remove gallery?') ?>">
				  <p><?php _e('The gallery will be permanently deleted and cannot be recovered. Are you sure?') ?></p>
				</div>
				
				<div class="import_export">
					<div class="st-box">
						<h3 class="box-title"><div class="dashicons dashicons-admin-plugins"></div> <?php _e('Import & Export Gallery', 'st-gallery'); ?></h3>
						<div class="box-content">
							<?php $this->import_option_page(); ?>
							<?php $this->export_option_page(); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="st-right">
				<?php $this -> StCopyright(); ?>
			</div>
			
		</div>
	<?php
	}

	/*
	 * Import Gallery
	 */
	 public function import_option_page() { 
		if (isset($_FILES['import']) && check_admin_referer('import')) {
			if ($_FILES['import']['error'] > 0){
				echo '<meta http-equiv="refresh" content="0; URL=admin.php?page=st_gallery&message=5">';		
			}else {
				$file_name = $_FILES['import']['name'];
				$file_ext = strtolower(end(explode(".", $file_name)));
				if (($file_ext == "zip")) {
					
					$upload_dir = wp_upload_dir();
					$upload_path = $upload_dir['basedir'];
					opendir($upload_path);
					$ST_File = new ST_File;
					
					if (file_exists($upload_path.'/'.$file_name)) {
						$rs = $ST_File->st_unzip($upload_path.'/'.$file_name, $upload_path, true);
						if ( $rs != FALSE){
							$unzipFile = $upload_path.'/'.$rs.'/uploads';
							$encode_options = file_get_contents($upload_path.'/'.$rs.'/import.json');
							if (file_exists($unzipFile)){
								$ST_File -> st_copy_all($unzipFile,$upload_path);
							}
							$ST_File -> st_unlink($upload_path.'/'.$rs);
						}else{
							echo 'Not Unzip';
						}
				    }else {
				    	move_uploaded_file($_FILES['import']['tmp_name'], $upload_path.'/'.$file_name);
						$rs = $ST_File->st_unzip($upload_path.'/'.$file_name, $upload_path, true);
						if ( $rs != FALSE){
							$unzipFile = $upload_path.'/'.$rs.'/uploads';
							$encode_options = file_get_contents($upload_path.'/'.$rs.'/import.json');
							if (file_exists($unzipFile)){
								$ST_File -> st_copy_all($unzipFile,$upload_path);
							}
							$ST_File->st_unlink($upload_path.'/'.$rs);
						}else{
							echo 'Not Unzip';
						}
				    }

					$data_import = json_decode($encode_options, true);
					if (empty($this->options)){
						$new_data_import = $data_import;
					}else{
						$old_options_value = $this->options;
						foreach ($old_options_value as $old_id => $old_gallery) { 
							foreach ($data_import as $import_id => $import_gallery) {
								if ($old_id==$import_id){ 
									unset($data_import[$old_id]);
									$new_id = uniqid(); 
									$data_import[$new_id] = $import_gallery;  
								}
							}
						}
						$new_data_import = array_merge($old_options_value,$data_import);
					}
					
					update_option('st_gallery_wp', $new_data_import);
					echo '<meta http-equiv="refresh" content="0; URL=admin.php?page=st_gallery&message=4">';
				}	
				else 
					echo "<div class='error'><p>".__('Invalid file or file size too big.' , 'st-gallery')."</p></div>";
			}
		}
			?>
	        <form method='post' enctype='multipart/form-data'>
	        	<div class="st-row row-import">
	        		<div class="col col-2"><label><?php _e('Import Gallery', 'st-gallery'); ?></label></div>
	        		<div class="col col-6">
	        			<?php wp_nonce_field('import'); ?>
	            		<input type='file' name='import' />
	        		</div>
	        		<div class="col col-2">
	        			<input type='submit' id="import" name='submit' value='<?php _e('Import', 'st-gallery'); ?>'/>
			        </div>
	        	</div>
	        </form>
	<?php
	} 



	/*
	 * Export Gallery
	 */
	public function export_option_page() {
		if (!isset($_POST['export'])) { ?>
	        <form method='post'>
	        	<div class="st-row">
	        		<div class="col col-2"><label for="select_gallery"><?php _e('Export Gallery', 'st-gallery'); ?></label></div>
	        		<div class="col col-6">
	        			<select id="select_gallery" name="select_gallery">
							<option value="all" selected="selected" ><?php _e('All Gallery', 'st-gallery'); ?></option>
							<?php
							foreach ($this->options as $key => $value) { 
								echo '<option value="'.$key.'">'.$value['name'].'</option>';
							}
							?>
						</select>
	        		</div>
	        		<div class="col col-2">
	        			<?php wp_nonce_field('export'); ?>
			        	<input type='submit' id="export" name='export' value='<?php _e('Export', 'st-gallery'); ?>'/>
			        </div>
	        	</div>
	        </form>
		<?php 
	  	} elseif (check_admin_referer('export')) {
	  		$upload_dir = wp_upload_dir();
			$upload_path = $upload_dir['basedir'];
			opendir($upload_path);
			
			$data = $this->options; 
			$select_gallery = $_POST['select_gallery'];
			$copy = "";
			if ($select_gallery!="all"){
				foreach ($data as $key => $value) {
					if ($key==$select_gallery){
						$data_export[$key] = $value;
						$this->st_create_json_file($upload_path, json_encode($data_export), $key);
						if ($value['images']!=null){
							foreach ($value['images'] as $img) {
								$copy = $this->st_gallery_copy_images($upload_path, $key, $img['url']);
							} 
						}
					}
				 }
			}else{
				$this->st_create_json_file($upload_path ,json_encode($data), $select_gallery );
				foreach ($data as $key => $value) {
					if ($value['images']!=null){
						foreach ($value['images'] as $img) {
							$copy = $this->st_gallery_copy_images($upload_path , $select_gallery , $img['url']);
						}
					} 
				}
			}
			
			$the_folder = $upload_path.'/'.$select_gallery;
			$filename = 'ST Gallery WP Export '.date('Y_m_d').' at '.date('H_i_s').'.zip';
			$zip_file_name = $the_folder.'.zip';  
			$download_file = true;
			$ST_File = new ST_File;
			$res = $ST_File->open($zip_file_name, ZipArchive::CREATE);
			if($res === TRUE) {
				$ST_File->addDir($the_folder, basename($the_folder));
				$ST_File->close();
			}else { echo __('Could not create a zip archive' , 'st-gallery');}
			
			if($download_file){
				ob_get_clean();
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private", false);
				header("Content-Type: application/zip");
				header("Content-Disposition: attachment; filename=" . basename($filename) . ";" );
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: " . filesize($zip_file_name));
				readfile($zip_file_name);
				unlink($zip_file_name);
			}
			$ST_File->st_unlink($the_folder);
			exit();
		}
	}

	
	/**
	 * Create json file
	 */
	 public function st_create_json_file($upload_path, $json_data, $gallery){
	 	$full_path = $upload_path.'/'.$gallery;
		if (!file_exists( $full_path )){ 
			if (mkdir( $full_path ) == true ){
				$this->st_create_json_file( $upload_path, $json_data , $gallery );
			}else{
				return __('There was a problem while creating '.$full_path , 'st-gallery');
			}
		}else{
			$filename = $full_path.'/import.json';
			if (!file_exists($filename)){
				$file = fopen($filename, "w") or die("Unable to open file!");
				fwrite($file, $json_data);
				fclose($file);
				$rs = 1;
			}
			$rs = 1;
		}
		return $rs;
	}


	/**
	 * Copy images from uploads -> uploads/{$id_gallery}/uploads
	 * @param $gallery: string, gallery id export
	 * @param $img: string, images url in gallery.
	 * @return string, if error
	 */
	public function st_gallery_copy_images($upload_path, $gallery , $img ){
		$full_path 		= 	$upload_path.'/'.$gallery.'/uploads';
		$url 			= 	explode('/' , $img);
		$year			= 	$full_path .'/'. $url[3];
		$month			=	$year .'/'. $url[4];
		$new_url		= 	$month .'/'. $url[5];
		$rs				=	'';
		

		if(!file_exists( $full_path )){
			if(mkdir( $full_path ) == true ){
				$this->st_gallery_copy_images( $upload_path, $gallery , $img );
			}else{
				return __('There was a problem while creating '.$full_path , 'st-gallery');
			}
		}else{
			if (!file_exists( $year )){
				if (mkdir( $year ) == true ){
					$this->st_gallery_copy_images( $upload_path, $gallery , $img );
				}else{
					return __('There was a problem while creating '.$year , 'st-gallery');
				}
			}else{
				if (!file_exists( $month )){
					if (mkdir( $month ) == true ){
						$this->st_gallery_copy_images( $upload_path, $gallery , $img );
					}else{
						return __('There was a problem while creating '.$month , 'st-gallery');
					}
				}else{
					$old_url = substr(get_home_path(),0,-1).$img;
					if (!file_exists( $new_url )){
						if ( copy( $old_url, $new_url ) == 1 ){
							$this->st_gallery_copy_images( $upload_path, $gallery , $img );
							return 1;
						}else{
							return __('There was a problem while copy '.$old_url , 'st-gallery');
						}
					}
				}
			} 
		}
	}


	/*
	 * Remove gallery
	 */
	public function remove_gallery_callback() {
		$id = $_POST['id'];
		unset($this->options[$id]);
		update_option('st_gallery_wp',$this->options);
		echo $id;
		die();
	}
	
	 
	/*
	 * Create shortcode show gallery
	 */
	public function st_gallery_shortcode( $input ) {
		ob_start(); 
		extract( shortcode_atts( array( 'id' => __('Please enter gallery ID' , 'st-gallery') ), $input, 'id' ) );
		foreach ($this->options as $key => $gallery) {
			if ($key==$id){
				switch ($gallery['settings']['style']) {
					case 'gallery': $this -> st_render_shortcode_gallery($id); break;
					case 'skitter': $this -> st_render_shortcode_skitter($id); break;
					default: 		$this -> st_render_shortcode_gallery($id); break;
				}
			}
		}
	return ob_get_clean();
	}
	
	
	/**
	 * render shortcode skitter
	 */
	public function st_render_shortcode_skitter($id){
		$gallery = $this->options[$id];
		$newid = uniqid();
		?>
		<div class="st-skitter <?php echo $this -> valString($gallery['skitter']['theme'], 'default') ?>" id="<?= $newid ?>">
			<?php if ( is_super_admin()) { ?>
				<div class="st-gallery-edit"><a href="<?= get_home_url() ?>/wp-admin/admin.php?page=st_gallery&action=edit&id=<?= $id ?>" class="edit-link"><?php _e('Edit Gallery', 'st-gallery'); ?></a></div>
			<?php }	?>
			<div class="box_skitter <?= $newid ?>" style="width: <?php echo $gallery['settings']['width'].$gallery['settings']['width_end'] ?>; height: <?php echo $gallery['settings']['height']?>px;">
				<ul>
				<?php 
				if (isset($gallery['settings']['source']) && esc_attr($gallery['settings']['source']) == 'Library'){
					foreach ($gallery['images'] as $i => $images) { ?>
						<li class="image" id="img-<?=$i ?>">
							<a href="#">
								<img src="<?php echo ( (isset($images['url_2']) && $images['url_2']!="") ? $images['url_2'] : get_home_url().$images['url']); ?>">
							</a>
							<div class="label_text"><p><?php echo $images['title']; ?></p></div>
						</li>
					<?php
					} 
				}else if (isset($gallery['settings']['source']) && esc_attr($gallery['settings']['source']) == 'Post'){
						global $post;
	
						$post_category = $gallery['post']['post_category'];
						$query = new WP_Query( array(
										'category__in'		=>	$post_category, 
										'posts_per_page'	=>	-1,
										'orderby' 			=> $gallery['post']['order_by'],
										'posts_per_page' 	=> $gallery['post']['limit'],
										) );
						
						if ( $query->have_posts() ) {
							$img_id = 1;
							while ( $query->have_posts() ) {
								$query->the_post(); 
								if ( (strlen(wp_get_attachment_url( get_post_thumbnail_id($post->ID) ) ) > 0) || ( ($this->st_get_first_img()!='') && ($gallery['post']['first_img']=='true') ) ){
								 ?>
								<li class="image img-<?=$img_id ?>" >
									<a href="#">
										<img src="<?php echo (strlen(wp_get_attachment_url( get_post_thumbnail_id($post->ID) )) > 0) ? wp_get_attachment_url(get_post_thumbnail_id($post->ID)) : $this->st_get_first_img(); ?>">
									</a>
									<div class="label_text"><p><?php echo get_the_title(); ?></p></div>
								</li> 
						<?php	
							$img_id++;	}	
							}
						}
						wp_reset_postdata();
					}
				?>
				</ul>
			</div>
		</div>
			<script type="text/javascript" language="javascript">
			(function($){
				$(document).ready(function() {
					$('.<?= $newid ?>').skitter({
						auto_play: 				<?php echo $this -> valBoolean($gallery['skitter']['auto_play']); ?>, 
						stop_over: 				<?php echo $this -> valBoolean($gallery['skitter']['stop_over']); ?>, 
						interval: 				<?php echo $this -> valInt($gallery['skitter']['interval']); ?>,
						show_randomly: 			<?php echo $this -> valBoolean($gallery['skitter']['show_randomly']); ?>,
						controls: 				<?php echo $this -> valBoolean($gallery['skitter']['controls']); ?>,
						controls_position: 		'<?php echo $this -> valString($gallery['skitter']['controls_position'], 'center') ?>',
						progressbar:			<?php echo $this -> valBoolean($gallery['skitter']['progressbar']); ?>, 
						label: 					<?php echo $this -> valBoolean($gallery['skitter']['label']); ?>, 
						labelAnimation: 		'<?php echo $this -> valString($gallery['skitter']['labelAnimation'], 'slideUp') ?>',
						
						<?php
						switch ($gallery['skitter']['navigation']) {
							case 'thumbs': ?>
								thumbs: true,
								<?php
								break;
							case 'numbers':?>
								numbers: true,
								<?php
								break;
							case 'dots':?>
								dots: true,
								preview: <?php echo $this -> valBoolean($gallery['skitter']['preview']) ?>,
								<?php
								break;
							
							default:
								
								break;
						}
						?>
						numbers_align: 			'<?php echo $this -> valString($gallery['skitter']['numbers_align'], 'center') ?>', 
						navigation: 			<?php echo $this -> valBoolean($gallery['skitter']['next_prev']); ?>,
						enable_navigation_keys: <?php echo $this -> valBoolean($gallery['skitter']['enable_navigation_keys']); ?>, 
						hideTools: 				<?php echo $this -> valBoolean($gallery['skitter']['hideTools']); ?>,
						focus: 					<?php echo $this -> valBoolean($gallery['skitter']['focus']); ?>,
						focus_position:  		'<?php echo $this -> valString($gallery['skitter']['focus_position'], 'center') ?>', 
						fullscreen: 			<?php echo $this -> valBoolean($gallery['skitter']['fullscreen']); ?>,
						animation: 				'<?php echo $this -> valString($gallery['skitter']['animation'], 'randomSmart') ?>',
						theme: 					'<?php echo $this -> valString($gallery['skitter']['theme'], 'default') ?>', 
					});
				});
			})(jQuery);
			</script>
		<?php
		echo $this->right();
	}
	
	public function valBoolean($value){
		return (isset($value) ? $value : 'false'); 
	}
	public function valInt($value){
		return (isset($value) ? abs(intval($value)) : 0);
	}
	public function valString($value, $default){
		return (isset($value) ? $value : $default);
	}

	/**
	 * Render shortcode gallery
	 */
	public function st_render_shortcode_gallery($id){ 
		$gallery = $this->options[$id];
		$newid = uniqid();
		?>
		<div class="st_gallery_wp st-gallery-wrapper <?=$gallery['gallery']['theme']?>" style="max-width: <?=$gallery['settings']['width'].$gallery['settings']['width_end'] ?>; ">
				<?php if ( is_super_admin()) { ?>
					<div class="st-gallery-edit"><a href="<?= get_home_url() ?>/wp-admin/admin.php?page=st_gallery&action=edit&id=<?= $id ?>" class="edit-link"><?php _e('Edit Gallery', 'st-gallery'); ?></a></div>
				<?php }	?>
					<div id="<?=$newid?>" class="st-gallery-main <?=$gallery['gallery']['theme']?>" style="max-width: <?=$gallery['settings']['width'].$gallery['settings']['width_end'] ?>; max-height: <?=$gallery['settings']['height'] ?>px;">
				<?php 
					if (isset($gallery['settings']['source']) && esc_attr($gallery['settings']['source']) == 'Library'){
						foreach ($gallery['images'] as $i => $images) { ?>
							<div class="image" id="img-<?=$i ?>">
								<img src="
								<?php
									if (isset($images['url_2'])&&$images['url_2']!=""){
										echo $images['url_2'];
									}else{
										echo get_home_url().$images['url'];
									}
								?>" 
								<?php 
									if (isset($gallery['gallery']['show_title_image']) && esc_attr($gallery['gallery']['show_title_image']) == 'true' && $images['title'] != "") {
										echo 'data-title="' . $images['title'] . '"';
									}
									if (isset($gallery['gallery']['show_caption_image']) && esc_attr($gallery['gallery']['show_caption_image']) == 'true' && $images['caption'] != "") {
										echo ' data-description="' . $images['caption'] . '"';
									}
								?>
								>
							</div>
						<?php
						} 
					}else if (isset($gallery['settings']['source']) && esc_attr($gallery['settings']['source']) == 'Post'){
						global $post;
	
						$post_category = $gallery['post']['post_category'];
						$query = new WP_Query( array(
										'category__in'		=>	$post_category, 
										'posts_per_page'	=>	-1,
										'orderby' 			=> $gallery['post']['order_by'],
										'posts_per_page' 	=> $gallery['post']['limit'],
										) );
						
						if ( $query->have_posts() ) {
							$img_id = 1;
							while ( $query->have_posts() ) {
								$query->the_post(); 
								if ( (strlen(wp_get_attachment_url( get_post_thumbnail_id($post->ID) ) ) > 0) || ( ($this->st_get_first_img()!='') && ($gallery['post']['first_img']=='true') ) ){
								 ?>
								<div class="image" id="img-<?=$img_id ?>">
									<img src="<?php
									if (strlen(wp_get_attachment_url( get_post_thumbnail_id($post->ID) )) > 0){
										echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); 
									}else{
										echo $this->st_get_first_img();
									}
									?>"
									<?php 
										if (isset($gallery['gallery']['show_title_image']) && esc_attr($gallery['gallery']['show_title_image']) == 'true' && get_the_title() != "") {
											echo 'data-title="' . get_the_title() . '"';
										}
										if (isset($gallery['gallery']['show_caption_image']) && esc_attr($gallery['gallery']['show_caption_image']) == 'true' && get_the_content() != "") {
											echo ' data-description="' . $this->st_replace(get_the_content('')). '"';
										}
									?>
									>
								</div> 
						<?php	
							$img_id++;	}	
							}
						}
						wp_reset_postdata();
					}
					?>
					
					</div>
			<?php 
			if (isset($gallery['gallery']['show_control']) && esc_attr($gallery['gallery']['show_control']) == 'true'){ ?>
				<div id="st-gallery-control" style="max-width: <?=$gallery['settings']['width'].$gallery['settings']['width_end'] ?>;">
					<ul class="st-control-text">
						<li><a class="<?=$newid?> action" href="#"><?php _e('Play', 'st-gallery'); ?></a></li>
						<li><a class="<?=$newid?> full" href="#"><?php _e('Fullscreen', 'st-gallery'); ?></a></li>
					</ul>
				</div>
			<?php 
			} 
			?>
	</div> 
	<script type="text/javascript">
		(function($){
			<?php
			if ($gallery['gallery']['image_delay']){
				if ($gallery['gallery']['image_delay']==0){ ?>
					$('.<?=$newid?>.action').html('Play');
		<?php	}else{ ?>
					$('.<?=$newid?>.action').addClass('active').html('Pause');
		<?php	}
			} ?>
			$(document).ready(function(){
					Galleria.run('#<?=$newid?>.<?php echo $this -> valString($gallery['gallery']['theme'], 'classic'); ?>',{
						theme: 		'<?php echo $this -> valString($gallery['gallery']['theme'], 'classic'); ?>',
						autoplay: 	<?php echo $this -> valInt($gallery['gallery']['image_delay']); ?>,
						clicknext: 	<?php echo $this -> valBoolean($gallery['gallery']['click_to_next']); ?>, 
						dataConfig: function(img) {
							return {
								title : $(img).attr('data-title'),
								description : $(img).attr('data-description'),
							};
						}, 
						extend: function() {
							var gallery = this;
							$('.<?=$newid?>.action').click(function() {
								event.preventDefault();
								
								if ($(this).hasClass('active')){
									gallery.pause();
									$(this).removeClass('active').html('Play');
								}else{
									gallery.play();
									$(this).addClass('active').html('Pause');
								}
								
							});
							$('.<?=$newid?>.full').click(function() {
								event.preventDefault();
								gallery.enterFullscreen();
							});
						},
						showCounter: 		<?php echo $this -> valBoolean($gallery['gallery']['show_counter']) ?>,
						showImagenav: 		<?php echo $this -> valBoolean($gallery['gallery']['show_prev_next']) ?>,
						imageCrop: 			<?php echo $this -> valBoolean($gallery['gallery']['image_crop']) ?>,
						thumbnails: 		<?php echo $this -> valBoolean($gallery['gallery']['showThumb']) ?>,
						thumbCrop: 			<?php echo $this -> valBoolean($gallery['gallery']['thumb_crop']) ?>,
						transition: 		'<?php echo $this -> valString($gallery['gallery']['transition'], 'fadeslide'); ?>',
						transitionSpeed: 	<?php echo $this -> valInt($gallery['gallery']['transition_speed']); ?>,
						lightbox: 			<?php echo $this -> valBoolean($gallery['gallery']['lightbox']) ?>,
						imagePan: 			<?php echo $this -> valBoolean($gallery['gallery']['imagePan']) ?>,
						responsive: true,
						height:				<?php
												if (($this -> valBoolean($gallery['gallery']['responsive']))=='true'){
													echo '0.5';
												}else{
													echo $this -> valInt($gallery['settings']['height']);
												}
						 					?>,
						 					
				});
			});
		})(jQuery);
	</script> <?php
	echo $this->right();
	}

	/*
	 * Update gallery
	 */
	public function showDetails(){ ?>
		<div class="wrap st_gallery_wp">
	   	<h2><?php _e('Edit Gallery', 'st-gallery'); ?>
	   		<a href="?page=st_gallery" class="add-new-h2"><div class="dashicons dashicons-arrow-left-alt"></div><?php _e('Back to the list', 'st-gallery'); ?></a>
	   		<a href="?page=st_gallery_add" class="add-new-h2"><div class="dashicons dashicons-plus"></div><?php _e('Add New', 'st-gallery'); ?></a>
	   	</h2> 
	   	<?php
			$this->st_message();
		?>
	  	<form method="post" action="" name="stForm" id="stForm">
	  		<?php 
	  		settings_fields('st_option_group'); 
			$id = trim($_GET['id']);
			foreach ($this->options as $key => $gallery) {
				if ($key==$id){ 
					?>
					
			<div class="st-left">
				<div class="st-box name">
					<input name="id" type="hidden" value="<?=$id; ?>">
		  			<input name="name" type="text" id="name" value="<?php echo (isset($gallery['name']) ? $gallery['name'] : '') ?>" class="name" placeholder="<?php _e('Enter name here', 'st-gallery'); ?>" > 
	  			</div>
	  			<div id="tabs-container">
				    <ul class="tabs-menu">
				        <li class="current"><a href="#tab-1"><?php _e('Library Source', 'st-gallery'); ?></a></li>
				        <li><a href="#tab-2"><?php _e('Post Source', 'st-gallery'); ?></a></li>
				    </ul>
				    <div class="tab">
				        <div id="tab-1" class="tab-content">
				        	<div id="add-images">
								<?php wp_enqueue_media(); ?>
								<input type="button" class="button st-button st-upload" id="st-upload" value="<?php _e('Go Library', 'st-gallery'); ?>" />
			  				</div>
			  				<div id="appendImages"><!-- images -->
			  					<?php
								if ($gallery['images']){
									foreach ($gallery['images'] as $i => $images) { ?>
										<div class="col col-4" id="item-<?= $i ?>">
											<div class="item">
												
												<div class="image">
													<input class="hiddenUrl" type="text" name="image[<?= $i ?>][url]" value="<?= $images['url'] ?>" />
													<?php if ($images['url_2']){
														echo '<img src="' .$images['url_2'] . '" >';
													} else {
														echo '<img src="' . get_home_url().$images['url'] . '" >';
													} ?>
												</div>
												<div class="actions">
													<div class="action edit" id="<?= $i ?>"><div class="dashicons dashicons-edit"></div> Edit</div>
													<div class="action st-remove" id="<?= $i ?>"><div class="dashicons dashicons-trash"></div>Delete</div>
												</div>
												<div class="note">
													<div class="note-content">
														<div class="dashicons dashicons-sort"></div> <?php _e('Drag & drop to sort', 'st-gallery') ?>
													</div>
												</div>
												<div class="info" id="info-<?= $i ?>">
													<label for="title"><?php _e('Title: ', 'st-gallery') ?></label><input type="text" name="image[<?= $i ?>][title]" value="<?= $images['title'] ?>" />
													<label for="caption"><?php _e('Caption: ', 'st-gallery') ?></label><textarea rows="3" name="image[<?= $i ?>][caption]"><?= $images['caption'] ?></textarea>
													<label for="url"><?php _e('Image URL: ', 'st-gallery') ?></label><input type="url" name="image[<?= $i ?>][url_2]" value="<?= $images['url_2'] ?>" />
												</div>
												
											</div>
											
										</div>
								<?php
									}
								}?>
			  				</div>
				        </div>
				        <div id="tab-2" class="tab-content">
				        	<div class="st-row">
				        		<div class="left">
				        			<label><?php _e('Select Category: ', 'st-gallery'); ?></label>
				        		</div>
				        		<div class="right">
						        	<div class="select_category">
						        		<?php wp_category_checklist( 0, 0, $gallery['post']['post_category'] ,false, null, false); ?> 	
						        	</div>
				        		</div>
				        	</div>
				        	<?php
				        		switch ($gallery['post']['order_by']) {
									case 'date': 			$date_selected 				= 'selected="selected"'; break;
									case 'modified': 		$modified_selected 			= 'selected="selected"'; break;
									case 'rand': 			$rand_selected 				= 'selected="selected"'; break;
									case 'title': 			$title_selected				= 'selected="selected"'; break;
									case 'comment_count': 	$comment_count_selected 	= 'selected="selected"'; break;
									default: 				$date_selected 				= 'selected="selected"'; break;
								}
				        		$order_by = array(
									'date' 			=> array(
										'select' 	=> $date_selected,
										'name' 		=> 'Published Date',
									),
									'modified' 		=> array(
										'select' 	=> $modified_selected,
										'name' 		=> 'Modified Date',
									),
									'rand' 			=> array(
										'select' 	=> $rand_selected,
										'name' 		=> 'Random',
									),
									'title' 		=> array(
										'select' 	=> $title_selected,
										'name' 		=> 'Post Title',
									),
									'comment_count' => array(
										'select' 	=> $comment_count_selected,
										'name' 		=> 'Popular',
									)
								);
								$this -> st_render_select('order_by', 'Order by', 'Order by:', $order_by);
						  		$this -> st_render_textbox('limit', 'Posts Display', 'Posts Display:', 'number', $this -> valInt($gallery['post']['limit']), 'min="1"', '(Posts)');
						  		$this -> st_render_radio('first_img', 'Using the first image of post content if without featured image', 'Using First Images:', $gallery['post']['first_img']);
				        	?>
				        </div>
				    </div>
				 </div>
				<?php submit_button('Save Changes', 'st-button', 'savechanges'); ?>
			</div>
			<div class="st-right">
				
				<div id="setting_bar">
					
					<h3 class="box-title"><div class="dashicons dashicons-admin-generic"></div> <?php _e('Settings', 'st-gallery'); ?></h3>
						<div class="st-box">
							<div class="box-content">
							<?php
								switch ($gallery['settings']['width_end']) {
									case '%': 		$phantram_selected 		= 'selected="selected"'; break;
									case 'px': 		$px_selected 			= 'selected="selected"'; break;
									default: 		$phantram_selected 		= 'selected="selected"'; break;
								}
								$width_end = '
								<select id="width_end" name="width_end">
									<option value="%" '.$phantram_selected.'>'.__('%', 'st-gallery').'</option>
									<option value="px" '.$px_selected.'>'.__('px', 'st-gallery').'</option>
								</select>';
								$this -> st_render_textbox('width', __('Manually set a gallery width', 'st-gallery'), __('Width:', 'st-gallery'), 'number', $gallery['settings']['width'], 'min="1"', $width_end);
								$this -> st_render_textbox('height', __('Manually set a gallery height', 'st-gallery'), __('Height:', 'st-gallery'), 'number', $gallery['settings']['height'], '', __('px', 'st-gallery'));
								
								switch ($gallery['settings']['source']) {
									case 'Library': 	$Library_selected 		= 'selected="selected"'; break;
									case 'Post': 		$Post_selected 			= 'selected="selected"'; break;
									default: 			$Library_selected 		= 'selected="selected"'; break;
								}
								$source = array(
											'Library' 		=> array(
												'select' 	=> $Library_selected,
												'name' 		=> __('Library', 'st-gallery'),
											),
											'Post' 			=> array(
												'select'	=> $Post_selected,
												'name' 		=> __('Post', 'st-gallery'),
											)
										);
								$this -> st_render_select('source', __('Sets image source for gallery', 'st-gallery'), __('Source:', 'st-gallery'), $source);
								
								switch ($gallery['settings']['style']) {
									case 'gallery': 	$gallery_selected 		= 'selected="selected"'; break;
									case 'skitter': 	$skitter_selected 		= 'selected="selected"'; break;
									default: 			$gallery_selected 		= 'selected="selected"'; break;
								}
								$style = array(
									'gallery'	=> array(
										'select'	=> $gallery_selected,
										'name'		=> 'Gallery',
									),
									'skitter'	=> array(
										'select'	=> $skitter_selected,
										'name'		=> 'Skitter',
									),
								);
								$this -> st_render_select('style', 'Choose Type', 'Choose Type: ', $style);
							?>
							</div>
						</div>
						
						<h3 class="box-title gallery-setting <?php echo ( ($this -> valString($gallery['settings']['style'], '')=='gallery') ? 'setting_display' : 'setting_hide'); ?>"><div class="dashicons dashicons-images-alt2"></div> <?php _e('Gallery Settings', 'st-gallery'); ?></h3>
					  	<div class="st-box <?php echo ( ($this -> valString($gallery['settings']['style'], '')=='gallery') ? 'setting_display' : 'setting_hide'); ?>">
							<div class="box-content">
							<?php 
								
								$this -> st_render_radio('show_control', __('Show control', 'st-gallery'), __('Show Control:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['show_control']));
								$this -> st_render_radio('click_to_next', __('Click to next', 'st-gallery'), __('Click To Next:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['click_to_next']));
								$this -> st_render_radio('show_counter', __('Toggles the counter', 'st-gallery'), __('Show Counter:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['show_counter']));
								$this -> st_render_radio('show_prev_next', __('Toggles the image navigation arrows', 'st-gallery'), __('Show Prev/Next:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['show_prev_next']));
								$this -> st_render_radio('image_crop', __('Defines gallery will crop the image', 'st-gallery'), __('Image Crop:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['image_crop']));
								$this -> st_render_radio('imagePan', __('Toggles the image pan effect', 'st-gallery'), __('Image Pan:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['imagePan']));
								$this -> st_render_radio('showThumb', __('Toggles the thumbnail', 'st-gallery'), __('Show Thumbnails:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['showThumb']));
								$this -> st_render_radio('thumb_crop', __('Defines gallery will crop the thumbnail', 'st-gallery'), __('Thumb Crop:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['thumb_crop']));
								
								switch ($this -> valString($gallery['gallery']['transition'], 'fadeslide')) {
									case 'fadeslide': 	$fadeslide_selected 	= 'selected="selected"'; break;
									case 'flash': 		$flash_selected 		= 'selected="selected"'; break;
									case 'pulse': 		$pulse_selected 		= 'selected="selected"'; break;
									case 'slide': 		$slide_selected 		= 'selected="selected"'; break;
									case 'fade': 		$fade_selected 			= 'selected="selected"'; break;
									default: 			$fadeslide_selected 	= 'selected="selected"'; break;
								}
								$transition = array(
											'fadeslide'	 	=> array(
												'select' 	=> $fadeslide_selected,
												'name' 		=> __('Fade Slide', 'st-gallery'),
											),
											'flash' 		=> array(
												'select' 	=> $flash_selected,
												'name' 		=> __('Flash', 'st-gallery'),
											),
											'pulse' 		=> array(
												'select'	=> $pulse_selected,
												'name' 		=> __('Pulse', 'st-gallery'),
											),
											'slide' 		=> array(
												'select' 	=> $slide_selected,
												'name' 		=> __('Slide', 'st-gallery'),
											),
											'fade' 			=> array(
												'select' 	=> $fade_selected,
												'name' 		=> __('Fade', 'st-gallery'),
											)
										);
								$this -> st_render_select('transition', __('Defines what transition to use', 'st-gallery'), __('Transition:', 'st-gallery'), $transition);
								$this -> st_render_textbox('transition_speed', __('Defines the speed of the transition', 'st-gallery'), __('Transition Speed:', 'st-gallery'), 'number', $this -> valInt($gallery['gallery']['transition_speed']), '', __('(100 = 1 sec)', 'st-gallery'));
								$this -> st_render_radio('lightbox', __('Zoom in when the user clicks on an image', 'st-gallery'), __('LightBox:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['lightbox']));
								$this -> st_render_textbox('image_delay', __('Enter 0 to disable autoplay gallery', 'st-gallery'), __('Image Delay:', 'st-gallery'), 'number', $this -> valInt($gallery['gallery']['image_delay']), 'min="0"', __('(1000 = 1 sec)', 'st-gallery'));
								$this -> st_render_radio('show_title_image', __('Toggles the title', 'st-gallery'), __('Show Title Image:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['show_title_image']));
								$this -> st_render_radio('show_caption_image', __('Toggles the caption', 'st-gallery'), __('Show Caption Image:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['show_caption_image']));
								
								switch ($this -> valString($gallery['gallery']['theme'], 'classic')) {
									case 'classic': 	$classic_selected 		= 'selected="selected"'; break;
									case 'v2': 			$v2_selected 			= 'selected="selected"'; break;
									default: 			$classic_selected 	= 'selected="selected"'; break;
								}
								$theme = array(
											'classic' 		=> array(
												'select' 	=> $classic_selected,
												'name' 		=> __('Classic', 'st-gallery'),
											),
											'v2' 			=> array(
												'select' 	=> $v2_selected,
												'name' 		=> __('Style 2', 'st-gallery'),
											)
										);
								$this -> st_render_select('theme', __('Sets theme for gallery', 'st-gallery'), __('Theme:', 'st-gallery'), $theme);
								$this -> st_render_radio('responsive', __('Responsive', 'st-gallery'), __('Responsive:', 'st-gallery'), $this -> valBoolean($gallery['gallery']['responsive']));
							?>
						</div>
					</div>
				
					<h3 class="box-title skitter-setting <?php echo ( ($this -> valString($gallery['settings']['style'], '')=='skitter') ? 'setting_display' : 'setting_hide'); ?>"><div class="dashicons dashicons-format-image"></div> <?php _e('Skitter Settings', 'st-gallery'); ?></h3>
					<div class="st-box <?php echo ( ($this -> valString($gallery['settings']['style'], '')=='skitter') ? 'setting_display' : 'setting_hide'); ?>">
						<div class="box-content">
				  		<?php 
						
							$this -> st_render_radio('skitter_auto_play', __('Auto play', 'st-gallery'), __('Auto Play:', 'st-gallery'), $gallery['skitter']['auto_play']);
							$this -> st_render_radio('skitter_stop_over', __('Stop animation to move mouse over it', 'st-gallery'), __('Stop Over:', 'st-gallery'), $gallery['skitter']['stop_over']);
							$this -> st_render_textbox('skitter_interval', __('Interval between transitions', 'st-gallery'), __('Interval:', 'st-gallery'), 'number',$this -> valInt($gallery['skitter']['interval']) , 'min="0"', __('(1000 = 1 sec)', 'st-gallery'));
							$this -> st_render_radio('skitter_show_randomly', __('Toggles the randomly sliders', 'st-gallery'), __('Show Randomly:', 'st-gallery'), $gallery['skitter']['show_randomly']);
							$this -> st_render_radio('skitter_controls', __('Show control', 'st-gallery'), __('Show Control:', 'st-gallery'), $gallery['skitter']['controls']);
							
							switch ($gallery['skitter']['controls_position']) {
								case 'center': 			$center_selected 		= 'selected="selected"'; break;
								case 'leftTop': 		$leftTop_selected 		= 'selected="selected"'; break;
								case 'rightTop': 		$rightTop_selected 		= 'selected="selected"'; break;
								case 'leftBottom': 		$leftBottom_selected 	= 'selected="selected"'; break;
								case 'rightBottom':		$rightBottom_selected 	= 'selected="selected"'; break;
								default: 				$center_selected 		= 'selected="selected"'; break;
							}
							$skitter_controls_position = array(
										'center'	 	=> array(
											'select' 	=> $center_selected,
											'name' 		=> __('Center', 'st-gallery'),
										),
										'leftTop' 		=> array(
											'select' 	=> $leftTop_selected,
											'name' 		=> __('Left Top', 'st-gallery'),
										),
										'rightTop' 		=> array(
											'select'	=> $rightTop_selected,
											'name' 		=> __('Right Top', 'st-gallery'),
										),
										'leftBottom' 		=> array(
											'select' 	=> $leftBottom_selected,
											'name' 		=> __('Left Bottom', 'st-gallery'),
										),
										'rightBottom' 			=> array(
											'select' 	=> $rightBottom_selected,
											'name' 		=> __('Right Bottom', 'st-gallery'),
										)
									);
							$this -> st_render_select('skitter_controls_position', __('Defines controls position', 'st-gallery'), __('Controls Position:', 'st-gallery'), $skitter_controls_position);
							$this -> st_render_radio('skitter_progressbar', __('Show/hide progress bar', 'st-gallery'), __('Progress Bar:', 'st-gallery'), $gallery['skitter']['progressbar']);
							$this -> st_render_radio('skitter_label', __('Toggles the title', 'st-gallery'), __('Show Title:', 'st-gallery'), $gallery['skitter']['label']);
							
							switch ($gallery['skitter']['labelAnimation']) {
								case 'slideUp': 	$slideUp_selected 		= 'selected="selected"'; break;
								case 'left': 		$left_selected 			= 'selected="selected"'; break;
								case 'right': 		$right_selected 		= 'selected="selected"'; break;
								case 'fixed': 		$fixed_selected 		= 'selected="selected"'; break;
								default: 			$slideUp_selected 		= 'selected="selected"'; break;
							}
							$labelAnimation = array(
										'slideUp'	 	=> array(
											'select' 	=> $slideUp_selected,
											'name' 		=> __('Slide Up', 'st-gallery'),
										),
										'left' 		=> array(
											'select' 	=> $left_selected,
											'name' 		=> __('Left', 'st-gallery'),
										),
										'right' 		=> array(
											'select'	=> $right_selected,
											'name' 		=> __('Right', 'st-gallery'),
										),
										'fixed' 		=> array(
											'select'	=> $fixed_selected,
											'name' 		=> __('Fixed', 'st-gallery'),
										),
									);
							$this -> st_render_select('skitter_labelAnimation', __('Defines title animation', 'st-gallery'), __('Title Animation:', 'st-gallery'), $labelAnimation);
							
							switch ($gallery['skitter']['navigation']) {
								case 'thumbs': 		$thumbs_selected 		= 'selected="selected"'; break;
								case 'numbers': 	$numbers_selected 		= 'selected="selected"'; break;
								case 'dots': 		$dots_selected 			= 'selected="selected"'; break;
								default: 			$thumbs_selected 		= 'selected="selected"'; break;
							}
							$navigation = array(
										'thumbs' 		=> array(
											'select' 	=> $thumbs_selected,
											'name' 		=> __('Thumbnails', 'st-gallery'),
										),
										'numbers' 		=> array(
											'select'	=> $numbers_selected,
											'name' 		=> __('Numbers', 'st-gallery'),
										),
										'dots' 			=> array(
											'select'	=> $dots_selected,
											'name' 		=> __('Dots', 'st-gallery'),
										)
									);
							$this -> st_render_select('skitter_navigation', __('Sets navigation style', 'st-gallery'), __('Navigation Style:', 'st-gallery'), $navigation);
							
							switch ($gallery['skitter']['navigation_position']) {
								case 'center': 		$center_selected 		= 'selected="selected"'; break;
								case 'left': 		$left_selected 			= 'selected="selected"'; break;
								case 'right': 		$right_selected 		= 'selected="selected"'; break;
								default: 			$center_selected 		= 'selected="selected"'; break;
							}
							$navigation_position = array(
										'center' 		=> array(
											'select' 	=> $center_selected,
											'name' 		=> __('Center', 'st-gallery'),
										),
										'left' 			=> array(
											'select'	=> $left_selected,
											'name' 		=> __('Left', 'st-gallery'),
										),
										'right' 		=> array(
											'select'	=> $right_selected,
											'name' 		=> __('Right', 'st-gallery'),
										)
									);
							$this -> st_render_select('skitter_navigation_position', __('Sets navigation position', 'st-gallery'), __('Navigation Position:', 'st-gallery'), $navigation_position);
							$this -> st_render_radio('skitter_preview', __('Thumbnail previews when you hover over the dots', 'st-gallery'), __('Preview:', 'st-gallery'), $gallery['skitter']['preview']);
							$this -> st_render_radio('skitter_next_prev', __('Show the navigation buttons next/previous', 'st-gallery'), __('Show Next/Prev:', 'st-gallery'), $gallery['skitter']['next_prev']);
							$this -> st_render_radio('skitter_enable_navigation_keys', __('Using key < > to previous/next sliders', 'st-gallery'), __('Navigation Keys:', 'st-gallery'), $gallery['skitter']['enable_navigation_keys']);
							$this -> st_render_radio('skitter_hideTools', __('Auto-hide the navigation buttons, controls, thumbs', 'st-gallery'), __('Auto hide:', 'st-gallery'), $gallery['skitter']['hideTools']);
							$this -> st_render_radio('skitter_focus', __('Focus slideshow', 'st-gallery'), __('Focus Slideshow:', 'st-gallery'), $gallery['skitter']['focus']);
							
							switch ($gallery['skitter']['focus_position']) {
								case 'center': 			$center_selected 			= 'selected="selected"'; break;
								case 'leftTop': 		$leftTop_selected 			= 'selected="selected"'; break;
								case 'rightTop': 		$rightTop_selected 			= 'selected="selected"'; break;
								case 'leftBottom': 		$leftBottom_selected 		= 'selected="selected"'; break;
								case 'rightBottom': 	$rightBottom_selected 		= 'selected="selected"'; break;
								default: 				$center_selected 			= 'selected="selected"'; break;
							}
							$focus_position = array(
										'center' 		=> array(
											'select' 	=> $center_selected,
											'name' 		=> __('Center', 'st-gallery'),
										),
										'leftTop' 		=> array(
											'select'	=> $leftTop_selected,
											'name' 		=> __('Left Top', 'st-gallery'),
										),
										'rightTop' 		=> array(
											'select'	=> $rightTop_selected,
											'name' 		=> __('Right Top', 'st-gallery'),
										),
										'leftBottom' 	=> array(
											'select'	=> $leftBottom_selected,
											'name' 		=> __('Left Bottom', 'st-gallery'),
										),
										'rightBottom' 	=> array(
											'select'	=> $rightBottom_selected,
											'name' 		=> __('Right Bottom', 'st-gallery'),
										)
									);
							$this -> st_render_select('skitter_focus_position', __('Sets position for focus slideshow button', 'st-gallery'), __('Focus Position:', 'st-gallery'), $focus_position);
							$this -> st_render_radio('skitter_fullscreen', __('Sets fullscreen', 'st-gallery'), __('Fullscreen:', 'st-gallery'), $gallery['skitter']['fullscreen']);
							
							
							switch ($gallery['skitter']['animation']) {
								case 'cube': 			$cube_selected 				= 'selected="selected"'; break;
								case 'cubeRandom': 		$cubeRandom_selected 		= 'selected="selected"'; break;
								case 'block': 			$block_selected 			= 'selected="selected"'; break;
								case 'cubeStop': 		$cubeStop_selected 			= 'selected="selected"'; break;
								
								case 'cubeHide': 		$cubeHide_selected 			= 'selected="selected"'; break;
								case 'cubeSize': 		$cubeSize_selected 			= 'selected="selected"'; break;
								case 'horizontal': 		$horizontal_selected 		= 'selected="selected"'; break;
								case 'showBars': 		$showBars_selected 			= 'selected="selected"'; break;
								case 'showBarsRandom': 	$showBarsRandom_selected 	= 'selected="selected"'; break;
								case 'tube': 			$tube_selected 				= 'selected="selected"'; break;
								case 'fade': 			$fade_selected 				= 'selected="selected"'; break;
								case 'fadeFour': 		$fadeFour_selected 			= 'selected="selected"'; break;
								case 'paralell': 		$paralell_selected 			= 'selected="selected"'; break;
								case 'blind': 			$blind_selected 			= 'selected="selected"'; break;
								
								case 'blindHeight': 	$blindHeight_selected 		= 'selected="selected"'; break;
								case 'blindWidth': 		$blindWidth_selected 		= 'selected="selected"'; break;
								case 'directionTop': 	$directionTop_selected 		= 'selected="selected"'; break;
								case 'directionBottom': $directionBottom_selected 	= 'selected="selected"'; break;
								case 'directionRight': 	$directionRight_selected 	= 'selected="selected"'; break;
								case 'directionLeft': 	$directionLeft_selected 	= 'selected="selected"'; break;
								case 'cubeStopRandom': 	$cubeStopRandom_selected 	= 'selected="selected"'; break;
								case 'cubeSpread': 		$cubeSpread_selected 		= 'selected="selected"'; break;
								case 'cubeJelly': 		$cubeJelly_selected 		= 'selected="selected"'; break;
								case 'glassCube': 		$glassCube_selected 		= 'selected="selected"'; break;
								
								case 'glassBlock': 		$glassBlock_selected 		= 'selected="selected"'; break;
								case 'circles': 		$circles_selected 			= 'selected="selected"'; break;
								case 'circlesInside': 	$circlesInside_selected 	= 'selected="selected"'; break;
								case 'circlesRotate': 	$circlesRotate_selected 	= 'selected="selected"'; break;
								case 'cubeShow': 		$cubeShow_selected 			= 'selected="selected"'; break;
								case 'upBars': 			$upBars_selected 			= 'selected="selected"'; break;
								case 'downBars': 		$downBars_selected 			= 'selected="selected"'; break;
								case 'hideBars': 		$hideBars_selected 			= 'selected="selected"'; break;
								case 'swapBars': 		$swapBars_selected 			= 'selected="selected"'; break;
								case 'swapBarsBack': 	$swapBarsBack_selected 		= 'selected="selected"'; break;
								
								case 'swapBlocks': 		$swapBlocks_selected 		= 'selected="selected"'; break;
								case 'cut': 			$cut_selected 				= 'selected="selected"'; break;
								case 'random': 			$random_selected 			= 'selected="selected"'; break;
								case 'randomSmart': 	$randomSmart_selected 		= 'selected="selected"'; break;
								
								default: 				$randomSmart_selected 		= 'selected="selected"'; break;
							}
							$skitter_animation = array(
										'cube' 				=> array(
											'select' 		=> $cube_selected,
											'name' 			=> __('cube', 'st-gallery'),
										),
										'cubeRandom' 		=> array(
											'select' 		=> $cubeRandom_selected,
											'name' 			=> __('cubeRandom', 'st-gallery'),
										),
										'block' 			=> array(
											'select' 		=> $block_selected,
											'name' 			=> __('block', 'st-gallery'),
										),
										'cubeStop' 			=> array(
											'select' 		=> $cubeStop_selected,
											'name' 			=> __('cubeStop', 'st-gallery'),
										),
										'cubeHide' 			=> array(
											'select' 		=> $cubeHide_selected,
											'name' 			=> __('cubeHide', 'st-gallery'),
										),
										'cubeSize' 			=> array(
											'select' 		=> $cubeSize_selected ,
											'name' 			=> __('cubeSize', 'st-gallery'),
										),
										'horizontal' 		=> array(
											'select' 		=> $horizontal_selected,
											'name' 			=> __('horizontal', 'st-gallery'),
										),
										'showBars' 			=> array(
											'select' 		=> $showBars_selected,
											'name' 			=> __('showBars', 'st-gallery'),
										),
										'showBarsRandom'	=> array(
											'select' 		=> $showBarsRandom_selected,
											'name' 			=> __('showBarsRandom', 'st-gallery'),
										),
										'tube' 				=> array(
											'select' 		=> $tube_selected,
											'name' 			=> __('tube', 'st-gallery'),
										),
										'fade' 				=> array(
											'select' 		=> $fade_selected,
											'name' 			=> __('fade', 'st-gallery'),
										),
										'fadeFour' 			=> array(
											'select' 		=> $fadeFour_selected,
											'name' 			=> __('fadeFour', 'st-gallery'),
										),
										'paralell' 			=> array(
											'select' 		=> $paralell_selected,
											'name' 			=> __('paralell', 'st-gallery'),
										),
										'blind' 			=> array(
											'select' 		=> $blind_selected,
											'name' 			=> __('blind', 'st-gallery'),
										),
										'blindHeight' 		=> array(
											'select' 		=> $blindHeight_selected,
											'name' 			=> __('blindHeight', 'st-gallery'),
										),
										'blindWidth' 		=> array(
											'select' 		=> $blindWidth_selected ,
											'name' 			=> __('blindWidth', 'st-gallery'),
										),
										'directionTop' 		=> array(
											'select' 		=> $directionTop_selected,
											'name' 			=> __('directionTop', 'st-gallery'),
										),
										'directionBottom' 	=> array(
											'select' 		=> $directionBottom_selected,
											'name' 			=> __('directionBottom', 'st-gallery'),
										),
										'directionRight'	=> array(
											'select' 		=> $directionRight_selected,
											'name' 			=> __('directionRight', 'st-gallery'),
										),
										'directionLeft' 	=> array(
											'select' 		=> $directionLeft_selected,
											'name' 			=> __('directionLeft', 'st-gallery'),
										),
										'cubeStopRandom' 	=> array(
											'select' 		=> $cubeStopRandom_selected,
											'name' 			=> __('cubeStopRandom', 'st-gallery'),
										),
										'cubeSpread' 		=> array(
											'select' 		=> $cubeSpread_selected,
											'name' 			=> __('cubeSpread', 'st-gallery'),
										),
										'cubeJelly' 		=> array(
											'select' 		=> $cubeJelly_selected,
											'name' 			=> __('cubeJelly', 'st-gallery'),
										),
										'glassCube' 		=> array(
											'select' 		=> $glassCube_selected,
											'name' 			=> __('glassCube', 'st-gallery'),
										),
										'glassBlock' 		=> array(
											'select' 		=> $glassBlock_selected,
											'name' 			=> __('glassBlock', 'st-gallery'),
										),
										'circles' 			=> array(
											'select' 		=> $circles_selected,
											'name' 			=> __('circles', 'st-gallery'),
										),
										'circlesInside' 	=> array(
											'select' 		=> $circlesInside_selected,
											'name' 			=> __('circlesInside', 'st-gallery'),
										),
										'circlesRotate' 	=> array(
											'select' 		=> $circlesRotate_selected,
											'name' 			=> __('circlesRotate', 'st-gallery'),
										),
										'cubeShow' 			=> array(
											'select' 		=> $cubeShow_selected,
											'name' 			=> __('cubeShow', 'st-gallery'),
										),
										'upBars' 			=> array(
											'select' 		=> $upBars_selected,
											'name' 			=> __('upBars', 'st-gallery'),
										),
										'downBars' 			=> array(
											'select' 		=> $downBars_selected,
											'name' 			=> __('downBars', 'st-gallery'),
										),
										'hideBars' 			=> array(
											'select' 		=> $hideBars_selected,
											'name' 			=> __('hideBars', 'st-gallery'),
										),
										'swapBars' 			=> array(
											'select' 		=> $swapBars_selected,
											'name' 			=> __('swapBars', 'st-gallery'),
										),
										'swapBarsBack' 		=> array(
											'select' 		=> $swapBarsBack_selected,
											'name' 			=> __('swapBarsBack', 'st-gallery'),
										),
										'swapBlocks' 		=> array(
											'select' 		=> $swapBlocks_selected,
											'name' 			=> __('swapBlocks', 'st-gallery'),
										),
										'cut' 				=> array(
											'select' 		=> $cut_selected,
											'name' 			=> __('cut', 'st-gallery'),
										),
										'random' 			=> array(
											'select' 		=> $random_selected,
											'name' 			=> __('random', 'st-gallery'),
										),
										'randomSmart' 		=> array(
											'select' 		=> $randomSmart_selected,
											'name' 			=> __('randomSmart', 'st-gallery'),
										),
									);
							$this -> st_render_select('skitter_animation', __('Sets animation', 'st-gallery'), __('Animation:', 'st-gallery'), $skitter_animation);
							
							switch ($gallery['skitter']['theme']) {
								case 'default': 	$default_selected 		= 'selected="selected"'; break;
								case 'minimalist': 	$minimalist_selected 	= 'selected="selected"'; break;
								case 'round': 		$round_selected 		= 'selected="selected"'; break;
								case 'clean': 		$clean_selected 		= 'selected="selected"'; break;
								case 'square': 		$square_selected 		= 'selected="selected"'; break;
								default: 			$default_selected 		= 'selected="selected"'; break;
							}
							$skitter_theme = array(
										'default' 		=> array(
											'select' 	=> $default_selected,
											'name' 		=> __('Default', 'st-gallery'),
										),
										'minimalist' 	=> array(
											'select' 	=> $minimalist_selected,
											'name' 		=> __('Minimalist', 'st-gallery'),
										),
										'round' 		=> array(
											'select' 	=> $round_selected,
											'name' 		=> __('Round', 'st-gallery'),
										),
										'clean' 		=> array(
											'select' 	=> $clean_selected,
											'name' 		=> __('Clean', 'st-gallery'),
										),
										'square' 		=> array(
											'select' 	=> $square_selected,
											'name' 		=> __('Square', 'st-gallery'),
										)
									);
							$this -> st_render_select('skitter_theme', __('Sets theme', 'st-gallery'), __('Theme:', 'st-gallery'), $skitter_theme);
						?>
						</div>
					</div>
					
					
				</div>
				
				<?php $this -> StCopyright(); ?>
				
			</div>
	  	</form>
	
	<?php
		}
	}

	if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['savechanges'])){
		unset($this->options[$id]);
		update_option('st_gallery_wp',$this->options);
		$old_options_value = get_option('st_gallery_wp');
		
		$new_options_value = array();
		
		$new_options_value[$id][name] 							= 	$_POST['name'];
		//===========  Settings ===========//
		$new_options_value[$id][settings][source] 				= 	$_POST['source'];
		$new_options_value[$id][settings][width] 				= 	$_POST['width'];
		$new_options_value[$id][settings][width_end] 			= 	$_POST['width_end'];
		$new_options_value[$id][settings][height] 				= 	$_POST['height'];
		$new_options_value[$id][settings][style] 				= 	$_POST['style'];
		//=========== Post Source  ===========//
		$new_options_value[$id][post][post_category] 			= 	$_POST['post_category'];
		$new_options_value[$id][post][order_by] 				= 	$_POST['order_by'];
		$new_options_value[$id][post][limit] 					= 	$_POST['limit'];
		$new_options_value[$id][post][first_img] 				= 	$_POST['first_img'];
		//=========== Library Source ===========//
		if (isset($_POST['image'])){
			$i = 1;
			foreach ($_POST['image'] as $key => $value) {
				$new_options_value[$id][images][$i][url] 		=	$this -> relativeURL($value['url']);
				$new_options_value[$id][images][$i][title]		= 	$value['title'];
				$new_options_value[$id][images][$i][caption]	=	$value['caption'];
				$new_options_value[$id][images][$i][url_2]		= 	$value['url_2'];
				$i++;
			}
		}
		//=========== Gallery Settings ===========//
		$new_options_value[$id][gallery][show_control]	 		= 	$_POST['show_control'];
		$new_options_value[$id][gallery][click_to_next]			= 	$_POST['click_to_next'];
		$new_options_value[$id][gallery][show_counter] 			= 	$_POST['show_counter'];
		$new_options_value[$id][gallery][show_prev_next] 		= 	$_POST['show_prev_next'];
		$new_options_value[$id][gallery][image_crop] 			= 	$_POST['image_crop'];
		$new_options_value[$id][gallery][imagePan] 				= 	$_POST['imagePan'];
		$new_options_value[$id][gallery][showThumb] 			= 	$_POST['showThumb'];
		$new_options_value[$id][gallery][thumb_crop] 			= 	$_POST['thumb_crop'];
		$new_options_value[$id][gallery][transition] 			= 	$_POST['transition'];
		$new_options_value[$id][gallery][transition_speed] 		= 	$_POST['transition_speed'];
		$new_options_value[$id][gallery][lightbox] 				= 	$_POST['lightbox'];
		$new_options_value[$id][gallery][image_delay] 			= 	$_POST['image_delay'];
		$new_options_value[$id][gallery][show_title_image]		= 	$_POST['show_title_image'];
		$new_options_value[$id][gallery][show_caption_image]	= 	$_POST['show_caption_image'];
		$new_options_value[$id][gallery][theme] 				= 	$_POST['theme'];
		$new_options_value[$id][gallery][responsive] 			= 	$_POST['responsive'];
		//=========== Skitter Settings ===========//
		$new_options_value[$id][skitter][auto_play]	 				= 	$_POST['skitter_auto_play'];
		$new_options_value[$id][skitter][stop_over]					= 	$_POST['skitter_stop_over'];
		$new_options_value[$id][skitter][interval] 					= 	$_POST['skitter_interval'];
		$new_options_value[$id][skitter][show_randomly] 			= 	$_POST['skitter_show_randomly'];
		$new_options_value[$id][skitter][controls] 					= 	$_POST['skitter_controls'];
		$new_options_value[$id][skitter][controls_position] 		= 	$_POST['skitter_controls_position'];
		$new_options_value[$id][skitter][progressbar] 				= 	$_POST['skitter_progressbar'];
		$new_options_value[$id][skitter][label] 					= 	$_POST['skitter_label'];
		$new_options_value[$id][skitter][labelAnimation] 			= 	$_POST['skitter_labelAnimation'];
		$new_options_value[$id][skitter][navigation] 				= 	$_POST['skitter_navigation'];
		$new_options_value[$id][skitter][navigation_position] 		= 	$_POST['skitter_navigation_position'];
		$new_options_value[$id][skitter][preview] 					= 	$_POST['skitter_preview'];
		$new_options_value[$id][skitter][next_prev]					= 	$_POST['skitter_next_prev'];
		$new_options_value[$id][skitter][enable_navigation_keys]	= 	$_POST['skitter_enable_navigation_keys'];
		$new_options_value[$id][skitter][hideTools] 				= 	$_POST['skitter_hideTools'];
		$new_options_value[$id][skitter][focus] 					= 	$_POST['skitter_focus'];
		$new_options_value[$id][skitter][focus_position] 			= 	$_POST['skitter_focus_position'];
		$new_options_value[$id][skitter][fullscreen] 				= 	$_POST['skitter_fullscreen'];
		$new_options_value[$id][skitter][animation] 				= 	$_POST['skitter_animation'];
		$new_options_value[$id][skitter][theme] 					= 	$_POST['skitter_theme'];
		
		
		if(!empty($old_options_value)){
			$new_options_value = array_merge($old_options_value,$new_options_value);
		}
		
		update_option('st_gallery_wp', $new_options_value);
		echo '<meta http-equiv="refresh" content="0; URL=admin.php?page=st_gallery&action=edit&id='.$id.'&message=2">';
	}
  	?>
	  	
	</div>
			
	<?php
	}

	/*
	 * Add new gallery
	 */
	public function addNew(){ ?>
	 	<div class="wrap st_gallery_wp">
	   	<h2>
	   		<?php _e('Add New Gallery', 'st-gallery'); ?> <a href="?page=st_gallery" class="add-new-h2"><div class="dashicons dashicons-arrow-left-alt"></div><?php _e('Back to the list', 'st-gallery'); ?></a>
	   	</h2> 
	   	<?php
			$this->st_message();
		?>
	  	<form method="post" action="" name="stForm" id="stForm">
	  		<?php
			settings_fields('st_option_group');
			$id = uniqid();
			?>
			<div class="st-left">
				<div class="st-box name">
					<input name="id" type="hidden" value="<?=$id ?>">
		  			<input name="name" type="text" id="name" value="" class="name" placeholder="<?php _e('Enter name here', 'st-gallery'); ?>"> 
	  			</div>
  				
  				<div id="tabs-container">
				    <ul class="tabs-menu">
				        <li class="current"><a href="#tab-1"><?php _e('Library Source', 'st-gallery'); ?></a></li>
				        <li><a href="#tab-2"><?php _e('Post Source', 'st-gallery'); ?></a></li>
				    </ul>
				    <div class="tab">
				        <div id="tab-1" class="tab-content">
				        	<div id="add-images">
								<?php wp_enqueue_media(); ?>
								<input type="button" class="button st-button st-upload" id="st-upload" value="<?php _e('Go Library', 'st-gallery'); ?>" />
			  				</div>
			  				<div id="appendImages"><!-- images --></div>
				        </div>
				        <div id="tab-2" class="tab-content">
				        	<div class="st-row">
				        		<div class="left">
				        			<label><?php _e('Select Category: ', 'st-gallery'); ?></label>
				        		</div>
				        		<div class="right">
						        	<div class="select_category">
						        		<?php wp_category_checklist( 0, 0, false ,false, null, false); ?> 	
						        	</div>
				        		</div>
				        	</div>
				        	
						  	<?php
							  	$order_by = array(
									'date' 			=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> 'Published Date',
									),
									'modified' 		=> array(
										'select' 	=> '',
										'name' 		=> 'Modified Date',
									),
									'rand' 			=> array(
										'select' 	=> '',
										'name' 		=> 'Random',
									),
									'title' 		=> array(
										'select' 	=> '',
										'name' 		=> 'Post Title',
									),
									'comment_count' => array(
										'select' 	=> '',
										'name' 		=> 'Popular',
									)
								);
								$this -> st_render_select('order_by', 'Order by', 'Order by:', $order_by);
						  		$this -> st_render_textbox('limit', 'Posts Display', 'Posts Display:', 'number', '10', 'min="1"', '(Posts)');
						  		$this -> st_render_radio('first_img', 'Using the first image of post content if without featured image', 'Using First Images:', true);
						  	?>
					  	
						</div>
					</div>
				</div>
				<?php submit_button('Submit', 'st-button', 'savechanges'); ?>
			</div>
			<div class="st-right">
				
				<div id="setting_bar">
					<h3 class="box-title"><div class="dashicons dashicons-admin-generic"></div> <?php _e('Settings', 'st-gallery'); ?></h3>
					<div class="st-box">
						<div class="box-content">
						<?php
							$width_end = '
							<select id="width_end" name="width_end">
								<option value="%" selected="selected">'.__('%', 'st-gallery').'</option>
								<option value="px">'.__('px', 'st-gallery').'</option>
							</select>';
							$this -> st_render_textbox('width', __('Manually set a gallery width', 'st-gallery'), __('Width:', 'st-gallery'), 'number', '100', 'min="1"', $width_end);
							$this -> st_render_textbox('height', __('Manually set a gallery height', 'st-gallery'), __('Height:', 'st-gallery'), 'number', '400', '', __('px', 'st-gallery'));
							$source = array(
										'Library' 		=> array(
											'select' 	=> 'selected="selected"',
											'name' 		=> __('Library', 'st-gallery'),
										),
										'Post' 			=> array(
											'select'	=> '',
											'name' 		=> __('Post', 'st-gallery'),
										)
									);
							$this -> st_render_select('source', __('Sets image source for gallery', 'st-gallery'), __('Source:', 'st-gallery'), $source);
							$style = array(
								'gallery'	=> array(
									'select'	=> 'selected="selected"',
									'name'		=> 'Gallery',
								),
								'skitter'	=> array(
									'select'	=> '',
									'name'		=> 'Skitter',
								),
							);
							$this -> st_render_select('style', 'Choose Type', 'Choose Type: ', $style);
						?>
						</div>
					</div>
					<h3 class="box-title gallery-setting <?php echo ( ($this -> valString($gallery['settings']['style'], 'gallery')=='gallery') ? 'setting_display' : 'setting_hide'); ?>"><div class="dashicons dashicons-images-alt2"></div> <?php _e('Gallery Settings', 'st-gallery'); ?></h3>
				  	<div class="st-box <?php echo ( ($this -> valString($gallery['settings']['style'], 'gallery')=='gallery') ? 'setting_display' : 'setting_hide'); ?>">
						<div class="box-content">
						<?php 
							
							$this -> st_render_radio('show_control', __('Show control', 'st-gallery'), __('Show Control:', 'st-gallery'), true);
							$this -> st_render_radio('click_to_next', __('Click to next', 'st-gallery'), __('Click To Next:', 'st-gallery'), false);
							$this -> st_render_radio('show_counter', __('Toggles the counter', 'st-gallery'), __('Show Counter:', 'st-gallery'), true);
							$this -> st_render_radio('show_prev_next', __('Toggles the image navigation arrows', 'st-gallery'), __('Show Prev/Next:', 'st-gallery'), true);
							$this -> st_render_radio('image_crop', __('Defines gallery will crop the image', 'st-gallery'), __('Image Crop:', 'st-gallery'), true);
							$this -> st_render_radio('imagePan', __('Toggles the image pan effect', 'st-gallery'), __('Image Pan:', 'st-gallery'), true);
							$this -> st_render_radio('showThumb', __('Toggles the thumbnail', 'st-gallery'), __('Show Thumbnails:', 'st-gallery'), true);
							$this -> st_render_radio('thumb_crop', __('Defines gallery will crop the thumbnail', 'st-gallery'), __('Thumb Crop:', 'st-gallery'), true);
							$transition = array(
										'fadeslide'	 	=> array(
											'select' 	=> 'selected="selected"',
											'name' 		=> __('Fade Slide', 'st-gallery'),
										),
										'flash' 		=> array(
											'select' 	=> '',
											'name' 		=> __('Flash', 'st-gallery'),
										),
										'pulse' 		=> array(
											'select'	=> '',
											'name' 		=> __('Pulse', 'st-gallery'),
										),
										'slide' 		=> array(
											'select' 	=> '',
											'name' 		=> __('Slide', 'st-gallery'),
										),
										'fade' 			=> array(
											'select' 	=> '',
											'name' 		=> __('Fade', 'st-gallery'),
										)
									);
							$this -> st_render_select('transition', __('Defines what transition to use', 'st-gallery'), __('Transition:', 'st-gallery'), $transition);
							$this -> st_render_textbox('transition_speed', __('Defines the speed of the transition', 'st-gallery'), __('Transition Speed:', 'st-gallery'), 'number', '500', '', __('(100 = 1 sec)', 'st-gallery'));
							$this -> st_render_radio('lightbox', __('Zoom in when the user clicks on an image', 'st-gallery'), __('LightBox:', 'st-gallery'), true);
							$this -> st_render_textbox('image_delay', __('Enter 0 to disable autoplay gallery', 'st-gallery'), __('Image Delay:', 'st-gallery'), 'number', '3000', 'min="0"', __('(1000 = 1 sec)', 'st-gallery'));
							$this -> st_render_radio('show_title_image', __('Toggles the title', 'st-gallery'), __('Show Title Image:', 'st-gallery'), true);
							$this -> st_render_radio('show_caption_image', __('Toggles the caption', 'st-gallery'), __('Show Caption Image:', 'st-gallery'), true);
							$theme = array(
										'classic' 		=> array(
											'select' 	=> 'selected="selected"',
											'name' 		=> __('Classic', 'st-gallery'),
										),
										'v2' 			=> array(
											'select' 	=> '',
											'name' 		=> __('Style 2', 'st-gallery'),
										)
									);
							$this -> st_render_select('theme', __('Sets theme for gallery', 'st-gallery'), __('Theme:', 'st-gallery'), $theme);
							$this -> st_render_radio('responsive', __('Responsive', 'st-gallery'), __('Responsive:', 'st-gallery'), true);
						?>
					</div>
				</div>
				 
				<h3 class="box-title skitter-setting <?php echo ( ($this -> valString($gallery['settings']['style'], '')=='skitter') ? 'setting_display' : 'setting_hide'); ?>"><div class="dashicons dashicons-format-image"></div> <?php _e('Skitter Settings', 'st-gallery'); ?></h3>
				<div class="st-box <?php echo ( ($this -> valString($gallery['settings']['style'], '')=='skitter') ? 'setting_display' : 'setting_hide'); ?>">
					<div class="box-content">
			  		<?php 
					
						$this -> st_render_radio('skitter_auto_play', __('Auto play', 'st-gallery'), __('Auto Play:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_stop_over', __('Stop animation to move mouse over it', 'st-gallery'), __('Stop Over:', 'st-gallery'), true);
						$this -> st_render_textbox('skitter_interval', __('Interval between transitions', 'st-gallery'), __('Interval:', 'st-gallery'), 'number', '2500', 'min="0"', __('(1000 = 1 sec)', 'st-gallery'));
						$this -> st_render_radio('skitter_show_randomly', __('Toggles the randomly sliders', 'st-gallery'), __('Show Randomly:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_controls', __('Show control', 'st-gallery'), __('Show Control:', 'st-gallery'), true);
						$skitter_controls_position = array(
									'center'	 	=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> __('Center', 'st-gallery'),
									),
									'leftTop' 		=> array(
										'select' 	=> '',
										'name' 		=> __('Left Top', 'st-gallery'),
									),
									'rightTop' 		=> array(
										'select'	=> '',
										'name' 		=> __('Right Top', 'st-gallery'),
									),
									'leftBottom' 		=> array(
										'select' 	=> '',
										'name' 		=> __('Left Bottom', 'st-gallery'),
									),
									'rightBottom' 			=> array(
										'select' 	=> '',
										'name' 		=> __('Right Bottom', 'st-gallery'),
									)
								);
						$this -> st_render_select('skitter_controls_position', __('Defines controls position', 'st-gallery'), __('Controls Position:', 'st-gallery'), $skitter_controls_position);
						$this -> st_render_radio('skitter_progressbar', __('Show/hide progress bar', 'st-gallery'), __('Progress Bar:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_label', __('Toggles the title', 'st-gallery'), __('Show Title:', 'st-gallery'), true);
						$labelAnimation = array(
									'slideUp'	 	=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> __('Slide Up', 'st-gallery'),
									),
									'left' 		=> array(
										'select' 	=> '',
										'name' 		=> __('Left', 'st-gallery'),
									),
									'right' 		=> array(
										'select'	=> '',
										'name' 		=> __('Right', 'st-gallery'),
									),
									'fixed' 		=> array(
										'select'	=> '',
										'name' 		=> __('Fixed', 'st-gallery'),
									),
								);
						$this -> st_render_select('skitter_labelAnimation', __('Defines title animation', 'st-gallery'), __('Title Animation:', 'st-gallery'), $labelAnimation);
						$navigation = array(
									'thumbs' 		=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> __('Thumbnails', 'st-gallery'),
									),
									'numbers' 			=> array(
										'select'	=> '',
										'name' 		=> __('Numbers', 'st-gallery'),
									),
									'dots' 			=> array(
										'select'	=> '',
										'name' 		=> __('Dots', 'st-gallery'),
									)
								);
						$this -> st_render_select('skitter_navigation', __('Sets navigation style', 'st-gallery'), __('Navigation Style:', 'st-gallery'), $navigation);
						$navigation_position = array(
									'center' 		=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> __('Center', 'st-gallery'),
									),
									'left' 			=> array(
										'select'	=> '',
										'name' 		=> __('Left', 'st-gallery'),
									),
									'right' 		=> array(
										'select'	=> '',
										'name' 		=> __('Right', 'st-gallery'),
									)
								);
						$this -> st_render_select('skitter_navigation_position', __('Sets navigation position', 'st-gallery'), __('Navigation Position:', 'st-gallery'), $navigation_position);
						$this -> st_render_radio('skitter_preview', __('Thumbnail previews when you hover over the dots', 'st-gallery'), __('Preview:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_next_prev', __('Show the navigation buttons next/previous', 'st-gallery'), __('Show Next/Prev:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_enable_navigation_keys', __('Using key < > to previous/next sliders', 'st-gallery'), __('Navigation Keys:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_hideTools', __('Auto-hide the navigation buttons, controls, thumbs', 'st-gallery'), __('Auto hide:', 'st-gallery'), true);
						$this -> st_render_radio('skitter_focus', __('Focus slideshow', 'st-gallery'), __('Focus Slideshow:', 'st-gallery'), true);
						$focus_position = array(
									'center' 		=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> __('Center', 'st-gallery'),
									),
									'leftTop' 		=> array(
										'select'	=> '',
										'name' 		=> __('Left Top', 'st-gallery'),
									),
									'rightTop' 		=> array(
										'select'	=> '',
										'name' 		=> __('Right Top', 'st-gallery'),
									),
									'leftBottom' 	=> array(
										'select'	=> '',
										'name' 		=> __('Left Bottom', 'st-gallery'),
									),
									'rightBottom' 	=> array(
										'select'	=> '',
										'name' 		=> __('Right Bottom', 'st-gallery'),
									)
								);
						$this -> st_render_select('skitter_focus_position', __('Sets position for focus slideshow button', 'st-gallery'), __('Focus Position:', 'st-gallery'), $focus_position);
						$this -> st_render_radio('skitter_fullscreen', __('Sets fullscreen', 'st-gallery'), __('Fullscreen:', 'st-gallery'), false);
						
						$skitter_animation = array(
							'cube' 				=> array(
								'select' 		=> 'selected="selected"',
								'name' 			=> __('cube', 'st-gallery'),
							),
							'cubeRandom' 		=> array(
								'select' 		=> '',
								'name' 			=> __('cubeRandom', 'st-gallery'),
							),
							'block' 			=> array(
								'select' 		=> '',
								'name' 			=> __('block', 'st-gallery'),
							),
							'cubeStop' 			=> array(
								'select' 		=> '',
								'name' 			=> __('cubeStop', 'st-gallery'),
							),
							'cubeHide' 			=> array(
								'select' 		=> '',
								'name' 			=> __('cubeHide', 'st-gallery'),
							),
							'cubeSize' 			=> array(
								'select' 		=> '' ,
								'name' 			=> __('cubeSize', 'st-gallery'),
							),
							'horizontal' 		=> array(
								'select' 		=> '',
								'name' 			=> __('horizontal', 'st-gallery'),
							),
							'showBars' 			=> array(
								'select' 		=> '',
								'name' 			=> __('showBars', 'st-gallery'),
							),
							'showBarsRandom'	=> array(
								'select' 		=> '',
								'name' 			=> __('showBarsRandom', 'st-gallery'),
							),
							'tube' 				=> array(
								'select' 		=> '',
								'name' 			=> __('tube', 'st-gallery'),
							),
							'fade' 				=> array(
								'select' 		=> '',
								'name' 			=> __('fade', 'st-gallery'),
							),
							'fadeFour' 			=> array(
								'select' 		=> '',
								'name' 			=> __('fadeFour', 'st-gallery'),
							),
							'paralell' 			=> array(
								'select' 		=> '',
								'name' 			=> __('paralell', 'st-gallery'),
							),
							'blind' 			=> array(
								'select' 		=> '',
								'name' 			=> __('blind', 'st-gallery'),
							),
							'blindHeight' 		=> array(
								'select' 		=> '',
								'name' 			=> __('blindHeight', 'st-gallery'),
							),
							'blindWidth' 		=> array(
								'select' 		=> '' ,
								'name' 			=> __('blindWidth', 'st-gallery'),
							),
							'directionTop' 		=> array(
								'select' 		=> '',
								'name' 			=> __('directionTop', 'st-gallery'),
							),
							'directionBottom' 	=> array(
								'select' 		=> '',
								'name' 			=> __('directionBottom', 'st-gallery'),
							),
							'directionRight'	=> array(
								'select' 		=> '',
								'name' 			=> __('directionRight', 'st-gallery'),
							),
							'directionLeft' 	=> array(
								'select' 		=> '',
								'name' 			=> __('directionLeft', 'st-gallery'),
							),
							'cubeStopRandom' 	=> array(
								'select' 		=> '',
								'name' 			=> __('cubeStopRandom', 'st-gallery'),
							),
							'cubeSpread' 		=> array(
								'select' 		=> '',
								'name' 			=> __('cubeSpread', 'st-gallery'),
							),
							'cubeJelly' 		=> array(
								'select' 		=> '',
								'name' 			=> __('cubeJelly', 'st-gallery'),
							),
							'glassCube' 		=> array(
								'select' 		=> '',
								'name' 			=> __('glassCube', 'st-gallery'),
							),
							'glassBlock' 		=> array(
								'select' 		=> '',
								'name' 			=> __('glassBlock', 'st-gallery'),
							),
							'circles' 			=> array(
								'select' 		=> '',
								'name' 			=> __('circles', 'st-gallery'),
							),
							'circlesInside' 	=> array(
								'select' 		=> '',
								'name' 			=> __('circlesInside', 'st-gallery'),
							),
							'circlesRotate' 	=> array(
								'select' 		=> '',
								'name' 			=> __('circlesRotate', 'st-gallery'),
							),
							'cubeShow' 			=> array(
								'select' 		=> '',
								'name' 			=> __('cubeShow', 'st-gallery'),
							),
							'upBars' 			=> array(
								'select' 		=> '',
								'name' 			=> __('upBars', 'st-gallery'),
							),
							'downBars' 			=> array(
								'select' 		=> '',
								'name' 			=> __('downBars', 'st-gallery'),
							),
							'hideBars' 			=> array(
								'select' 		=> '',
								'name' 			=> __('hideBars', 'st-gallery'),
							),
							'swapBars' 			=> array(
								'select' 		=> '',
								'name' 			=> __('swapBars', 'st-gallery'),
							),
							'swapBarsBack' 		=> array(
								'select' 		=> '',
								'name' 			=> __('swapBarsBack', 'st-gallery'),
							),
							'swapBlocks' 		=> array(
								'select' 		=> '',
								'name' 			=> __('swapBlocks', 'st-gallery'),
							),
							'cut' 				=> array(
								'select' 		=> '',
								'name' 			=> __('cut', 'st-gallery'),
							),
							'random' 			=> array(
								'select' 		=> '',
								'name' 			=> __('random', 'st-gallery'),
							),
							'randomSmart' 		=> array(
								'select' 		=> '',
								'name' 			=> __('randomSmart', 'st-gallery'),
							),
						);
						$this -> st_render_select('skitter_animation', __('Sets animation', 'st-gallery'), __('Animation:', 'st-gallery'), $skitter_animation);
						
						$skitter_theme = array(
									'default' 		=> array(
										'select' 	=> 'selected="selected"',
										'name' 		=> __('Default', 'st-gallery'),
									),
									'minimalist' 	=> array(
										'select' 	=> '',
										'name' 		=> __('Minimalist', 'st-gallery'),
									),
									'round' 		=> array(
										'select' 	=> '',
										'name' 		=> __('Round', 'st-gallery'),
									),
									'clean' 		=> array(
										'select' 	=> '',
										'name' 		=> __('Clean', 'st-gallery'),
									),
									'square' 		=> array(
										'select' 	=> '',
										'name' 		=> __('Square', 'st-gallery'),
									)
								);
						$this -> st_render_select('skitter_theme', __('Sets theme', 'st-gallery'), __('Theme:', 'st-gallery'), $skitter_theme);
					?>
					</div>
				</div>
				  
				</div>
				<?php $this -> StCopyright(); ?>
			</div>
	  	</form>
	  	
	<?php

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['savechanges'])) {
		$old_options_value = get_option('st_gallery_wp');
		$id = $_POST['id'];
		
		$new_options_value = array();
		
		$new_options_value[$id][name] 							= 	$_POST['name'];
		//===========  Settings ===========//
		$new_options_value[$id][settings][source] 				= 	$_POST['source'];
		$new_options_value[$id][settings][width] 				= 	$_POST['width'];
		$new_options_value[$id][settings][width_end] 			= 	$_POST['width_end'];
		$new_options_value[$id][settings][height] 				= 	$_POST['height'];
		$new_options_value[$id][settings][style] 				= 	$_POST['style'];
		//=========== Post Source  ===========//
		$new_options_value[$id][post][post_category] 			= 	$_POST['post_category'];
		$new_options_value[$id][post][order_by] 				= 	$_POST['order_by'];
		$new_options_value[$id][post][limit] 					= 	$_POST['limit'];
		$new_options_value[$id][post][first_img] 				= 	$_POST['first_img'];
		//=========== Library Source ===========//
		if (isset($_POST['image'])){
			$i = 1;
			foreach ($_POST['image'] as $key => $value) {
				$new_options_value[$id][images][$i][url] 		=	$this -> relativeURL($value['url']);
				$new_options_value[$id][images][$i][title]		= 	$value['title'];
				$new_options_value[$id][images][$i][caption]	=	$value['caption'];
				$new_options_value[$id][images][$i][url_2]		= 	$value['url_2'];
				$i++;
			}
		}
		//=========== Gallery Settings ===========//
		$new_options_value[$id][gallery][show_control]	 		= 	$_POST['show_control'];
		$new_options_value[$id][gallery][click_to_next]			= 	$_POST['click_to_next'];
		$new_options_value[$id][gallery][show_counter] 			= 	$_POST['show_counter'];
		$new_options_value[$id][gallery][show_prev_next] 		= 	$_POST['show_prev_next'];
		$new_options_value[$id][gallery][image_crop] 			= 	$_POST['image_crop'];
		$new_options_value[$id][gallery][imagePan] 				= 	$_POST['imagePan'];
		$new_options_value[$id][gallery][showThumb] 			= 	$_POST['showThumb'];
		$new_options_value[$id][gallery][thumb_crop] 			= 	$_POST['thumb_crop'];
		$new_options_value[$id][gallery][transition] 			= 	$_POST['transition'];
		$new_options_value[$id][gallery][transition_speed] 		= 	$_POST['transition_speed'];
		$new_options_value[$id][gallery][lightbox] 				= 	$_POST['lightbox'];
		$new_options_value[$id][gallery][image_delay] 			= 	$_POST['image_delay'];
		$new_options_value[$id][gallery][show_title_image]		= 	$_POST['show_title_image'];
		$new_options_value[$id][gallery][show_caption_image]	= 	$_POST['show_caption_image'];
		$new_options_value[$id][gallery][theme] 				= 	$_POST['theme'];
		$new_options_value[$id][gallery][responsive] 			= 	$_POST['responsive'];
		//=========== Skitter Settings ===========//
		$new_options_value[$id][skitter][auto_play]	 				= 	$_POST['skitter_auto_play'];
		$new_options_value[$id][skitter][stop_over]					= 	$_POST['skitter_stop_over'];
		$new_options_value[$id][skitter][interval] 					= 	$_POST['skitter_interval'];
		$new_options_value[$id][skitter][show_randomly] 			= 	$_POST['skitter_show_randomly'];
		$new_options_value[$id][skitter][controls] 					= 	$_POST['skitter_controls'];
		$new_options_value[$id][skitter][controls_position] 		= 	$_POST['skitter_controls_position'];
		$new_options_value[$id][skitter][progressbar] 				= 	$_POST['skitter_progressbar'];
		$new_options_value[$id][skitter][label] 					= 	$_POST['skitter_label'];
		$new_options_value[$id][skitter][labelAnimation] 			= 	$_POST['skitter_labelAnimation'];
		$new_options_value[$id][skitter][navigation] 				= 	$_POST['skitter_navigation'];
		$new_options_value[$id][skitter][navigation_position] 		= 	$_POST['skitter_navigation_position'];
		$new_options_value[$id][skitter][preview] 					= 	$_POST['skitter_preview'];
		$new_options_value[$id][skitter][next_prev]					= 	$_POST['skitter_next_prev'];
		$new_options_value[$id][skitter][enable_navigation_keys]	= 	$_POST['skitter_enable_navigation_keys'];
		$new_options_value[$id][skitter][hideTools] 				= 	$_POST['skitter_hideTools'];
		$new_options_value[$id][skitter][focus] 					= 	$_POST['skitter_focus'];
		$new_options_value[$id][skitter][focus_position] 			= 	$_POST['skitter_focus_position'];
		$new_options_value[$id][skitter][fullscreen] 				= 	$_POST['skitter_fullscreen'];
		$new_options_value[$id][skitter][animation] 				= 	$_POST['skitter_animation'];
		$new_options_value[$id][skitter][theme] 					= 	$_POST['skitter_theme'];
		
		
		if (!empty($old_options_value)) {
			$new_options_value = array_merge($old_options_value, $new_options_value);
		}
		
		update_option('st_gallery_wp', $new_options_value);
		echo '<meta http-equiv="refresh" content="0; URL=admin.php?page=st_gallery&action=edit&id=' . $id . '&message=1">';
	}
	?>
	</div>
	 	
	<?php 
	}

	/**
	 * Render Radio
	 */
	public function st_render_radio($id, $tip, $label, $value){
	 	$checktrue = '';
		$checkfalse = '';
	 	if ($value=='true'){ $checktrue = 'checked="checked"'; } else { $checkfalse = 'checked="checked"'; }
	 	echo '<div class="st-row">';
		echo '<div class="left">';
		echo '<label for="'.$id.'" title="'.$tip.'" class="tip">'.$label.'</label>';
		echo '</div>';
		echo '<div class="right">';
		echo '<input type="radio" name="'.$id.'" id="'.$id.'-true" value="true" '.$checktrue.'><label for="'.$id.'-true">'.__('Yes', 'st-gallery').' </label>&nbsp;';
		echo '<input type="radio" name="'.$id.'" id="'.$id.'-false" value="false" '.$checkfalse.'><label for="'.$id.'-false">'.__('No', 'st-gallery').'</label>';
		echo '</div>';
		echo '</div>';
	}
	 
	 /**
	  * Render Select
	  */
	public function st_render_select($id, $tip, $label, $valueArray){
	  	echo '<div class="st-row">';
		echo '<div class="left">';
		echo '<label for="'.$id.'" title="'.$tip.'" class="tip">'.$label.'</label>';
		echo '</div>';
		echo '<div class="right">';
		echo '<select id="'.$id.'" name="'.$id.'">';
		foreach ($valueArray as $key => $value) {
			echo '<option value="'.$key.'" '.$value['select'].'>'.$value['name'].'</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '</div>';
	 }
	
	/**
	 * Render textbox
	 */
	public function st_render_textbox($id, $tip, $label, $type, $value, $option, $text){
		echo '<div class="st-row">';
		echo '<div class="left">';
		echo '<label for="'.$id.'" title="'.$tip.'" class="tip">'.$label.'</label>';
		echo '</div>';
		echo '<div class="right">';
		echo '<input type="'.$type.'" name="'.$id.'" id="'.$id.'" value="'.$value.'" '.$option.'><span>'.$text.'</span>';
		echo '</div>';
		echo '</div>';
	}

	/*
	 * Copyright ST Gallery WP
	 */
	public function StCopyright(){?>
		<h3 class="box-title"><div class="dashicons dashicons-sos"></div> <?php _e('Abouts', 'st-gallery'); ?></h3>
		<div class="st-box">
			<div class="box-content">
				<div class="st-row">
					Hi,</br></br>We are Beautiful-Templates and we provide Wordpress Themes & Plugins, Joomla Templates & Extensions.</br>Thank you for using our products. Let drop us feedback to improve products & services.</br></br>Best regards,</br> Beautiful Templates Team
				</div>
			</div>
			<div class="st-row st-links">
				<div class="col col-8 links">
					<ul>
						<li>
							<a href="http://beautiful-templates.com/" target="_blank"> <?php _e('Home', 'st-gallery'); ?></a>
						</li>
						<li>
							<a href="http://beautiful-templates.com/amember/" target="_blank"> <?php _e('Submit Ticket', 'st-gallery'); ?></a>
						</li>
						<li>
							<a href="http://beautiful-templates.com/evo/forum/" target="_blank"> <?php _e('Forum', 'st-gallery'); ?></a>
						</li>
						<li>
							<?php add_thickbox(); ?>
							<a href="http://beautiful-templates.com/document/st-gallery-wp/index.html?TB_iframe=true&width=1000&height=600" class="thickbox"><?php _e('Document', 'st-gallery'); ?></a>
						</li>
					</ul>
				</div>
				<div class="col col-2 social">
					<ul>
						<li>
							<a href="https://www.facebook.com/beautifultemplates/" target="_blank"><div class="dashicons dashicons-facebook-alt"></div></a>
						</li>
						<li>
							<a href="https://twitter.com/cooltemplates/" target="_blank"><div class="dashicons dashicons-twitter"></div></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="st-box st-rss">
			<div class="box-content">
				<div class="st-row st_load_rss">
					<span class="spinner" style="display:block;"></span>
				</div>
			</div>
		</div>
	<?php 
	}
	
    protected function right(){
        return '<a style="display:none;" href="http://beautiful-templates.com">gallery wordpress plugin</a>';
    }
    
	/**
	 * Replace all character ' and " in string and remove html tag
	 **/
	public function st_replace($subject){
		$new_subject = explode('<span id="more-', $subject);
		$search = array('"',"'");
		$replace = array('&#34;',"&#39;");
		return strip_tags(str_replace($search, $replace, $new_subject[0]));
	}
	
	
}
	