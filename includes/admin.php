<style>
@import url("http://fonts.googleapis.com/css?family=Open+Sans:400,600,700");
@import url("http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css");
*, *:before, *:after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
}

body {
  font: 14px/1 'Open Sans', sans-serif;
  color: #555;
  background: #eee;
}
.form-table th{
	width: 300px!important;
}
h1 {
  padding: 50px 0;
  font-weight: 400;
  text-align: center;
}

p {
  margin: 0 0 20px;
  line-height: 1.5;
}

main {
  min-width: 320px;
  width: 90%;
  padding: 50px;
  margin: 0 auto;
  background: #fff;
}

section {
  display: none;
  padding: 20px 0 0;
  border-top: 1px solid #ddd;
}

label {
  display: inline-block;
  margin: 0 0 -1px;
  padding: 15px 25px;
  font-weight: 600;
  text-align: center;
  color: #bbb;
  border: 1px solid transparent;
}


input:checked + label {
  color: #555;
  border: 1px solid #ddd;
  border-top: 2px solid orange;
  border-bottom: 1px solid #fff;
}
input[name="tabs"] {
	display:none;
}
input[type=text], input[type=search], input[type=radio], input[type=tel], input[type=time], input[type=url], input[type=week], input[type=password], input[type=color], input[type=date], input[type=datetime], input[type=datetime-local], input[type=email], input[type=month], input[type=number], select, textarea{
	width:100%;
}
#wsuc_cache:checked ~ #wsuc_cache_content,
#wsuc_css:checked ~ #wsuc_css_content,
#wsuc_js:checked ~ #wsuc_js_content,
#wsuc_opt_img:checked ~ #wsuc_opt_img_content,
#wsuc_opt_img_combin:checked ~ #wsuc_opt_img_combin_content, 
#wsuc_ll_img:checked ~ #wsuc_ll_img_content {
  display: block;
}
section p span{margin-left:20px;}
@media screen and (max-width: 650px) {
  label {
    font-size: 0;
  }

  label:before {
    margin: 0;
    font-size: 18px;
  }
}
@media screen and (max-width: 400px) {
  label {
    padding: 15px;
  }
}
.form-table td {
    vertical-align: top;
}
    </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <h1>Wp Speedup Cache Settings</h1>
  <?php 
  
 
	function createimageinstantly($imges=array()){
		$x=$y=300;
		
		$uploads = wp_upload_dir();
		
	
		//header('Content-Type: image/png');
		//$targetFolder = '/gw/media/uploads/processed/';
		//$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
		$targetPath = $uploads['basedir'];		
		
		if(!empty($imges)){
			$height_array = array();
			$max_width = 0;
			$images_detail = array();
			foreach($imges as $key=>$img){
				$size = getimagesize($img);
				//$size2 = getimagesize($img2);
				//$size3 = getimagesize($img3);			
				//$height_array = array($size1[1], $size2[1] ,$size3[1]);				
				//$max_width = ($size1[0]+$size2[0]+$size3[0])+60 ;	
				$size['src'] = $img ;				
				$height_array[] = $size[1];				
				$max_width = $max_width+$size[0]+20 ;
				$images_detail[$key] = 	$size ;
			}
			$max_height = max($height_array);
			
			
			$outputImage = imagecreatetruecolor( $max_width, $max_height);

			// set background to white
			$white = imagecolorallocate($outputImage, 0, 0, 0);
			//imagefill($outputImage, 0, 0, $white);
			imagecolortransparent($outputImage, $white);
			
			/*
			$first = imagecreatefrompng($img1);
			$second = imagecreatefrompng($img2);
			$third = imagecreatefrompng($img3);

			//imagecopyresized ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
			
			
			imagecopyresized($outputImage,$first,0,0,0,0, $size1[0], $size1[1],$size1[0], $size1[1]);
			
			imagecopyresized($outputImage,$second,($size1[0]+20),0,0,0, $size2[0], $size2[1], $size2[0], $size2[1]);
			
			imagecopyresized($outputImage,$third,($size1[0]+$size2[1]+40),0,0,0, $size3[0], $size3[1],$size3[0], $size3[1]); */
			
						
			$new_coordinates = 0;
			$new_images_detail = array();
			foreach($images_detail as $key=>$img){					
				$new_image = imagecreatefrompng($img['src']);
				imagecopyresized($outputImage,$new_image,$new_coordinates,0,0,0, $img[0], $img[1],$img[0], $img[1]);
				$new_coordinates = $new_coordinates+$img[0]+20;					
			}			
			
			// Add the text
			//imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
			//$white = imagecolorallocate($im, 255, 255, 255);
			$text = 'School Name Here';
			$font = 'OldeEnglish.ttf';
			//imagettftext($outputImage, 32, 0, 150, 150, $white, $font, $text);
			
			$wp_upload_dir = wp_upload_dir();
			
			$image_name = 'combine_image_'.round(microtime(true)).'.png';
			$filename = $wp_upload_dir['path'].'/'.$image_name;
			imagepng($outputImage, $filename);			
			
			// create attachment post			
			$filetype = wp_check_filetype( basename( $image_name ), null );

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_title(preg_replace( '/\.[^.]+$/', '', basename( $filename ) )),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);
			
			
			$attach_id = wp_insert_attachment( $attachment, $filename, 0 );
			// Include image.php
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

			// Assign metadata to attachment
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
			update_option( 'wnw_speedup_combine_image_id', $attach_id );
			
			imagedestroy($outputImage);
		}
	}
  
  
  
  
  function new_optimize_image($width,$url){
		$width = $width < 1920 ? $width : 1920;
		//echo 'https://w3speedup.com/optimize/basic.php?width='.$width.'&url='.urlencode($url);
		return get_curl_url('https://w3speedup.com/optimize/basic.php?width='.$width.'&url='.urlencode($url));
	}
  function get_ws_optimize_image($image_url, $image_width){
		global $document_root;
		$result = 'invalid image' ;
		if(!empty($image_url)){
			$url_array = parse_url($image_url);
			//echo $document_root.$url_array['path'] ;
			
			$image_size = !empty($image_width) ? array($image_width) : getimagesize($document_root.$url_array['path']);
			$optmize_image = new_optimize_image($image_size[0],$image_url);
			$optimize_image_size = @imagecreatefromstring($optmize_image);
			//exit;
			if(empty($optimize_image_size)){
				$result = 'invalid image';
			}else{    
				$image_type = array('gif','jpg','png','jpeg');
				$type = explode('.',$image_url);
				$type = array_reverse($type);
				if(in_array($type[0],$image_type)){
					rename($document_root.$url_array['path'],$document_root.$url_array['path'].'org.'.$type[0]);
					file_put_contents($document_root.$url_array['path'],$optmize_image);
					chmod($document_root.$url_array['path'], 0775);
					//$result = $document_root.$url_array['path'];
					$result = 'Image Optimized';
				}
				
			}
		}
		return $result ;
	}
  
  
  
  
  $result='';
	if(isset($_POST['ws_action']) && $_POST['ws_action'] == 'cache'){
		unset($_POST['ws_action']);
		foreach($_POST as $key=>$value){
			$array[$key] = $value;
		}
		
		update_option( 'wnw_wp_speedup_option', $array );		
		//print_r($result);
	}
	 $result = get_option( 'wnw_wp_speedup_option', true );
	 
	/*if(isset($_POST['ws_action']) && $_POST['ws_action'] == 'image_fields'){
		$array['optimiz_images'] = $_POST['optimiz_images'];		
		update_option( 'wnw_speedup_opti_images', $array );	
	}
	$opti_images = get_option( 'wnw_speedup_opti_images' ); */
	if(isset($_POST['ws_action']) && $_POST['ws_action'] == 'combine_image_save'){
		$c_array['combine_images'] = $_POST['combine_images'];		
		update_option( 'wnw_speedup_combine_images', $c_array );
		
		$c_images_src = array();
		if(isset($c_array['combine_images']) && !empty($c_array['combine_images'])){
			foreach($c_array['combine_images'] as $value){ 
				if(!empty($value['src'])){
					$c_images_src[] = $value['src'] ;
				}
			}		
		}
				
		if(!empty($c_images_src)){		
			createimageinstantly($c_images_src);
		}
		
	}
	
	
	$combine_images = get_option( 'wnw_speedup_combine_images' );
	
	//print_r($opti_images);
	
	
	
	
	
  ?>
<main>
  
		<input id="wsuc_cache" type="radio" name="tabs" checked>
		<label for="wsuc_cache">Caching</label>
		<input id="wsuc_opt_img" type="radio" name="tabs">
		<label for="wsuc_opt_img">Images Optimization</label>
		<input id="wsuc_opt_img_combin" type="radio" name="tabs">
		<label for="wsuc_opt_img_combin">Images Combined</label>		
		
		<!--<input id="wsuc_css" type="radio" name="tabs">
		<label for="wsuc_css">Css</label>  

		<input id="wsuc_js" type="radio" name="tabs">
		<label for="wsuc_js">Js</label>

		<input id="wsuc_opt_img" type="radio" name="tabs">
		<label for="wsuc_opt_img">Images</label>

		<input id="wsuc_ll_img" type="radio" name="tabs">
		<label for="wsuc_ll_img">Lazyload Images</label>-->
   	
	<section id="wsuc_cache_content">
		<form method="post">
			<table class="form-table">

				<tbody>
					<tr>
						<th scope="row">Turn ON optimization</th>
						<td>
							<input type="checkbox" name="status" <?php if (!empty($result['status']) && $result['status'] == "on") echo "checked";?> >
							<input type="hidden" name="ws_action" value="cache">
						</td>
					</tr>
					<tr>
						<th scope="row">CDN url</th>
						<td><input type="text" name="cdn" placeholder="Pleas Enter CDN url here" value="<?php if(!empty($result['cdn'])) echo $result['cdn'];?>"></td>
					</tr>
					<tr>
						<th scope="row">Enable js minification</th>
						<td><input type="checkbox" name="js" <?php if (!empty($result['js']) && $result['js'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row">Enable css minification</th>
						<td><input type="checkbox" name="css" <?php if (!empty($result['css']) && $result['css'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row">Separate css cache files for mobile</th>
						<td><input type="checkbox" name="css_mobile" <?php if (!empty($result['css_mobile']) && $result['css_mobile'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row">Combine Google fonts</th>
						<td><input type="checkbox" name="google_fonts" <?php if (!empty($result['google_fonts']) && $result['google_fonts'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
					<th scope="row">Preload css for above the fold content</th>
					<td><textarea name="preload_css" rows="10" cols="16" placeholder="Please Enter Preload css here" ><?php if (!empty($result['preload_css'])) echo $result['preload_css'];?></textarea></td>
					</tr>
					<tr>
					<tr>
						<th scope="row">Exclude css from minification</th>
						<td><textarea name="exclude_css" rows="10" cols="16" placeholder="Please Enter Preload css here" ><?php if (!empty($result['exclude_css'])) echo $result['exclude_css'];?></textarea></td>
					</tr>
					<tr>
						<th scope="row">Enable lazy Loading Images</th>
						<td><input type="checkbox" name="lazy_load" <?php if (!empty($result['lazy_load']) && $result['lazy_load'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
					<tr>
						<th scope="row">Enable lazy Loading Iframe</th>
						<td><input type="checkbox" name="lazy_load_iframe" <?php if (!empty($result['lazy_load_iframe']) && $result['lazy_load_iframe'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
					<tr>
						<th scope="row">Enable lazy Loading Video</th>
						<td><input type="checkbox" name="lazy_load_video" <?php if (!empty($result['lazy_load_video']) && $result['lazy_load_video'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
					<tr>
						<th scope="row">Exclude images from Lazy Loading</th>
						<td><textarea name="exclude_lazy_load" rows="10" cols="16" placeholder="Please Enter matching text of the image here" ><?php if (!empty($result['exclude_lazy_load'])) echo $result['exclude_lazy_load'];?></textarea></td>
					</tr>
					<tr>
						<th scope="row">Exclude Javascript from combine</th>
						<td><textarea name="exclude_javascript" rows="10" cols="16" placeholder="Please Enter matching text of the javascript here" ><?php if (!empty($result['exclude_javascript'])) echo $result['exclude_javascript'];?></textarea></td>
						<td class="top">Defer&nbsp;<input type="checkbox" name="exclude_js_defer" <?php if (!empty($result['exclude_js_defer']) && $result['exclude_js_defer'] == "on") echo "checked";?> ></td>
					</tr>
                                        <tr>
						<th scope="row">Exclude Javascript from combine 2</th>
						<td><textarea name="exclude_custom_javascript" rows="10" cols="16" placeholder="Please Enter matching text of the javascript here" ><?php if (!empty($result['exclude_custom_javascript'])) echo $result['exclude_custom_javascript'];?></textarea></td>
						<td class="top">Defer&nbsp;<input type="checkbox" name="exclude_custom_js_defer" <?php if (!empty($result['exclude_custom_js_defer']) && $result['exclude_custom_js_defer'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row">Custom Javascript</th>
						<td><textarea name="custom_javascript" rows="10" cols="16" placeholder="Please Enter matching text of the javascript here" ><?php if (!empty($result['custom_javascript'])) echo stripslashes($result['custom_javascript']);?></textarea></td>
						<td class="top">Load as file&nbsp;<input type="checkbox" name="custom_javascript_file" <?php if (!empty($result['custom_javascript_file']) && $result['custom_javascript_file'] == "on") echo "checked";?> >&nbsp;&nbsp;Defer&nbsp;<input type="checkbox" name="custom_javascript_defer" <?php if (!empty($result['custom_javascript_defer']) && $result['custom_javascript_defer'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row">Exclude Inner Javascript from combine</th>
						<td><textarea name="exclude_inner_javascript" rows="10" cols="16" placeholder="Please Enter matching text of the inner javascript here" ><?php if (!empty($result['exclude_inner_javascript'])) echo $result['exclude_inner_javascript'];?></textarea></td>
					</tr>
					<tr>
					<tr>
						<th scope="row">Lazy Load inner Js</th>
						<td><textarea name="lazy_load_inner_js" rows="10" cols="16" placeholder="Please Enter matching text of the inner javascript here" ><?php if(!empty($result['lazy_load_inner_js'])) echo $result['lazy_load_inner_js'];?></textarea></td>
					</tr>
					<tr>
						<th scope="row">Load Combined Css</th>
						<td><select name="load_combined_css">
						<option value="on_page_load" <?php echo !empty($result['load_combined_css']) && $result['load_combined_css'] == 'on_page_load' ? 'selected' : '' ;?>>On Page Load</option>
						<option value="after_page_load" <?php echo !empty($result['load_combined_css']) && $result['load_combined_css'] == 'after_page_load' ? 'selected' : '' ;?>>After Page Load</option>
						</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Load Combined Js</th>
						<td><select name="load_combined_js">
						<option value="on_page_load" <?php echo !empty($result['load_combined_js']) && $result['load_combined_js'] == 'on_page_load' ? 'selected' : '' ;?>>On Page Load</option>
						<option value="after_page_load" <?php echo !empty($result['load_combined_js']) && $result['load_combined_js'] == 'after_page_load' ? 'selected' : '' ;?>>After Page Load</option>
						</select>
						</td>
						
					</tr>
					<tr>
					<th scope="row">Delay External Js by</th>
						<td>
						<input type="number" step="any" name="js_delay_load" value="<?php echo !empty($result['js_delay_load']) ? $result['js_delay_load'] : 10 ;?>" >
						</td>
					</tr>
					<tr>
					<th scope="row">Delay Internal Js by</th>
						<td>
						<input type="number" step="any" name="internal_js_delay_load" value="<?php echo !empty($result['internal_js_delay_load']) ? $result['internal_js_delay_load'] : 10 ;?>" >
						</td>
					</tr>
					<tr>
					<th scope="row">Delay internal css by</th>
						<td>
						<input type="number" step="any" name="internal_css_delay_load" value="<?php echo !empty($result['internal_css_delay_load']) ? $result['internal_css_delay_load'] : 10 ;?>" >
						</td>
					</tr>
					<th scope="row">Delay google fonts by</th>
						<td>
						<input type="number" step="any" name="google_fonts_delay_load" value="<?php echo !empty($result['google_fonts_delay_load']) ? $result['google_fonts_delay_load'] : 2 ;?>" >
						</td>
					</tr>
					<tr>
						<th scope="row">Load Main Css as Url</th>

						<td><input type="checkbox" name="load_main_css_as" <?php if (!empty($result['load_main_css_as']) && $result['load_main_css_as'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>

					<tr>
						<th scope="row">Exclude page from Load Combined Css</th>
						<td><textarea name="exclude_page_from_load_combined_css" rows="10" cols="16" placeholder="Please Enter css Page Url." ><?php if (!empty($result['exclude_page_from_load_combined_css'])) echo stripslashes($result['exclude_page_from_load_combined_css']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">Exclude page from Load Combined Js</th>
						<td><textarea name="exclude_page_from_load_combined_js" rows="10" cols="16" placeholder="Please Enter css Page Url." ><?php if (!empty($result['exclude_page_from_load_combined_js'])) echo stripslashes($result['exclude_page_from_load_combined_js']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">Custom css</th>
						<td><textarea name="custom_css" rows="10" cols="16" placeholder="Please Enter css without the style tag." ><?php if (!empty($result['custom_css'])) echo stripslashes($result['custom_css']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">Custom Javascript</th>
						<td><textarea name="custom_js" rows="10" cols="16" placeholder="Please Enter js without the script tag." ><?php if (!empty($result['custom_js'])) echo stripslashes($result['custom_js']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">Custom Javascript after load</th>
						<td><textarea name="custom_js_after_load" rows="10" cols="16" placeholder="Please Enter js without the script tag." ><?php if(!empty($result['custom_js_after_load'])) echo stripslashes($result['custom_js_after_load']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">Exclude Pages From Optimization</th>
						<td><textarea name="exclude_pages_from_optimization" rows="10" cols="16" placeholder="Please Enter Page Url." ><?php if(!empty($result['exclude_pages_from_optimization'])) echo stripslashes($result['exclude_pages_from_optimization']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><input type="submit" value="Save"></th>
						<td></td>
					</tr>
				</tbody>
			</table>


		</form>

	</section>
	
	<section id="wsuc_opt_img_content">
		<form method="post">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Image Src</th>
						<th scope="row">Width <input type="hidden" name="ws_action" value="image_fields"></th>
					</tr>
					
					<?php 
					
					$opti_images = isset($_POST['optimiz_images']) ?  $_POST['optimiz_images'] : array() ; 
					if(!empty($opti_images)){
						foreach($opti_images as $value){ 
							if(!empty($value['src'])){
								$image_url = $value['src'] ;
								$image_width = $value['width'] ;
								echo '<tr><td>'.$image_url.'</td>' ; 
								echo '<td>'.get_ws_optimize_image($image_url, $image_width).'</td></tr>';
							}
						}
					}
					/*if(!empty($opti_images)){
						$i = 0;
						foreach($opti_images['optimiz_images'] as $value){ 
							if(!empty($value['src'])){
						?>
							<tr class="image_src_field">
								<td style="width:70%; padding-left:0px;"><input type="text" name="optimiz_images[<?php echo $key; ?>][src]" placeholder="Pleas Enter Img Src" value="<?php echo $value['src'] ;?>"></td>
								<td style="padding-left:0px;"><input type="text" name="optimiz_images[][width]" placeholder="Pleas Enter Image Width" value="<?php echo $value['width'] ;?>"></td>
								<td class="remove_image_field" style="width:5%; cursor:pointer;">X</td>
							</tr>
						<?php $i++; }							
						}
					}else{ */?>
					
					<tr class="image_src_field">
						<td style="width:70%; padding-left:0px;"><input type="text" name="optimiz_images[0][src]" placeholder="Pleas Enter Img Src" value=""></td>
						<td style="padding-left:0px;"><input type="text" name="optimiz_images[0][width]" placeholder="Pleas Enter Image Width" value=""></td>
						<td class="remove_image_field" style="width:5%; cursor:pointer;">X</td>
					</tr>
					
					<tr class="image_add_more_field">
						<th scope="row"><button type="button" class="add_more_image" >Add More</button></th>
						<td></td>
					</tr>
					<tr>
						<th scope="row"><input type="submit" value="Save"></th>
						<td></td>
					</tr>
				</tbody>
			</table>
		</form>
	</section> 
	<section id="wsuc_opt_img_combin_content">
		<form method="post">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Image Src</th>
						<th scope="row">Position <input type="hidden" name="ws_action" value="combine_image_save"></th>
					</tr>	
					
					<?php //print_r($combine_images);
					//$uploads = wp_upload_dir();
					//print_r($uploads['basedir']);
					
					if(!empty($combine_images)){
						$i = 0;
						foreach($combine_images['combine_images'] as $value){ 
							if(!empty($value['src'])){
						?>
							<tr class="image_src_field">
								<td style="width:70%; padding-left:0px;"><input type="text" name="combine_images[<?php echo $i; ?>][src]" placeholder="Pleas Enter Img Src" value="<?php echo $value['src'] ;?>"></td>
								<td style="padding-left:0px;"><input type="text" name="combine_images[<?php echo $i; ?>][position]" placeholder="Pleas Enter Image Width" value="<?php echo $value['position'] ;?>"></td>
								<td class="remove_image_field" style="width:5%; cursor:pointer;">X</td>
							</tr>
						<?php $i++; }							
						}
					}else{ ?>
						<tr class="image_src_field">
							<td style="width:70%; padding-left:0px;"><input type="text" name="combine_images[0][src]" placeholder="Pleas Enter Img Src" value=""></td>
							<td style="padding-left:0px;"><input type="text" name="combine_images[0][position]" placeholder="Pleas Enter Image Width" value=""></td>
							<td class="remove_image_field" style="width:5%; cursor:pointer;">X</td>
						</tr>
					<?php } ?>
					<tr class="image_add_more_field">
						<th scope="row"><button type="button" class="add_more_combine_image" >Add More</button></th>
						<td></td>
					</tr>
					<?php $combine_image_att_id = get_option( 'wnw_speedup_combine_image_id');
					if(!empty($combine_image_att_id)){
						$cb_src = wp_get_attachment_image_src( $combine_image_att_id , 'full' );
					?>
						<tr>
							<th scope="row">Combined Image</th>
							<td><a href="<?php echo $cb_src[0]; ?>"><?php echo $cb_src[0] ;?></a></td>
						</tr>
					<?php } ?>
					<tr>
						<th scope="row"><input type="submit" value="Save"></th>
						<td></td>
					</tr>
				</tbody>
			</table>
		</form>
	</section>
	<script>
		jQuery(document).ready(function(){
			jQuery('.add_more_image').click(function(){
				var index = jQuery(this).parents('#wsuc_opt_img_content').find('.image_src_field').length ;
				
				var $html = '<tr class="image_src_field"><td style="width:70%; padding-left:0px;"><input type="text" name="optimiz_images['+index+'][src]" placeholder="Pleas Enter Img Src" value=""></td><td style="padding-left:0px;"><input type="text" name="optimiz_images['+index+'][width]" placeholder="Pleas Enter Image Width" value=""></td><td class="remove_image_field" style="width:5%; cursor:pointer;">X</td></tr>';
				
				jQuery(this).parents('.image_add_more_field').before($html);				
			});
			
			jQuery('.add_more_combine_image').click(function(){
				
				var index =  jQuery(this).parents('#wsuc_opt_img_combin_content').find('.image_src_field').length ;
				//alert(index);
				
				var $html = '<tr class="image_src_field"><td style="width:70%; padding-left:0px;"><input type="text" name="combine_images['+index+'][src]" placeholder="Pleas Enter Img Src" value=""></td><td style="padding-left:0px;"><input type="text" name="combine_images['+index+'][position]" placeholder="Pleas Enter Image Width" value=""></td><td class="remove_image_field" style="width:5%; cursor:pointer;">X</td></tr>';
				
				jQuery(this).parents('.image_add_more_field').before($html);				
			});
			
			//jQuery('.remove_image_field').click(function(){
			jQuery( "table" ).delegate( ".remove_image_field", "click", function() {
				jQuery(this).parents('.image_src_field').remove();
			});
		});
	</script>
	<!-- <section id="wsuc_css_content">

	</section>

	<section id="wsuc_opt_img_content">

	</section>-->

</main>