<?php
/*
Author: Beautiful Templates
Author URI: http://beautiful-templates.com
License:  GPL2
*/
ob_start(); 
require_once 'classes/st-file.php';
require_once 'classes/st-widget.php';
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
				<a href="?page=st_gallery&action=add" class="add-new-h2"><div class="dashicons dashicons-plus"></div><?php _e('Add New', 'st-gallery'); ?></a> 
				<a href="?page=st_gallery&action=import" class="add-new-h2"><div class="dashicons dashicons-update"></div><?php _e('Import sample gallery', 'st-gallery'); ?></a>
			</h2> 
			<?php
				$this->st_message();
			?>
			<div class="st-left">
				<div class="st-allGallery">
					<div class="st-row listTitle">
						<div class="col name"><?php _e('Name', 'st-gallery'); ?></div>
						<div class="col shortcode"><?php _e('Shortcode', 'st-gallery'); ?></div>
						<div class="col actions"><?php _e('Actions', 'st-gallery'); ?></div>
					</div>
					<?php 
					if ($this->options){
						foreach ($this->options as $key => $value) { ?>
					<div class="st-row" id="<?php echo $key; ?>">
						<div class="col name"><a href="?page=st_gallery&action=edit&id=<?php echo $key; ?>"><?php echo $value['name']; ?></a></div>
						<div class="col shortcode"><input size="30" class="shortcode" type="text" value='[st-gallery id="<?php echo $key; ?>"]' onmouseover='this.select()'></div>
						<div class="col actions">
							<span class="action edit"><a href="?page=st_gallery&action=edit&id=<?php echo $key; ?>"><?php _e('Edit', 'st-gallery'); ?></a></span>
							<span class="action remove" id="<?php echo $key; ?>"><?php _e('Remove', 'st-gallery'); ?></span>
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
		<div class="st-skitter <?php echo $this -> valString($gallery['skitter']['theme'], 'default') ?>" id="<?php echo $newid; ?>">
			<?php if ( is_super_admin()) { ?>
				<div class="st-gallery-edit"><a href="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=st_gallery&action=edit&id=<?php echo $id; ?>" class="edit-link"><span class="dashicons dashicons-edit"></span> <?php _e('Edit', 'st-gallery'); ?></a></div>
			<?php }	?>
			<div class="box_skitter <?php echo $newid; ?>" style="background-color: <?php echo $this->valString($gallery['settings']['bgcolor'], '#000000') ?>; width: <?php echo $gallery['settings']['width'].$gallery['settings']['width_end'] ?>; height: <?php echo $gallery['settings']['height']?>px;">
				<ul>
				<?php 
				if (isset($gallery['settings']['source']) && esc_attr($gallery['settings']['source']) == 'Library'){
					foreach ($gallery['images'] as $i => $images) { ?>
						<li class="image" id="img-<?php echo $i; ?>">
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
								<li class="image img-<?php echo $img_id; ?>" >
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
					$('.<?php echo $newid; ?>').skitter({
						auto_play: 				<?php echo $this -> valBoolean($gallery['skitter']['auto_play'], true ); ?>, 
						stop_over: 				<?php echo $this -> valBoolean($gallery['skitter']['stop_over'], true ); ?>, 
						interval: 				<?php echo $this -> valInt($gallery['skitter']['interval'], 3000 ); ?>,
						show_randomly: 			<?php echo $this -> valBoolean($gallery['skitter']['show_randomly'], false); ?>,
						controls: 				<?php echo $this -> valBoolean($gallery['skitter']['controls'], true); ?>,
						controls_position: 		'<?php echo $this -> valString($gallery['skitter']['controls_position'], 'center') ?>',
						progressbar:			<?php echo $this -> valBoolean($gallery['skitter']['progressbar'], true); ?>, 
						label: 					<?php echo $this -> valBoolean($gallery['skitter']['label'], true); ?>, 
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
								preview: <?php echo $this -> valBoolean($gallery['skitter']['preview'], true); ?>,
								<?php
								break;
							
							default:
								
								break;
						}
						?>
						numbers_align: 			'<?php echo $this -> valString($gallery['skitter']['numbers_align'], 'center'); ?>', 
						navigation: 			<?php echo $this -> valBoolean($gallery['skitter']['next_prev'], true); ?>,
						enable_navigation_keys: <?php echo $this -> valBoolean($gallery['skitter']['enable_navigation_keys'], true); ?>, 
						hideTools: 				<?php echo $this -> valBoolean($gallery['skitter']['hideTools'], true); ?>,
						focus: 					<?php echo $this -> valBoolean($gallery['skitter']['focus'], true); ?>,
						focus_position:  		'<?php echo $this -> valString($gallery['skitter']['focus_position'], 'center'); ?>', 
						fullscreen: 			<?php echo $this -> valBoolean($gallery['skitter']['fullscreen'], false); ?>,
						animation: 				'<?php echo $this -> valString($gallery['skitter']['animation'], 'randomSmart'); ?>',
						theme: 					'<?php echo $this -> valString($gallery['skitter']['theme'], 'default'); ?>', 
					});
				});
			})(jQuery);
			</script>
		<?php
		echo $this->right();
	}
	
	public function valBoolean($value, $default){
		return (isset($value) ? $value : $default); 
	}
	public function valInt($value , $default){
		return (isset($value) ? abs(intval($value)) : abs(intval($default)) );
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
		<div class="st_gallery_wp st-gallery-wrapper <?php echo $gallery['gallery']['theme']; ?>" style="max-width: <?php echo $gallery['settings']['width'].$gallery['settings']['width_end'] ?>; height: <?php echo $gallery['settings']['height'] ?>px;">
				<?php if ( is_super_admin()) { ?>
					<div class="st-gallery-edit"><a href="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=st_gallery&action=edit&id=<?php echo $id; ?>" class="edit-link"><span class="dashicons dashicons-edit"></span> <?php _e('Edit', 'st-gallery'); ?></a></div>
				<?php }	?>
					<div id="<?php echo $newid; ?>" class="st-gallery-main <?php echo $gallery['gallery']['theme']; ?>" style="max-width: <?php echo $gallery['settings']['width'].$gallery['settings']['width_end'] ?>; height: <?php echo $gallery['settings']['height'] ?>px;">
				<?php 
					if (isset($gallery['settings']['source']) && esc_attr($gallery['settings']['source']) == 'Library'){
						foreach ($gallery['images'] as $i => $images) { ?>
							<div class="image" id="img-<?php echo $i; ?>">
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
								<div class="image" id="img-<?php echo $img_id; ?>">
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
				<div id="st-gallery-control" style="max-width: <?php echo $gallery['settings']['width'].$gallery['settings']['width_end'] ?>;">
					<ul class="st-control-text">
						<li><a class="<?php echo $newid; ?> action" href="#"><?php _e('Play', 'st-gallery'); ?></a></li>
						<li><a class="<?php echo $newid; ?> full" href="#"><?php _e('Fullscreen', 'st-gallery'); ?></a></li>
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
					$('.<?php echo $newid; ?>.action').html('Play');
		<?php	}else{ ?>
					$('.<?php echo $newid; ?>.action').addClass('active').html('Pause');
		<?php	}
			} ?>
			$(document).ready(function(){
				
					Galleria.run('#<?php echo $newid; ?>.<?php echo $this -> valString($gallery['gallery']['theme'], 'classic'); ?>',{
						theme: 		'<?php echo $this -> valString($gallery['gallery']['theme'], 'classic'); ?>',
						autoplay: 	<?php echo $this -> valInt($gallery['gallery']['image_delay'], 3000 ); ?>,
						clicknext: 	<?php echo $this -> valBoolean($gallery['gallery']['click_to_next'], false); ?>, 
						dataConfig: function(img) {
							return {
								title : $(img).attr('data-title'),
								description : $(img).attr('data-description'),
							};
						}, 
						extend: function() {
							var gallery = this;
							$('.<?php echo $newid; ?>.action').click(function() {
								event.preventDefault();
								
								if ($(this).hasClass('active')){
									gallery.pause();
									$(this).removeClass('active').html('Play');
								}else{
									gallery.play();
									$(this).addClass('active').html('Pause');
								}
								
							});
							$('.<?php echo $newid; ?>.full').click(function() {
								event.preventDefault();
								gallery.enterFullscreen();
							});
							
							var bgcolor = "<?php echo $this->valString($gallery['settings']['bgcolor'], '#000000') ?>";
							gallery.$('container').css('background', bgcolor);
							this.bind('fullscreen_enter', function(e) {
							    gallery.$('container').css('background', bgcolor);
							});
        
						},
						showCounter: 		<?php echo $this -> valBoolean($gallery['gallery']['show_counter'], true); ?>,
						showImagenav: 		<?php echo $this -> valBoolean($gallery['gallery']['show_prev_next'], true); ?>,
						imageCrop: 			<?php echo $this -> valBoolean($gallery['gallery']['image_crop'], true); ?>,
						thumbnails: 		<?php echo $this -> valBoolean($gallery['gallery']['showThumb'], true); ?>,
						thumbCrop: 			<?php echo $this -> valBoolean($gallery['gallery']['thumb_crop'], true); ?>,
						transition: 		'<?php echo $this -> valString($gallery['gallery']['transition'], 'fadeslide'); ?>',
						transitionSpeed: 	<?php echo $this -> valInt($gallery['gallery']['transition_speed'], 500); ?>,
						lightbox: 			<?php echo $this -> valBoolean($gallery['gallery']['lightbox'], true); ?>,
						imagePan: 			<?php echo $this -> valBoolean($gallery['gallery']['imagePan'], true); ?>,
						responsive: true,
						height:				<?php
												if ($gallery['gallery']['responsive']=='true'){
													echo '0.5';
												}else{
													echo $this -> valInt($gallery['settings']['height'], 500);
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
	public function galleryEditor($action){ ?>
		<div class="wrap st_gallery_wp">
	   	<h2>
	   	<?php
	   	settings_fields('st_option_group');  
	   	switch ($action) {
		   case 'add': 
		   		_e('Add Gallery', 'st-gallery');
			   	$id = uniqid();
			   break;
		   case 'edit': 
			   	_e('Edit Gallery', 'st-gallery');
			   	$id = trim($_GET['id']);
			   	if (isset($id)){
					foreach ($this->options as $key => $value) {
						if ($key==$id){
							$gallery = $value;
						}
					}
				}
			   break;
		   default: _e('Gallery Editor', 'st-gallery');
			   break;
	   }
	   ?>
	   		<a href="?page=st_gallery" class="add-new-h2"><div class="dashicons dashicons-arrow-left-alt"></div><?php _e('Back to the list', 'st-gallery'); ?></a>
	   		<a href="?page=st_gallery&action=add" class="add-new-h2"><div class="dashicons dashicons-plus"></div><?php _e('Add New', 'st-gallery'); ?></a>
	   	</h2> 
	   	<?php
			$this->st_message();
		?>
	  	<form method="post" action="" name="stForm" id="stForm">

			<div class="st-left">
				<div class="st-box name">
					<input name="id" type="hidden" value="<?php echo $id; ?>">
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
								if (isset($gallery['images'])){
									foreach ($gallery['images'] as $i => $images) { ?>
										<div class="col col-4" id="item-<?php echo $i; ?>">
											<div class="item">
												
												<div class="image">
													<input class="hiddenUrl" type="text" name="image[<?php echo $i; ?>][url]" value="<?php echo $images['url']; ?>" />
													<?php if ($images['url_2']){
														echo '<img src="' .$images['url_2'] . '" >';
													} else {
														echo '<img src="' . get_home_url().$images['url'] . '" >';
													} ?>
												</div>
												<div class="actions">
													<div class="action edit" id="<?php echo $i; ?>"><div class="dashicons dashicons-edit"></div> Edit</div>
													<div class="action st-remove" id="<?php echo $i; ?>"><div class="dashicons dashicons-trash"></div>Delete</div>
												</div>
												<div class="note">
													<div class="note-content">
														<div class="dashicons dashicons-sort"></div> <?php _e('Drag & drop to sort', 'st-gallery') ?>
													</div>
												</div>
												<div class="info" id="info-<?php echo $i; ?>">
													<label for="title"><?php _e('Title: ', 'st-gallery') ?></label><input type="text" name="image[<?php echo $i; ?>][title]" value="<?php echo $images['title']; ?>" />
													<label for="caption"><?php _e('Caption: ', 'st-gallery') ?></label><textarea rows="3" name="image[<?php echo $i; ?>][caption]"><?php echo $images['caption']; ?></textarea>
													<label for="url"><?php _e('Image URL: ', 'st-gallery') ?></label><input type="url" name="image[<?php echo $i; ?>][url_2]" value="<?php echo $images['url_2']; ?>" />
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
						        		<?php wp_category_checklist( 0, 0, isset($gallery['post']['post_category']) ? $gallery['post']['post_category'] : '' ,false, null, false); ?> 	
						        	</div>
				        		</div>
				        	</div>
				        	<?php
				        		$order_by = array(
									'date' 			=> 'Published Date',
									'modified' 		=> 'Modified Date',
									'rand' 			=> 'Random',
									'title' 		=> 'Post Title',
									'comment_count' => 'Popular',
								);
								$this -> st_render_select('order_by', 'Order by', 'Order by:', $order_by, isset($gallery['post']['order_by']) ? $gallery['post']['order_by'] : 'rand');
						  		$this -> st_render_textbox('limit', 'Posts Display', 'Posts Display:', 'number', isset($gallery['post']['limit']) ? $gallery['post']['limit'] : 10, 'min="1"', '(Posts)');
						  		$this -> st_render_radio('first_img', 'Using the first image of post content if without featured image', 'Using First Images:', isset($gallery['post']['first_img']) ? $gallery['post']['first_img'] : true );
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
								$phantram_selected = $px_selected = "";
								switch (isset($gallery['settings']['width_end']) ? $gallery['settings']['width_end'] : '%') {
									case '%': 		$phantram_selected 		= 'selected="selected"'; break;
									case 'px': 		$px_selected 			= 'selected="selected"'; break;
									default: 		$phantram_selected 		= 'selected="selected"'; break;
								}
								
								$width_end = '
								<select id="width_end" name="width_end">
									<option value="%" '.$phantram_selected.'>'.__('%', 'st-gallery').'</option>
									<option value="px" '.$px_selected.'>'.__('px', 'st-gallery').'</option>
								</select>';
								$this -> st_render_textbox('width', __('Manually set a gallery width', 'st-gallery'), __('Width:', 'st-gallery'), 'number', isset($gallery['settings']['width']) ? $gallery['settings']['width'] : 100 , 'min="1"', $width_end);
								$this -> st_render_textbox('height', __('Manually set a gallery height', 'st-gallery'), __('Height:', 'st-gallery'), 'number', isset($gallery['settings']['height']) ? $gallery['settings']['height'] : 500 , '', __('px', 'st-gallery'));
								$this -> st_render_textbox('bgcolor', __('Manually set background color', 'st-gallery'), __('Background Color:', 'st-gallery'), 'text', isset($gallery['settings']['bgcolor']) ? $gallery['settings']['bgcolor'] : '#000000', '', '');
								
								$source = array(
											'Library' 		=> __('Library', 'st-gallery'),
											'Post' 			=>  __('Post', 'st-gallery'),
										);
								$this -> st_render_select('source', __('Sets image source for gallery', 'st-gallery'), __('Source:', 'st-gallery'), $source , isset($gallery['settings']['source'])  ? $gallery['settings']['source'] : 'Library');
								
								$style = array(
									'gallery'	=> 'Gallery',
									'skitter'	=> 'Skitter',
								);
								$this -> st_render_select('style', 'Choose Type', 'Choose Type: ', $style , isset($gallery['settings']['style']) ? $gallery['settings']['style'] : 'gallery');
							?>
							</div>
						</div>
						<?php
							$skitter_display = "setting_hide";
							$gallery_display = "setting_display";
							switch (isset($gallery['settings']['style']) ? $gallery['settings']['style'] : 'gallery' ) {
								case 'skitter':
									$skitter_display = "setting_display";
									$gallery_display = "setting_hide";
									break;
								default:
									$skitter_display = "setting_hide";
									$gallery_display = "setting_display";
									break;
							}
						?>
						<h3 class="box-title gallery-setting <?php echo $gallery_display; ?>"><div class="dashicons dashicons-images-alt2"></div> <?php _e('Gallery Settings', 'st-gallery'); ?></h3>
					  	<div class="st-box <?php echo $gallery_display; ?>">
							<div class="box-content">
							<?php 
								
								$this -> st_render_radio('show_control', __('Show control', 'st-gallery'), __('Show Control:', 'st-gallery'), isset($gallery['gallery']['show_control']) ? $gallery['gallery']['show_control'] : true);
								$this -> st_render_radio('click_to_next', __('Click to next', 'st-gallery'), __('Click To Next:', 'st-gallery'), isset($gallery['gallery']['click_to_next']) ? $gallery['gallery']['click_to_next'] : false);
								$this -> st_render_radio('show_counter', __('Toggles the counter', 'st-gallery'), __('Show Counter:', 'st-gallery'), isset($gallery['gallery']['show_counter']) ? $gallery['gallery']['show_counter'] : true);
								$this -> st_render_radio('show_prev_next', __('Toggles the image navigation arrows', 'st-gallery'), __('Show Prev/Next:', 'st-gallery'), isset($gallery['gallery']['show_prev_next']) ? $gallery['gallery']['show_prev_next'] : true);
								$this -> st_render_radio('image_crop', __('Defines gallery will crop the image', 'st-gallery'), __('Image Crop:', 'st-gallery'), isset($gallery['gallery']['image_crop']) ? $gallery['gallery']['image_crop'] : true);
								$this -> st_render_radio('imagePan', __('Toggles the image pan effect', 'st-gallery'), __('Image Pan:', 'st-gallery'), isset($gallery['gallery']['imagePan']) ? $gallery['gallery']['imagePan'] : true);
								$this -> st_render_radio('showThumb', __('Toggles the thumbnail', 'st-gallery'), __('Show Thumbnails:', 'st-gallery'), isset($gallery['gallery']['showThumb']) ? $gallery['gallery']['showThumb'] : true);
								$this -> st_render_radio('thumb_crop', __('Defines gallery will crop the thumbnail', 'st-gallery'), __('Thumb Crop:', 'st-gallery'), isset($gallery['gallery']['thumb_crop']) ? $gallery['gallery']['thumb_crop'] : true);
								
								$transition = array(
											'fadeslide'	 	=> __('Fade Slide', 'st-gallery'),
											'flash' 		=> __('Flash', 'st-gallery'),
											'pulse' 		=> __('Pulse', 'st-gallery'),
											'slide' 		=> __('Slide', 'st-gallery'),
											'fade' 			=> __('Fade', 'st-gallery'),
										);
								$this -> st_render_select('transition', __('Defines what transition to use', 'st-gallery'), __('Transition:', 'st-gallery'), $transition, isset($gallery['gallery']['transition']) ? $gallery['gallery']['transition'] : 'fadeslide');
								$this -> st_render_textbox('transition_speed', __('Defines the speed of the transition', 'st-gallery'), __('Transition Speed:', 'st-gallery'), 'number', isset($gallery['gallery']['transition_speed']) ? $gallery['gallery']['transition_speed'] : 500, '', __('(100 = 1 sec)', 'st-gallery'));
								$this -> st_render_radio('lightbox', __('Zoom in when the user clicks on an image', 'st-gallery'), __('LightBox:', 'st-gallery'), isset($gallery['gallery']['lightbox']) ? $gallery['gallery']['lightbox'] : true);
								$this -> st_render_textbox('image_delay', __('Enter 0 to disable autoplay gallery', 'st-gallery'), __('Image Delay:', 'st-gallery'), 'number', isset($gallery['gallery']['image_delay']) ? $gallery['gallery']['image_delay'] : 3000 , 'min="0"', __('(1000 = 1 sec)', 'st-gallery'));
								$this -> st_render_radio('show_title_image', __('Toggles the title', 'st-gallery'), __('Show Title Image:', 'st-gallery'), isset($gallery['gallery']['show_title_image']) ? $gallery['gallery']['show_title_image'] : true);
								$this -> st_render_radio('show_caption_image', __('Toggles the caption', 'st-gallery'), __('Show Caption Image:', 'st-gallery'), isset($gallery['gallery']['show_caption_image']) ? $gallery['gallery']['show_caption_image'] : true);
								
								$theme = array(
											'classic' 		=> __('Classic', 'st-gallery'),
											'v2' 			=> __('Style 2', 'st-gallery'),
										);
								$this -> st_render_select('theme', __('Sets theme for gallery', 'st-gallery'), __('Theme:', 'st-gallery'), $theme, isset($gallery['gallery']['theme']) ? $gallery['gallery']['theme'] : 'classic');
								$this -> st_render_radio('responsive', __('Responsive', 'st-gallery'), __('Responsive:', 'st-gallery'), isset($gallery['gallery']['responsive']) ? $gallery['gallery']['responsive'] : false);
							?>
						</div>
					</div>
				
					<h3 class="box-title skitter-setting <?php echo $skitter_display; ?>"><div class="dashicons dashicons-format-image"></div> <?php _e('Skitter Settings', 'st-gallery'); ?></h3>
					<div class="st-box <?php echo $skitter_display; ?>">
						<div class="box-content">
				  		<?php 
						
							$this -> st_render_radio('skitter_auto_play', __('Auto play', 'st-gallery'), __('Auto Play:', 'st-gallery'), isset($gallery['skitter']['auto_play']) ? $gallery['skitter']['auto_play'] : true);
							$this -> st_render_radio('skitter_stop_over', __('Stop animation to move mouse over it', 'st-gallery'), __('Stop Over:', 'st-gallery'), isset($gallery['skitter']['stop_over']) ? $gallery['skitter']['stop_over'] : true);
							$this -> st_render_textbox('skitter_interval', __('Interval between transitions', 'st-gallery'), __('Interval:', 'st-gallery'), 'number', isset($gallery['skitter']['interval']) ? $gallery['skitter']['interval'] : 3000 , 'min="0"', __('(1000 = 1 sec)', 'st-gallery'));
							$this -> st_render_radio('skitter_show_randomly', __('Toggles the randomly sliders', 'st-gallery'), __('Show Randomly:', 'st-gallery'), isset($gallery['skitter']['show_randomly']) ? $gallery['skitter']['show_randomly'] : false);
							$this -> st_render_radio('skitter_controls', __('Show control', 'st-gallery'), __('Show Control:', 'st-gallery'), isset($gallery['skitter']['controls']) ? $gallery['skitter']['controls'] : true);
							
							$skitter_controls_position = array(
										'center'	 	=> __('Center', 'st-gallery'),
										'leftTop' 		=> __('Left Top', 'st-gallery'),
										'rightTop' 		=> __('Right Top', 'st-gallery'),
										'leftBottom' 	=> __('Left Bottom', 'st-gallery'),
										'rightBottom' 	=> __('Right Bottom', 'st-gallery'),
									);
							$this -> st_render_select('skitter_controls_position', __('Defines controls position', 'st-gallery'), __('Controls Position:', 'st-gallery'), $skitter_controls_position, isset($gallery['skitter']['controls_position']) ? $gallery['skitter']['controls_position'] : 'center');
							$this -> st_render_radio('skitter_progressbar', __('Show/hide progress bar', 'st-gallery'), __('Progress Bar:', 'st-gallery'), isset($gallery['skitter']['progressbar']) ? $gallery['skitter']['progressbar'] : true);
							$this -> st_render_radio('skitter_label', __('Toggles the title', 'st-gallery'), __('Show Title:', 'st-gallery'), isset($gallery['skitter']['label']) ? $gallery['skitter']['label'] : true);
							
							$labelAnimation = array(
										'slideUp'	 	=> __('Slide Up', 'st-gallery'),
										'left' 			=> __('Left', 'st-gallery'),
										'right' 		=> __('Right', 'st-gallery'),
										'fixed' 		=> __('Fixed', 'st-gallery'),
									);
							$this -> st_render_select('skitter_labelAnimation', __('Defines title animation', 'st-gallery'), __('Title Animation:', 'st-gallery'), $labelAnimation, isset($gallery['skitter']['labelAnimation']) ? $gallery['skitter']['labelAnimation'] : 'slideUp');
							
							$navigation = array(
										'thumbs' 		=> __('Thumbnails', 'st-gallery'),
										'numbers' 		=> __('Numbers', 'st-gallery'),
										'dots' 			=> __('Dots', 'st-gallery'),
									);
							$this -> st_render_select('skitter_navigation', __('Sets navigation style', 'st-gallery'), __('Navigation Style:', 'st-gallery'), $navigation, isset($gallery['skitter']['navigation']) ? $gallery['skitter']['navigation'] : 'dots');
							
							$navigation_position = array(
										'center' 		=> __('Center', 'st-gallery'),
										'left' 			=> __('Left', 'st-gallery'),
										'right' 		=> __('Right', 'st-gallery'),
									);
							$this -> st_render_select('skitter_navigation_position', __('Sets navigation position', 'st-gallery'), __('Navigation Position:', 'st-gallery'), $navigation_position , isset($gallery['skitter']['navigation_position']) ? $gallery['skitter']['navigation_position'] : 'center');
							$this -> st_render_radio('skitter_preview', __('Thumbnail previews when you hover over the dots', 'st-gallery'), __('Preview:', 'st-gallery'), isset($gallery['skitter']['preview']) ? $gallery['skitter']['preview'] : true);
							$this -> st_render_radio('skitter_next_prev', __('Show the navigation buttons next/previous', 'st-gallery'), __('Show Next/Prev:', 'st-gallery'), isset($gallery['skitter']['next_prev']) ? $gallery['skitter']['next_prev'] : true);
							$this -> st_render_radio('skitter_enable_navigation_keys', __('Using key < > to previous/next sliders', 'st-gallery'), __('Navigation Keys:', 'st-gallery'), isset($gallery['skitter']['enable_navigation_keys']) ? $gallery['skitter']['enable_navigation_keys'] : true);
							$this -> st_render_radio('skitter_hideTools', __('Auto-hide the navigation buttons, controls, thumbs', 'st-gallery'), __('Auto hide:', 'st-gallery'), isset($gallery['skitter']['hideTools']) ? $gallery['skitter']['hideTools'] : 'true');
							$this -> st_render_radio('skitter_focus', __('Focus slideshow', 'st-gallery'), __('Focus Slideshow:', 'st-gallery'), isset($gallery['skitter']['focus']) ? $gallery['skitter']['focus'] : true);
							
							$focus_position = array(
										'center' 		=> __('Center', 'st-gallery'),
										'leftTop' 		=> __('Left Top', 'st-gallery'),
										'rightTop' 		=> __('Right Top', 'st-gallery'),
										'leftBottom' 	=> __('Left Bottom', 'st-gallery'),
										'rightBottom' 	=> __('Right Bottom', 'st-gallery'),
									);
							$this -> st_render_select('skitter_focus_position', __('Sets position for focus slideshow button', 'st-gallery'), __('Focus Position:', 'st-gallery'), $focus_position, isset($gallery['skitter']['focus_position']) ? $gallery['skitter']['focus_position'] : 'center');
							$this -> st_render_radio('skitter_fullscreen', __('Sets fullscreen', 'st-gallery'), __('Fullscreen:', 'st-gallery'), isset($gallery['skitter']['fullscreen']) ? $gallery['skitter']['fullscreen'] : false);
							
							$skitter_animation = array(
										'cube' 				=> 'cube',
										'cubeRandom' 		=> 'cubeRandom',
										'block' 			=> 'block',
										'cubeStop' 			=> 'cubeStop',
										'cubeHide' 			=> 'cubeHide',
										'cubeSize' 			=> 'cubeSize',
										'horizontal' 		=> 'horizontal',
										'showBars' 			=> 'showBars',
										'showBarsRandom'	=> 'showBarsRandom',
										'tube' 				=> 'tube',
										'fade' 				=> 'fade',
										'fadeFour' 			=> 'fadeFour',
										'paralell' 			=> 'paralell',
										'blind' 			=> 'blind',
										'blindHeight' 		=> 'blindHeight',
										'blindWidth' 		=> 'blindWidth',
										'directionTop' 		=> 'directionTop',
										'directionBottom' 	=> 'directionBottom',
										'directionRight'	=> 'directionRight',
										'directionLeft' 	=> 'directionLeft',
										'cubeStopRandom' 	=> 'cubeStopRandom',
										'cubeSpread' 		=> 'cubeSpread',
										'cubeJelly' 		=> 'cubeJelly',
										'glassCube' 		=> 'glassCube',
										'glassBlock' 		=> 'glassBlock',
										'circles' 			=> 'circles', 
										'circlesInside' 	=> 'circlesInside',
										'circlesRotate' 	=> 'circlesRotate',
										'cubeShow' 			=> 'cubeShow',
										'upBars' 			=> 'upBars',
										'downBars' 			=> 'downBars',
										'hideBars' 			=> 'hideBars',
										'swapBars' 			=> 'swapBars',
										'swapBarsBack' 		=> 'swapBarsBack',
										'swapBlocks' 		=> 'swapBlocks',
										'cut' 				=> 'cut',
										'random' 			=> 'random',
										'randomSmart' 		=> 'randomSmart',
									);
							$this -> st_render_select('skitter_animation', __('Sets animation', 'st-gallery'), __('Animation:', 'st-gallery'), $skitter_animation , isset($gallery['skitter']['animation']) ? $gallery['skitter']['animation'] : 'randomSmart');
							
							$skitter_theme = array(
										'default' 		=> __('Default', 'st-gallery'),
										'minimalist' 	=> __('Minimalist', 'st-gallery'),
										'round' 		=> __('Round', 'st-gallery'),
										'clean' 		=> __('Clean', 'st-gallery'),
										'square' 		=> __('Square', 'st-gallery'),
									);
							$this -> st_render_select('skitter_theme', __('Sets theme', 'st-gallery'), __('Theme:', 'st-gallery'), $skitter_theme, isset($gallery['skitter']['theme']) ? $gallery['skitter']['theme'] : 'default');
						?>
						</div>
					</div>
					
					
				</div>
				
				<?php $this -> StCopyright(); ?>
				
			</div>
	  	</form>
	
	<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['savechanges'])){
		
		$m = 1;
		
		if ($action=='edit'){
			unset($this->options[$id]);
			update_option('st_gallery_wp',$this->options);
			$m = 2;
		}
		$old_options_value = get_option('st_gallery_wp');
		
		$new_options_value = array();
		
		$new_options_value[$id]['name'] 							= 	$_POST['name'];
		//===========  Settings ===========//
		$new_options_value[$id]['settings']['source'] 				= 	$_POST['source'];
		$new_options_value[$id]['settings']['width'] 				= 	$_POST['width'];
		$new_options_value[$id]['settings']['width_end'] 			= 	$_POST['width_end'];
		$new_options_value[$id]['settings']['height'] 				= 	$_POST['height'];
		$new_options_value[$id]['settings']['bgcolor'] 				= 	$_POST['bgcolor'];
		$new_options_value[$id]['settings']['style'] 				= 	$_POST['style'];
		//=========== Post Source  ===========//
		isset($_POST['post_category']) ? $new_options_value[$id]['post']['post_category'] = $_POST['post_category'] : '';
		$new_options_value[$id]['post']['order_by'] 				= 	$_POST['order_by'];
		$new_options_value[$id]['post']['limit'] 					= 	$_POST['limit'];
		$new_options_value[$id]['post']['first_img'] 				= 	$_POST['first_img'];
		//=========== Library Source ===========//
		if (isset($_POST['image'])){
			$i = 0;
			foreach ($_POST['image'] as $key => $value) {
				++$i;
				$new_options_value[$id]['images'][$i]['url'] 		=	$this -> relativeURL($value['url']);
				$new_options_value[$id]['images'][$i]['title']		= 	$value['title'];
				$new_options_value[$id]['images'][$i]['caption']	=	$value['caption'];
				$new_options_value[$id]['images'][$i]['url_2']		= 	$value['url_2'];
			}
		}
		//=========== Gallery Settings ===========//
		$new_options_value[$id]['gallery']['show_control']	 		= 	$_POST['show_control'];
		$new_options_value[$id]['gallery']['click_to_next']			= 	$_POST['click_to_next'];
		$new_options_value[$id]['gallery']['show_counter'] 			= 	$_POST['show_counter'];
		$new_options_value[$id]['gallery']['show_prev_next'] 		= 	$_POST['show_prev_next'];
		$new_options_value[$id]['gallery']['image_crop'] 			= 	$_POST['image_crop'];
		$new_options_value[$id]['gallery']['imagePan'] 				= 	$_POST['imagePan'];
		$new_options_value[$id]['gallery']['showThumb'] 			= 	$_POST['showThumb'];
		$new_options_value[$id]['gallery']['thumb_crop'] 			= 	$_POST['thumb_crop'];
		$new_options_value[$id]['gallery']['transition'] 			= 	$_POST['transition'];
		$new_options_value[$id]['gallery']['transition_speed'] 		= 	$_POST['transition_speed'];
		$new_options_value[$id]['gallery']['lightbox'] 				= 	$_POST['lightbox'];
		$new_options_value[$id]['gallery']['image_delay'] 			= 	$_POST['image_delay'];
		$new_options_value[$id]['gallery']['show_title_image']		= 	$_POST['show_title_image'];
		$new_options_value[$id]['gallery']['show_caption_image']	= 	$_POST['show_caption_image'];
		$new_options_value[$id]['gallery']['theme'] 				= 	$_POST['theme'];
		$new_options_value[$id]['gallery']['responsive'] 			= 	$_POST['responsive'];
		//=========== Skitter Settings ===========//
		$new_options_value[$id]['skitter']['auto_play']	 				= 	$_POST['skitter_auto_play'];
		$new_options_value[$id]['skitter']['stop_over']					= 	$_POST['skitter_stop_over'];
		$new_options_value[$id]['skitter']['interval'] 					= 	$_POST['skitter_interval'];
		$new_options_value[$id]['skitter']['show_randomly'] 			= 	$_POST['skitter_show_randomly'];
		$new_options_value[$id]['skitter']['controls'] 					= 	$_POST['skitter_controls'];
		$new_options_value[$id]['skitter']['controls_position'] 		= 	$_POST['skitter_controls_position'];
		$new_options_value[$id]['skitter']['progressbar'] 				= 	$_POST['skitter_progressbar'];
		$new_options_value[$id]['skitter']['label'] 					= 	$_POST['skitter_label'];
		$new_options_value[$id]['skitter']['labelAnimation'] 			= 	$_POST['skitter_labelAnimation'];
		$new_options_value[$id]['skitter']['navigation'] 				= 	$_POST['skitter_navigation'];
		$new_options_value[$id]['skitter']['navigation_position'] 		= 	$_POST['skitter_navigation_position'];
		$new_options_value[$id]['skitter']['preview'] 					= 	$_POST['skitter_preview'];
		$new_options_value[$id]['skitter']['next_prev']					= 	$_POST['skitter_next_prev'];
		$new_options_value[$id]['skitter']['enable_navigation_keys']	= 	$_POST['skitter_enable_navigation_keys'];
		$new_options_value[$id]['skitter']['hideTools'] 				= 	$_POST['skitter_hideTools'];
		$new_options_value[$id]['skitter']['focus'] 					= 	$_POST['skitter_focus'];
		$new_options_value[$id]['skitter']['focus_position'] 			= 	$_POST['skitter_focus_position'];
		$new_options_value[$id]['skitter']['fullscreen'] 				= 	$_POST['skitter_fullscreen'];
		$new_options_value[$id]['skitter']['animation'] 				= 	$_POST['skitter_animation'];
		$new_options_value[$id]['skitter']['theme'] 					= 	$_POST['skitter_theme'];
		
		
		if(!empty($old_options_value)){
			$new_options_value = array_merge($old_options_value,$new_options_value);
		}
		
		update_option('st_gallery_wp', $new_options_value);
		echo '<meta http-equiv="refresh" content="0; URL=admin.php?page=st_gallery&action=edit&id='.$id.'&message='.$m.'">';
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
	public function st_render_select($id, $tip, $label, $valueArray, $default){
	  	echo '<div class="st-row">';
		echo '<div class="left">';
		echo '<label for="'.$id.'" title="'.$tip.'" class="tip">'.$label.'</label>';
		echo '</div>';
		echo '<div class="right">';
		echo '<select id="'.$id.'" name="'.$id.'">';
		foreach ($valueArray as $value => $name) {
			echo '<option value="'.$value.'" ';
			if ($value==$default) echo 'selected';
			echo '>'.$name.'</option>';
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
?>