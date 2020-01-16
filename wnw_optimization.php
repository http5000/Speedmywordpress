<?php 

/*

Plugin Name: Speedmywordpress


Description: This plugin is for improve scores on google page speed test and Gtmetrix

Version: 1.0.0

Author: Http5000

License: GPLv2 or later

*/



function wnw_opti_activation(){

  global $wpdb ;

  /*----------------R(26-02-2019)----------*/

	$table_name = 'wp_speedup';

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {		

		 $sql = "CREATE TABLE IF NOT EXISTS `wp_speedup` (

		  `ID` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,

		  `type` VARCHAR(11) NOT NULL,

		  `path` text NOT NULL,

		  `newpath` text NOT NULL

		) ENGINE=InnoDB AUTO_INCREMENT=".rand(1,100)." DEFAULT CHARSET=utf8;";

		 $wpdb->query($sql) ;

	}

/*----------------end----------*/

}

register_activation_hook( __FILE__, 'wnw_opti_activation' );



function wnw_opti_deactivation()

{

   global $wpdb;

   $wpdb->query("DROP TABLE IF EXISTS `wp_speedup` ;");

   wp_clear_scheduled_hook('Find_size_fun');

}



register_deactivation_hook(__FILE__, 'wnw_opti_deactivation');





add_action('wp','wnw_remove_emoji');

function wnw_remove_emoji(){

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 

	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' ); 

	remove_action( 'wp_print_styles', 'print_emoji_styles' ); 

	remove_action( 'admin_print_styles', 'print_emoji_styles' );

}

wnw_prime_time('parse_start');



global $wpdb,$exclude_optimization,$optimize_image_array,$sitename, $image_home_url,$home_url,$full_url,$full_url_without_param, $secure,$additional_img,$wnw_exclude_lazyload,$exclude_css,$full_url_array,$main_css_url, $current_user,$lazy_load_js,$document_root,$fonts_api_links,$lazyload_inner_js,$lazyload_inner_ads_js,$lazyload_inner_ads_js_arr,$css_ext,$js_ext,$cache_folder_path, $speedup_options,$exclude_css_from_minify,$js_delay_load,$internal_js_delay_load,$internal_css_delay_load,$google_fonts_delay_load;

$speedup_options = get_option( 'wnw_wp_speedup_option', true );

$optimize_image_array = array();

$secure =  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

$home_url = $secure.$_SERVER['HTTP_HOST'];



$image_home_url = !empty($speedup_options['cdn']) ? $speedup_options['cdn'] : $secure.$_SERVER['HTTP_HOST'];//;



$sitename = 'home';

$document_root = $_SERVER['DOCUMENT_ROOT'];

$full_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$full_url_array = explode('?',$full_url);

$full_url_without_param = $full_url_array[0];

$exclude_optimization = array();

$useragent=$_SERVER['HTTP_USER_AGENT'];

$cache_folder_path = '/wp-content/cache';

$js_delay_load = !empty($speedup_options['js_delay_load']) ? $speedup_options['js_delay_load']*1000 : 10000;

$internal_css_delay_load = !empty($speedup_options['internal_css_delay_load']) ? $speedup_options['internal_css_delay_load']*1000 : 10000;

$google_fonts_delay_load = !empty($speedup_options['google_fonts_delay_load']) ? $speedup_options['google_fonts_delay_load']*1000 : 2000;

$internal_js_delay_load = !empty($speedup_options['internal_js_delay_load']) ? $speedup_options['internal_js_delay_load']*1000 : 10000;
$exclude_css_from_minify = !empty($speedup_options['exclude_css']) ? explode("\r\n", $speedup_options['exclude_css']) : array();

if(!empty($speedup_options['css_mobile']) && wp_is_mobile()){

    $css_ext = 'mob.css';

    $js_ext = 'mob.js';

    $exclude_css = !empty($speedup_options['preload_css_mobile']) ? explode("\r\n", $speedup_options['preload_css_mobile']) : array();

}else{

    $css_ext = '.css';

    $js_ext = '.js';

	$exclude_css  = !empty($speedup_options['preload_css']) ? explode("\r\n", $speedup_options['preload_css']) : array();

}

 //$exclude_css = array();



$exclude_inner_js= !empty($speedup_options['exclude_inner_javascript']) ? explode("\r\n", $speedup_options['exclude_inner_javascript']) : array('google-analytics', 'hbspt','/* <![CDATA[ */');

$additional_img = array();

$lazyload_inner_js_arr = !empty($speedup_options['lazy_load_inner_js']) ? explode("\r\n", $speedup_options['lazy_load_inner_js']) : array('googletagmanager','connect.facebook.net','static.hotjar.com','js.driftt.com');

foreach($lazyload_inner_js_arr as $arr){

	if(!empty($arr)){

		$lazyload_inner_js[$arr] = '';

	}

}

$lazyload_inner_ads_js = array();//key=>value

$main_css_url = array();

$lazy_load_js = array();

	



function wnw_isexternal($url) {

  $components = parse_url($url);

  return !empty($components['host']) && strcasecmp($components['host'], $_SERVER['HTTP_HOST']);

}



function wnw_compress( $minify )

{

	$minify = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minify );

	$minify = str_replace( array("\r\n", "\r", "\n", "\t",'  ','    ', '    '), ' ', $minify );

	return $minify;

}

function wnw_endswith($string, $test) {
	$str_arr = explode('?',$string);
	$string = $str_arr[0];
    $strlen = strlen($string);

    $testlen = strlen($test);

    if ($testlen > $strlen) return false;

    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;

}

function create_blank_file($path){

    $file = fopen($path,'w');

    fwrite($file,'//Silence is gloden');

    fclose($file);

}

function wnw_prime_time($text){

    /*if(empty($_REQUEST['rest'])){

        return;

    }

    global $starttime;

    if(empty($starttime)){

        $starttime = microtime(true);

    }else{

        $endtime = microtime(true);

        $duration = $endtime-$starttime;

        $hours = (int)($duration/60/60);

        $minutes = (int)($duration/60)-$hours*60;

        $seconds = (int)$duration-$hours*60*60-$minutes*60;

        echo $duration.$text.'<br>';

    }*/

}



//function wnw_str_replace_first($from, $to, $content)

//{

//    $from = '/'.preg_quote($from, '/').'/';

//    return preg_replace($from, $to, $content, 1);

//}

function wnw_parse_link($tag,$link){

	$xmlDoc = new DOMDocument();

	if (@$xmlDoc->loadHTML($link) === false){

		return array();

	}

    

	//$xmlDoc->loadHTML($link);



    $tag_html = $xmlDoc->getElementsByTagName($tag);

    $link_arr = array();

	if(!empty($tag_html[0])){

		foreach ($tag_html[0]->attributes as $attr) {

			$link_arr[$attr->nodeName] = $attr->nodeValue;

		}

	}

    return $link_arr;

}

function parse_script($tag,$link){

    //$link_arr = get_tags_data('>','</script>',$link);

    $data_exists = strpos($link,'>');

    if(!empty($data_exists)){

        $end_tag_pointer = strpos($link,'</script>',$data_exists);

        $link_arr = substr($link, $data_exists+1, $end_tag_pointer-$data_exists-1);

    }

    return $link_arr;

}

function implode_link_array($tag,$array){

    $link = '<'.$tag.' ';

    foreach($array as $key => $arr){

        $link .= $key.'="'.$arr.'" ';

    }

    $link .= '>';

    return $link;

}

function implode_script_array($tag,$array){

    $link = '<script ';

    foreach($array as $key => $arr){

        $link .= $key.'="'.$arr.'" ';

    }

    $link .= '></script>';

    return $link;

}

function str_replace_set($str,$rep){

    global $str_replace_str_array, $str_replace_rep_array;

    $str_replace_str_array[] = $str;

    $str_replace_rep_array[] = $rep;

}

function str_replace_bulk($html){

    global $str_replace_str_array, $str_replace_rep_array;

    $html = str_replace($str_replace_str_array,$str_replace_rep_array,$html);

    return $html;

}

function start_site_optimization(){

    ob_start();

}

function create_file_cache_js($path){

	global $document_root,$cache_folder_path;

	$cache_path = $cache_folder_path.'/wnw-cache/js';

	$cache_file_path = $cache_path.'/'.md5($path).'.js';

	if(!file_exists($document_root.$cache_file_path) || (file_exists($document_root.$cache_file_path) && filemtime($document_root.$cache_file_path) < filemtime($document_root.$path))){

		if(!file_exists($document_root.$cache_path)){

			mkdir($document_root.$cache_path);

			create_blank_file($document_root.$cache_path.'/index.php');

		}

		include_once 'includes/jsmin.php';

		$html = file_get_contents($document_root.$path);

		$src_array = explode('/',$path);

		$count = count($src_array);

		unset($src_array[$count-1]);

		$html = str_replace('$(window).load(et_all_elements_loaded)','$(window).load(et_all_elements_loaded);et_all_elements_loaded()',$html);
		if(function_exists('wpspeedup_internal_js_customize')){
        	$html = wpspeedup_internal_js_customize($html,$path);
        }
		$minify = JSMin::minify($html);

		$minify = str_replace('sourceMappingURL=','sourceMappingURL='.implode('/',$src_array),$minify.";\n");

		$file = fopen($document_root.$cache_file_path,'w');

		fwrite($file,$minify);

		fclose($file);

	}

	return $cache_file_path;

}

function create_file_cache_css($path){

	global $document_root,$home_url,$cache_folder_path;

	$cache_path = $cache_folder_path.'/wnw-cache/css';

	$cache_file_path = $cache_path.'/'.md5($path).'.css';

	

	if(!file_exists($document_root.$cache_file_path) || (file_exists($document_root.$cache_file_path) && filemtime($document_root.$cache_file_path) < filemtime($document_root.$path))){

		if(!file_exists($document_root.$cache_path)){

			mkdir($document_root.$cache_path);

			create_blank_file($document_root.$cache_path.'/index.php',0777,true);

		}

		$minify = wnw_compress(gz_relative_to_absolute_path($home_url.$path,file_get_contents($document_root.$path)));

		$file = fopen($document_root.$cache_file_path,'w');

		fwrite($file,$minify);

		fclose($file);

	}

	return $cache_file_path;

}

function create_file_cache_cssurl($url){

	global $document_root,$cache_folder_path;

	$cache_path = $cache_folder_path.'/wnw-cache/css';

	$cache_file_path = $cache_path.'/'.md5($url).'.css';

    if(!file_exists($document_root.$cache_file_path) || (file_exists($document_root.$cache_file_path) && time() - filemtime($document_root.$cache_file_path)) > 18000){

        $minify = wnw_compress(gz_relative_to_absolute_path($url,file_get_contents($url)));

        $file = fopen($document_root.$cache_file_path,'w');

        fwrite($file,$minify);

        fclose($file);

    }

	return $cache_file_path;

}

function create_file_cache($path){

	global $document_root;

	$ext = pathinfo($path, PATHINFO_EXTENSION);

	if($ext == 'js'){

		return create_file_cache_js($path);

	}elseif($ext == 'css'){

		return create_file_cache_css($path);

	}

	

}





function get_site_optimized($html){

    global $wpdb, $sitename, $image_home_url,$home_url,$full_url,$full_url_without_param, $secure,$additional_img,$wnw_exclude_lazyload,$exclude_css,$full_url_array,$main_css_url, $current_user,$lazy_load_js,$document_root,$fonts_api_links,$lazyload_inner_js,$css_ext,$js_ext,$exclude_inner_js,$exclude_optimization,$lazyload_inner_ads_js,$lazyload_inner_ads_js_arr,$cache_folder_path,$speedup_options,$exclude_css_from_minify,$internal_js;
	$internal_js=array();

	if(!empty($_REQUEST['orgurl']) || strpos($html,'<body') === false){

        return $html;

    }

	

	if (function_exists( 'is_amp_endpoint' ) && is_amp_endpoint()) {

		return $html;

	}

    $value = $speedup_options['status'];

    if(empty($value) && empty($_REQUEST['tester'])){

         return $html;

    }

    if(is_admin()){

        return $html;

    }

	;

	$e_p_from_optimization = !empty($speedup_options['exclude_pages_from_optimization']) ? explode("\r\n",$speedup_options['exclude_pages_from_optimization']) : array();

	if(!empty($e_p_from_optimization)){

		foreach( $e_p_from_optimization as $e_page ){

			if((is_home() || is_front_page()) && $home_url == $e_page){

				return $html;

				break;

			}else if($home_url != $e_page){

				/* ob_start(); 		echo $home_url; 	$tttt = ob_get_clean(); */

				if(strpos($full_url, $e_page)!==false){

				    return $html;

				    break;

				}

			}

								

		}			

	}

	

	

    if(is_404() || (!empty($current_user) && current_user_can('edit_others_pages')) ){//

        return $html;

    }

	

    $current_url = !empty($full_url_without_param) ? trim($full_url_without_param,'/') : $sitename;

    $url_array = explode('/',trim(str_replace($home_url,'',$full_url),'/'));

    $sanitize_url = $current_url;

    $display_css = false;

    $full_cache_path = $document_root.$cache_folder_path.'/wnw-cache';

    $encoded_url = '';

    if(!empty($url_array)){

         if(!file_exists($document_root.$cache_folder_path)){

            mkdir($document_root.$cache_folder_path);

            create_blank_file($document_root.$cache_folder_path.'/index.php');

        }

		if(!file_exists($full_cache_path)){

            mkdir($full_cache_path,0777,true);

            create_blank_file($full_cache_path.'/index.php');

        }

       

    }



    /*if(!class_exists('JSMin')){

        require_once('jsmin.php');

    }*/



    $all_js= '';

    $all_js1= '';

	$all_css='';

    $all_js_html = '';

    $uri_parts = explode('?', trim(str_replace($home_url,'',$full_url),'/'), 2);

    $current_url = $full_url_without_param;

    wnw_prime_time('parse_html');

	$all_links = wnw_setAllLinks($html);
    

	
    wnw_prime_time('parse_html_done');

	

    //str_replace_set('id="div-gpt-ad-','class="lazyload-ads" id="div-gpt-ad-',$html);

   // str_replace_set('id=\'div-gpt-ad-','class="lazyload-ads" id=\'div-gpt-ad-',$html);

    $script_links = $all_links['script'];

    $is_js_file_updated = 0;

    //echo '<!--<pre>'; print_r($script_links);echo '</pre>-->';

	$included_js = array();

	$final_merge_js = array();

	$js_file_name = '';

	if(!empty($script_links) && $speedup_options['js'] == 'on'){
            
                $exclude_js_arr  = !empty($speedup_options['exclude_javascript']) ? explode("\r\n", $speedup_options['exclude_javascript']) : array();
		$exclude_custom_js_defer  = !empty($speedup_options['exclude_custom_javascript']) ? explode("\r\n", $speedup_options['exclude_custom_javascript']) : array();
            
                /*-----------------start 27-07-2019-----------------*/

			$js_file_has_change = false ;

			$all_js = 1;

			foreach($script_links as $k => $scr){			

				$script_text='';

				$script_obj = array();

				$script_obj = wnw_parse_link('script',$scr);
                                
				if(!array_key_exists('src',$script_obj)){

					$script_text = parse_script('<script',$scr);

				}



				if(!empty($script_obj['type']) && strtolower($script_obj['type']) != 'text/javascript'){

					continue;

				}

				if(!empty($script_obj['src'])){
					$url_array = parse_url($script_obj['src']);
					$exclude_js = 0;
					foreach($exclude_js_arr as $ex_js){
						if(strpos($scr,$ex_js) !== false){
							$exclude_js = 1;
						}
					}
                                        foreach($exclude_custom_js_defer as $ex_js){
						if(strpos($scr,$ex_js) !== false){
							$exclude_js = 1;
						}
					}

					if($exclude_js){
						if($speedup_options['exclude_js_defer'] == 'on'){
							$script_obj['defer'] = 'defer';
							str_replace_set($scr,implode_script_array('<script',$script_obj),$html);
						}
                                                if($speedup_options['exclude_custom_js_defer'] == 'on'){
							$script_obj['defer'] = 'defer';
							str_replace_set($scr,implode_script_array('<script',$script_obj),$html);
						}
						continue; 
					}
					if(!wnw_isexternal($script_obj['src']) && file_exists($document_root.$url_array['path']) && wnw_endswith($url_array['path'], '.js')){
							$f_path = "SELECT ID FROM 	wp_speedup WHERE type='js' AND path='".$url_array['path']."'";
							$exit_id = $wpdb->get_var($f_path);
							if(empty($exit_id)){
								$js_file_has_change = true ;
								break;
							}else{
								$included_js[$script_obj['src']] = $exit_id;
							}
                                                    }
					$val = $script_obj['src'];
					if(!empty($val) && !wnw_isexternal($val) && strpos($scr, '.js')){

						str_replace_set($scr,'',$html);

						if(is_array($included_js) && array_key_exists ($val , $included_js) ){

							$final_merge_js[] = $included_js[$val];

						}
					}else{
						$lazy_load_js[] = $script_obj;
						str_replace_set($scr,'',$html);
					}
				}else{

				  //echo '<!--<pre>'; print_r($script_text);echo '</pre>-->';
                                 
				  $inner_js = $script_text;				  

				  $lazy_loadjs = 0;

				  $exclude_js_bool = 0;

					if(!empty($exclude_inner_js)){

					  foreach($exclude_inner_js as $js){

						  if(strpos($inner_js,$js) !== false){

							 $exclude_js_bool=1;

						  }

					  }

				  }

				  if(!empty($exclude_js_bool)){

					  continue;

				  }

				  if(!empty($lazyload_inner_js)){

					  foreach($lazyload_inner_js as $key => $js){

						  if(!empty($key) && strpos($scr,$key) !== false){

							  $lazyload_inner_js[$key] .= $inner_js.";\n";

							  $lazy_loadjs = 1;

						  }

					  }

				  }

				  if(!empty($lazyload_inner_ads_js)){

					  foreach($lazyload_inner_ads_js as $key => $js){

						  if(!empty($key) && strpos($scr,$key) !== false){

							  $lazyload_inner_ads_js_arr[] = $inner_js;

							  $lazy_loadjs = 1;

						  }

					  }

				  }



				  if(!$lazy_loadjs){

					$md5_inner_js = md5($inner_js);

					/*-------------R(26-02-2019)-------------*/		

						$f_path = "SELECT ID FROM wp_speedup WHERE type='js' AND newpath='".$md5_inner_js."'";

						$exit_id = $wpdb->get_var($f_path);

						if(empty($exit_id)){

							$js_file_has_change = true ;

							break;

						}else{

							$final_merge_js[] = $exit_id;

						}

					/*-------------end--------------*/

					//$all_js .= $inner_js.";\n";

				  }

				  //$all_js_html .= $script."\n";

				  str_replace_set($scr,'',$html);

				}

			}

			if(!$js_file_has_change){

				$file_name = is_array($final_merge_js) ? implode('-', $final_merge_js) : '';

				if(!empty($file_name)){

					$js_file_name = md5($file_name).$js_ext;

					if(!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-js/'.$js_file_name)){

						$js_file_has_change = true;

					}

				}	

			}

		/*-----------------end 27-07-2019-----------------*/

		if($js_file_has_change == true){

			$all_js='';
			$included_js = array();

			$final_merge_js = array();
			$lazy_load_js = array();
			$js_file_name = '';
			foreach($lazyload_inner_js as $key => $arr){
				$lazyload_inner_js[$key] = '';
			}
			foreach($lazyload_inner_ads_js_arr as $key => $arr){
				$lazyload_inner_ads_js_arr[$key] = '';
			}
			
			foreach($script_links as $script){

				$script_text='';

				$script_obj = array();

				$script_obj = wnw_parse_link('script',$script);

				if(!array_key_exists('src',$script_obj)){

					$script_text = parse_script('<script',$script);

				}



				if(!empty($script_obj['type']) && strtolower($script_obj['type']) != 'text/javascript'){

					continue;

				}

				//echo '<pre>'; print_r($script_obj);echo '</pre>';

				if(!empty($script_obj['src'])){

					$url_array = parse_url($script_obj['src']);

					$exclude_js = 0;

					foreach($exclude_js_arr as $ex_js){

						if(strpos($script,$ex_js) !== false){

							$exclude_js = 1;

						}

					}
                                        foreach($exclude_custom_js_defer as $ex_js){

						if(strpos($script,$ex_js) !== false){

							$exclude_js = 1;

						}

					}

					if($exclude_js){

						if($speedup_options['exclude_js_defer'] == 'on'){

							$script_obj['defer'] = 'defer';

							str_replace_set($script,implode_script_array('<script',$script_obj),$html);

						}
                                                continue;
                                                if($speedup_options['exclude_custom_js_defer'] == 'on'){

							$script_obj['defer'] = 'defer';

							str_replace_set($script,implode_script_array('<script',$script_obj),$html);

						}

						continue;
                                                

					}

					if(!wnw_isexternal($script_obj['src']) && file_exists($document_root.$url_array['path']) && wnw_endswith($url_array['path'], '.js')){

						

						$old_path = $url_array['path'];

						$url_array['path'] = create_file_cache($url_array['path']);

						$script_obj['src'] = $home_url.$url_array['path'];

						

						/*--------r(26-02-2019)-----------*/

															

							$f_path = "SELECT ID FROM 	wp_speedup WHERE type='js' AND path='".$old_path."'";

							$exit_id = $wpdb->get_var($f_path);

							if(empty($exit_id)){

								$query = $wpdb->prepare('INSERT INTO wp_speedup SET type=%s, path= %s, newpath = %s' , array('js' ,$old_path, $script_obj['src']) ) ;		

								$wpdb->query($query) ;

								$lastid = $wpdb->insert_id;

								$included_js[$script_obj['src']] = $lastid;

							}else{

								$included_js[$script_obj['src']] = $exit_id;

							}

						

						/*--------end-----------*/

						

					}

					$val = $script_obj['src'];

					

					

					if(!empty($val) && !wnw_isexternal($val) && strpos($script, '.js')){

						$filename = $document_root . $url_array['path'];

						$all_js .= file_get_contents($filename).";\n";

						str_replace_set($script,'',$html);

						

						/*--------R(26-02-2019-----------*/

						if(is_array($included_js) && array_key_exists ($val , $included_js) ){

							$final_merge_js[] = $included_js[$val];

						}

						/*--------end-----------*/

						

					}else{

						$lazy_load_js[] = $script_obj;

						str_replace_set($script,'',$html);

					}





				}else{

				  //echo '<!--<pre>'; print_r($script_text);echo '</pre>-->';

				  $inner_js = $script_text;

				  /*if(strpos($script,'Five9SocialWidget')){

					  str_replace_set($script,'',$html);

					  continue;

				  }*/

				  

				  

				  $lazy_loadjs = 0;

				  $exclude_js_bool = 0;

					if(!empty($exclude_inner_js)){

					  foreach($exclude_inner_js as $js){

						  if(strpos($inner_js,$js) !== false){

							 $exclude_js_bool=1;

						  }

					  }

				  }

				  if(!empty($exclude_js_bool)){

					  continue;

				  }

				  if(!empty($lazyload_inner_js)){

					  foreach($lazyload_inner_js as $key => $js){

						  if(!empty($key) && strpos($script,$key) !== false){

							  $lazyload_inner_js[$key] .= $inner_js.";\n";

							  $lazy_loadjs = 1;

						  }

					  }

				  }

				  if(!empty($lazyload_inner_ads_js)){

					  foreach($lazyload_inner_ads_js as $key => $js){

						  if(!empty($key) && strpos($script,$key) !== false){

							  $lazyload_inner_ads_js_arr[] = $inner_js;

							  $lazy_loadjs = 1;

						  }

					  }

				  }



				  if(!$lazy_loadjs){

					$md5_inner_js = md5($inner_js);

					/*-------------R(26-02-2019)-------------*/		

						$f_path = "SELECT ID FROM wp_speedup WHERE type='js' AND newpath='".$md5_inner_js."'";

						$exit_id = $wpdb->get_var($f_path);

						if(empty($exit_id)){

							$query = $wpdb->prepare('INSERT INTO wp_speedup SET type=%s, path= %s, newpath = %s' , array('js' ,'', $md5_inner_js));	

							$wpdb->query($query) ;

							$lastid = $wpdb->insert_id;

							$final_merge_js[] = $lastid;

						}else{

							$final_merge_js[] = $exit_id;

						}

					/*-------------end--------------*/

					$all_js .= $inner_js.";\n";

				  }

				  //$all_js_html .= $script."\n";

				  str_replace_set($script,'',$html);

				}

			}



			/*-------------R(26-02-2019)-------------*/

				$file_name = is_array($final_merge_js) ? implode('-', $final_merge_js) : '';

				if(!empty($file_name)){

					if (!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-js')) {

						mkdir($document_root.$cache_folder_path.'/wnw-cache/all-js', 0775, true);

					}

					$js_file_name = md5($file_name).$js_ext;

					if(!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-js/'.$js_file_name)){

						$file = fopen($document_root.$cache_folder_path.'/wnw-cache/all-js/'.$js_file_name,'w');

						fwrite($file,$all_js.(!empty($speedup_options['custom_js']) ? stripslashes($speedup_options['custom_js']) : '').';if(typeof jQuery === "object"){setTimeout(function(){jQuery(window).trigger("load");},100);setInterval(function(){jQuery(window).trigger("resize");},1000);}');

						fclose($file);

					}

				}	

		}else{

			$file_name = is_array($final_merge_js) ? implode('-', $final_merge_js) : '';

			$js_file_name = md5($file_name).$js_ext;

		}

	}

	if(!empty($speedup_options['custom_js_after_load'])){

		$lazyload_inner_js['custom_js'] = stripslashes($speedup_options['custom_js_after_load']);

	}

    //exit;
	$excluded_img = !empty($speedup_options['exclude_lazy_load']) ? explode("\r\n",$speedup_options['exclude_lazy_load']) : array();
	if(!empty($speedup_options['lazy_load_iframe'])){

		$iframe_links = $all_links['iframe'];

		foreach($iframe_links as $img){

			$exclude_image = 0;

			foreach( $excluded_img as $ex_img ){

				if(!empty($ex_img) && strpos($img,$ex_img)!==false){

					$exclude_image = 1;

				}

			}

			if($exclude_image){

				continue;

			}

			//echo '<br><pre>'.print_r($img);

			$img_obj = wnw_parse_link('iframe',$img);

			$img_obj['data-src'] = $img_obj['src'];

			$img_obj['src'] = 'about:blank';

			$img_obj['data-class'] = 'LazyLoad';

			str_replace_set($img,implode_link_array('iframe',$img_obj),$html);

		}

	}

	if(!empty($speedup_options['lazy_load_video'])){

		$iframe_links = $all_links['video'];

		foreach($iframe_links as $img){

			//echo '<br><pre>'.print_r($img);

			$v_src = $image_home_url.'/blank.mp4';

			$img_new = str_replace('src=','data-class="LazyLoad" src="'.$v_src.'" data-src=',$img);

			str_replace_set($img,$img_new,$html);

		}

	}

	

    $img_links = $all_links['img'];

	if(!empty($speedup_options['lazy_load'])){

    foreach($img_links as $img){

		$exclude_image = 0;

		foreach( $excluded_img as $ex_img ){

			if(!empty($ex_img) && strpos($img,$ex_img)!==false){

				$exclude_image = 1;

			}

		}

		if($exclude_image){
			continue;
		}

			$imgnn = $img;
			if(strpos($img, 'srcset') !== false){
				$imgnn = str_replace(' srcset=',' srcset="'.$image_home_url.'/blank.png 500w, '.$image_home_url.'/blank.png 1000w" data-srcset=',$imgnn);
			}
           $imgnn = str_replace(array(' src=','<img'),array(' data-src=','<img data-class="LazyLoad" src="'.$image_home_url.'/blank.png"'),$imgnn);		
           $html = str_replace($img,$imgnn,$html);

    }

}

    //exit;

    

    wnw_prime_time('defer_images_done');

    $all_css1 = '';



    $css_links = $all_links['link'];

    $fonts_api_links = array();

    $i= 1;

if(!empty($css_links) && $speedup_options['css'] == 'on'){

    //echo '<pre>'; print_r($css_links);echo '</pre>'; exit;

	$included_css = array();

	$main_included_css = array();

	$final_merge_css = array();

	$final_merge_main_css = array();

	$css_file_name = '';

	

	

	/*-------------start 27-07-2019-----------------------------*/

		$css_file_has_change = false ;

		$all_css = 1;

		foreach($css_links as $css){

			$css_obj = wnw_parse_link('link',$css);

		   

			if($css_obj['rel'] == 'stylesheet'){

				$org_css = '';

				$media = '';

				$exclude_css1 = 0;

				if(!empty($exclude_css_from_minify)){

					foreach($exclude_css_from_minify as $ex_css){

						if(!empty($ex_css) && strpos($css, $ex_css) !== false){

							$exclude_css1 = 1;

						}

					}

				}

				if($exclude_css1){

					continue;

				}

				if(!empty($css_obj['media']) && $css_obj['media'] != 'all' && $css_obj['media'] != 'screen'){

					$media = $css_obj['media'];

				}

				$url_array = parse_url($css_obj['href']);

				if(!wnw_isexternal($css_obj['href']) && file_exists($document_root.$url_array['path']) ){

					if(!wnw_endswith($css_obj['href'], '.css') && strpos($css_obj['href'], '.css?') === false){

						continue;

						

					}else{

						$org_css = $home_url.$url_array['path'];

						$url_array['path'] = create_file_cache($url_array['path']);

						$css_obj['href'] = $home_url.$url_array['path'];

						/*-------------R(26-02-2019--------*/

							$f_path = "SELECT ID FROM wp_speedup WHERE type='css' AND path='".$org_css."'";

							$exit_id = $wpdb->get_var($f_path);

							if(empty($exit_id)){

								$css_file_has_change = true;

								break;

							}else{

								$included_css[$css_obj['href']] =  $exit_id;

							}

						/*----------end-----------------*/	

						

					}

				}

				$full_css_url = $css_obj['href'];

				$url = explode('?',$full_css_url);

				$url_array = parse_url($full_css_url);



				if($url_array['host'] == 'fonts.googleapis.com'){

					if(empty($speedup_options['google_fonts'])){

						continue;

					}

					parse_str($url_array['query'], $get_array);

					if(!empty($get_array['family'])){

						$font_array = explode('|',$get_array['family']);

						foreach($font_array as $font){

							if(strpos($font,'00') !== false){

							$font_split = explode(':',$font);						

							if(!is_array($fonts_api_links[$font_split[0]])){

								$fonts_api_links[$font_split[0]] = array();						}

								$fonts_api_links[$font_split[0]] = array_merge($fonts_api_links[$font_split[0]],explode(',',$font_split[1]));

							}

						}

					}

					str_replace_set($css,'', $html);

					continue;

				}

				$src = $url[0];

				$include_as_inline = 0;

				if(!empty($exclude_css)){

					foreach($exclude_css as $ex_css){

						if(!empty($ex_css) && strpos($org_css, $ex_css) !== false){

							$include_as_inline = 1;

						}

					}

				}

				$src = $full_css_url;

				if(!empty($src) && !wnw_isexternal($src) && !empty($include_as_inline) && wnw_endswith($src, '.css') ){

					$path = parse_url($src, PHP_URL_PATH);

					$filename = $document_root.$path;

					$inline_css_var = file_get_contents($filename);

					$inline_css[$filename]['filename'] = $filename;

					$inline_css[$filename]['media'] = $media;

					if(is_array($included_css) && array_key_exists ($src , $included_css) ){

						$final_merge_main_css[] = $included_css[$src];

					}

					str_replace_set($css,'',$html);

				}elseif(!empty($src) && !wnw_isexternal($src) && wnw_endswith($src, '.css')){

					

					if(is_array($included_css) && array_key_exists ($src , $included_css) ){

						$final_merge_css[] = $included_css[$src];

					}

					

					str_replace_set($css,'',$html);

				}elseif(wnw_endswith($full_css_url, '.css') || strpos($full_css_url, '.css?')){

					$main_css_url[] = $full_css_url;

					str_replace_set($css,'',$html);

				}

			}

		}

		if(!$css_file_has_change){

			$file_name = is_array($final_merge_css) ? implode('-', $final_merge_css) : '';

			if(!empty($file_name)){

				$css_file_name = md5($file_name).$css_ext;

				if(!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-css/'.$css_file_name)){

					$css_file_has_change = true;

				}

			}

		}

	/*-------------end 27-07-2019-----------------------------*/

	if($css_file_has_change == true){

		$all_css = '';

		foreach($css_links as $css){

			$css_obj = wnw_parse_link('link',$css);

		   

			if($css_obj['rel'] == 'stylesheet'){

				$org_css = '';

				$media = '';

				$exclude_css1 = 0;

				if(!empty($exclude_css_from_minify)){

					foreach($exclude_css_from_minify as $ex_css){

						if(!empty($ex_css) && strpos($css, $ex_css) !== false){

							$exclude_css1 = 1;

						}

					}

				}

				if($exclude_css1){

					continue;

				}

				if(!empty($css_obj['media']) && $css_obj['media'] != 'all' && $css_obj['media'] != 'screen'){

					$media = $css_obj['media'];

				}

				$url_array = parse_url($css_obj['href']);

				if(!wnw_isexternal($css_obj['href']) && file_exists($document_root.$url_array['path']) ){

					if(!wnw_endswith($css_obj['href'], '.css') && strpos($css_obj['href'], '.css?') === false){

						continue;

						$org_css = $css_obj['href'];

						$url_array['path'] = create_file_cache_cssurl($css_obj['href']);

						$css_obj['href'] = $home_url.$url_array['path'];

					}else{

						$org_css = $home_url.$url_array['path'];

						$url_array['path'] = create_file_cache($url_array['path']);

						$css_obj['href'] = $home_url.$url_array['path'];

						/*-------------R(26-02-2019--------*/

							$f_path = "SELECT ID FROM wp_speedup WHERE type='css' AND path='".$org_css."'";

							$exit_id = $wpdb->get_var($f_path);

							if(empty($exit_id)){

								$query = $wpdb->prepare('INSERT INTO wp_speedup SET type=%s, path= %s, newpath = %s' , array('css' ,$org_css, $css_obj['href']));	

								$wpdb->query($query) ;

								$lastid = $wpdb->insert_id;

								$included_css[$css_obj['href']] =  $lastid;

							}else{

								$included_css[$css_obj['href']] =  $exit_id;

							}

						/*----------end-----------------*/	

						

					}

				}

				$full_css_url = $css_obj['href'];

				$url = explode('?',$full_css_url);

				$url_array = parse_url($full_css_url);



				if($url_array['host'] == 'fonts.googleapis.com'){

					if(empty($speedup_options['google_fonts'])){

						continue;

					}

					parse_str($url_array['query'], $get_array);

					if(!empty($get_array['family'])){

						$font_array = explode('|',$get_array['family']);

						foreach($font_array as $font){

							if(strpos($font,'00') !== false){

							$font_split = explode(':',$font);						

							if(!is_array($fonts_api_links[$font_split[0]])){

								$fonts_api_links[$font_split[0]] = array();						}

								$fonts_api_links[$font_split[0]] = array_merge($fonts_api_links[$font_split[0]],explode(',',$font_split[1]));

							}

						}

					}

					str_replace_set($css,'', $html);

					continue;

				}

				$src = $url[0];

				$include_as_inline = 0;

				if(!empty($exclude_css)){

					foreach($exclude_css as $ex_css){

						if(!empty($ex_css) && strpos($org_css, $ex_css) !== false){

							$include_as_inline = 1;

						}

					}

				}

				$src = $full_css_url;

				if(!empty($src) && !wnw_isexternal($src) && !empty($include_as_inline) && wnw_endswith($src, '.css') ){

					

					$path = parse_url($src, PHP_URL_PATH);

					$filename = $document_root.$path;

					$inline_css_var = file_get_contents($filename);

					$inline_css[$filename]['filename'] = $filename;

					$inline_css[$filename]['media'] = $media;//!empty($media) ? '@'.$media.'{'.$inline_css_var.'}' : $inline_css_var ;

					/*-------------R(26-02-2019--------*/

						if(is_array($included_css) && array_key_exists ($src , $included_css) ){

							$final_merge_main_css[] = $included_css[$src];

						}

					/*-------------end--------*/

					str_replace_set($css,'',$html);

				}elseif(!empty($src) && !wnw_isexternal($src) && wnw_endswith($src, '.css')){

					$path = parse_url($src, PHP_URL_PATH);

					$filename = $document_root.$path;

					if(filesize($filename) > 0){

						$inline_css_var = file_get_contents($filename);

						$all_css .= !empty($media) ? '@media '.$media.'{'.$inline_css_var.'}' : $inline_css_var ;

					}

					/*-------------R(26-02-2019--------*/

						if(is_array($included_css) && array_key_exists ($src , $included_css) ){

							$final_merge_css[] = $included_css[$src];

						}

					/*-------------end--------*/

					

					str_replace_set($css,'',$html);

				}elseif(wnw_endswith($full_css_url, '.css') || strpos($full_css_url, '.css?')){

					$main_css_url[] = $full_css_url;

					str_replace_set($css,'',$html);

				}

			}

		}

	

			$file_name = is_array($final_merge_css) ? implode('-', $final_merge_css) : '';

			if(!empty($file_name)){

				if (!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-css')) {

					mkdir($document_root.$cache_folder_path.'/wnw-cache/all-css');

				}

				$css_file_name = md5($file_name).$css_ext;

				if(!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-css/'.$css_file_name)){

					$file = fopen($document_root.$cache_folder_path.'/wnw-cache/all-css/'.$css_file_name,'w');

					fwrite($file,$all_css);

					fclose($file);

				}

			}

		/*-------------end--------*/

		

		/* $css_exists = rand(1,10);

		$file = fopen($full_cache_path.'/all-css'.$css_exists.$css_ext,'w');

		fwrite($file,$all_css);

		fclose($file); */

    }else{

		$file_name = is_array($final_merge_css) ? implode('-', $final_merge_css) : '';

		$css_file_name = md5($file_name).$css_ext;

	}

}



    //print_r($fonts_api_links); exit;

    wnw_prime_time('defer_css_done');

	$appendonstyle = 0;

	$start_body_pointer = strpos($html,'<body');

	$start_body_pointer = $start_body_pointer ? $start_body_pointer : strpos($html,'</head');

	$head_html = substr($html,0,$start_body_pointer);

	

    if(strpos($head_html,'<style') !== false){

    	$appendonstyle=1;

    }elseif(strpos($head_html,'<link') !== false){

		$appendonstyle=2;

	}else{

		$appendonstyle=3;

	}

	if(!empty($fonts_api_links)){

        $all_links = '';

        foreach($fonts_api_links as $key => $links){

            $all_links .= $key.':'.implode(',',$links).'|';

        }

        global $google_font;

        $google_font[] = $secure."fonts.googleapis.com/css?family=".urlencode(trim($all_links,'|'));

    }

    $encoded_url= trim($encoded_url,'/');

    $encoded_url = !empty($encoded_url) ? '/'.$encoded_url.'/' : '/';

    $html = gz_relative_to_absolute_path($home_url.'/test.html',$html);

    $html = str_replace_bulk($html);

    

	//$inline_css = (count($inline_css) > 0 ) ? array_reverse($inline_css) : $inline_css ;

    $all_inline_css = '';

	if(is_array($inline_css) && count($inline_css) > 0){

		foreach($inline_css as $inline){

			//$html .= file_get_contents($inline['filename']);

			$all_inline_css .= !empty($inline['media']) ? '@media '.$inline['media'].'{'.file_get_contents($inline['filename']).'}' : file_get_contents($inline['filename']) ;

		}

	}

	$all_inline_css .= (!empty($speedup_options['custom_css']) ? stripslashes($speedup_options['custom_css']) : '').'@keyframes fadeIn {  to {    opacity: 1;  }}.fade-in {  opacity: 0;  animation: fadeIn .5s ease-in 1 forwards;}.is-paused {  animation-play-state: paused;}';

	if($speedup_options['load_main_css_as'] == 'on' && !empty($all_inline_css)){

		$file_name = is_array($final_merge_main_css) ? implode('-', $final_merge_main_css) : '';

		$main_css_file_name = md5($file_name).$css_ext;

		$main_css_link = $image_home_url.$cache_folder_path.'/wnw-cache/all-css/'.$main_css_file_name ;

		if(!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-css/'.$main_css_file_name)){

			if (!file_exists($document_root.$cache_folder_path.'/wnw-cache/all-css')) {

				mkdir($document_root.$cache_folder_path.'/wnw-cache/all-css');

			}

			$file = fopen($document_root.$cache_folder_path.'/wnw-cache/all-css/'.$main_css_file_name,'w');

			fwrite($file,$all_inline_css);

			fclose($file);

		}

		$html = wnw_insert_content_head($html , '<link rel="stylesheet" href="'.$main_css_link.'" />',$appendonstyle);

		

	}else{

		$html = wnw_insert_content_head($html , 'main_wnw_inline_css',$appendonstyle);
		$html = str_replace("main_wnw_inline_css",'<style>'.$all_inline_css.'</style>',$html);

	}

	$exclude_page = false ;

    if(!empty($css_file_name)){

        $main_css_link = $image_home_url.$cache_folder_path.'/wnw-cache/all-css/'.$css_file_name ;

		$e_p_from_load_combined_css = !empty($speedup_options['exclude_page_from_load_combined_css']) ? explode("\r\n",$speedup_options['exclude_page_from_load_combined_css']) : array();

		if(!empty($e_p_from_load_combined_css)){

			$exclude_page = false ;

			foreach( $e_p_from_load_combined_css as $e_page ){				

				if(strpos($current_url, $e_page)!==false){

					$exclude_page = true;

					break;

				}					

			}			

		}

		

		if($exclude_page == true){			

			if($speedup_options['load_combined_css'] != 'after_page_load'){

				$main_css_url[] = $main_css_link;

			}else{

				$html = wnw_insert_content_head($html ,'<link rel="stylesheet" href="'.$main_css_link.'" />',$appendonstyle);

			}			

		}else if($speedup_options['load_combined_css'] == 'after_page_load'){

			$main_css_url[] = $main_css_link;

		}else{

			$html = wnw_insert_content_head($html ,'<link rel="stylesheet" href="'.$main_css_link.'" />',$appendonstyle);

		}

    }

	if(!empty($speedup_options['custom_javascript']) && $speedup_options['custom_javascript_file'] == 'on'){

		$custom_js_path = $cache_folder_path.'/wnw-cache/js/'.'wnw-custom-js.js';

		if(!file_exists($document_root.$custom_js_path)){

			$file = fopen($document_root.$custom_js_path,'w');

			fwrite($file,stripslashes($speedup_options['custom_javascript']));

			fclose($file);

		}

		$html = str_replace('</body>','<script '.(!empty($speedup_options['custom_javascript_defer']) ? 'defer="defer"' : '').' id="wnw-custom-js" src="'.$image_home_url.$custom_js_path.'"></script></body>',$html);

	}

	$exclude_page = false ;

	if(!empty($js_file_name)){

        //$main_js_url = $home_url.$cache_folder_path.'/wnw-cache'.$encoded_url.'all-js'.$js_exists.$js_ext;

		$main_js_url = $image_home_url.$cache_folder_path.'/wnw-cache/all-js/'.$js_file_name;

		$e_p_from_load_combined_js = !empty($speedup_options['exclude_page_from_load_combined_js']) ? explode("\r\n",$speedup_options['exclude_page_from_load_combined_js']) : array();

		if(!empty($e_p_from_load_combined_js)){

			$exclude_page = false ;

			foreach( $e_p_from_load_combined_js as $e_page ){				

				if(strpos($current_url, $e_page)!==false){

					$exclude_page = true;

					break;

				}					

			}			

		}

		

		if($exclude_page == true){			

			if($speedup_options['load_combined_js'] != 'after_page_load'){						

				$internal_js[] = array('src'=>$main_js_url);

				$html = str_replace('</body>','<script>'.lazy_load_images().'</script></body>',$html);			

			}else{

				$html = str_replace('</body>','<script>'.lazy_load_images().'</script></body>',$html);

				$html = str_replace('</body>','<script defer="defer" id="main-js" src="'.$main_js_url.'"></script></body>',$html);

			}

			

		}else if($speedup_options['load_combined_js'] == 'after_page_load'){

			$internal_js[] = array('src'=>$main_js_url);

			$html = str_replace('</body>','<script>'.lazy_load_images().'</script></body>',$html);

		}else{

			$html = str_replace('</body>','<script>'.lazy_load_images().'</script></body>',$html);

			$html = str_replace('</body>','<script defer="defer" id="main-js" src="'.$main_js_url.'"></script></body>',$html);

		}

    }else{

		$html = str_replace('</body>','<script defer="defer" id="main-js" src="'.$main_js_url.'"></script></body>',$html);

	}

    



    wnw_prime_time('html_done');

	return $html;



}

function wnw_insert_content_head($html, $content, $pos){

	if($pos == 1){

		$html = preg_replace('/<style/',  $content.'<style', $html, 1);

	}elseif($pos == 2){

		$html = preg_replace('/<link/',  $content.'<link', $html, 1);

	}else{

		$html = preg_replace('/<script/',  $content.'<script', $html, 1);

	}

	return $html;

}

function lazy_load_images(){

    global $home_url, $full_url_without_param, $image_home_url,$wnw_exclude_lazyload,$main_css_url, $lazy_load_js,$document_root,$optimize_image_array,$lazyload_inner_js,$lazyload_inner_ads_js_arr,$google_font,$js_delay_load,$internal_css_delay_load, $internal_js,$internal_js_delay_load, $google_fonts_delay_load;
	
    $script = 'var internal_js_delay_load = '.$internal_js_delay_load.';
	    var js_delay_load = '.$js_delay_load.';
		var internal_css_delay_load = '.$internal_css_delay_load.';
		var google_fonts_delay_load = '.$google_fonts_delay_load.';
		var lazy_load_js='.json_encode($lazy_load_js).';
		
		var internal_js='.json_encode($internal_js).';

        var lazy_load_css='.json_encode($main_css_url).';

        var optimize_images_json='.json_encode($optimize_image_array).';

		var googlefont='.json_encode($google_font).';

        var lazyload_inner_js = '.json_encode($lazyload_inner_js).';

        var lazyload_inner_ads_js = '.json_encode($lazyload_inner_ads_js_arr).';

        var wnw_first_js = false;
		
		var wnw_int_first_js = false;

        var wnw_first_inner_js = false;

        var wnw_first_css = false;

		var wnw_first_google_css = false;

        var wnw_first = false;

        var wnw_optimize_image = false;

		var mousemoveloadimg = false;
        var page_is_scrolled = false;
        /*load_extJS();*/

		setTimeout(function(){load_googlefont();},google_fonts_delay_load);

        window.addEventListener("load", function(event){

			setTimeout(function(){load_extJS();},js_delay_load);
			setTimeout(function(){load_intJS();},internal_js_delay_load);


			setTimeout(function(){load_extCss();},internal_css_delay_load);

			

            lazyloadimages(0);

        });

        window.addEventListener("scroll", function(event){
           
           load_all_js();

		   load_extCss();

		});

		window.addEventListener("mousemove", function(){ 

			load_all_js();

			load_extCss();

		});

		window.addEventListener("touchstart", function(){ 

			load_all_js();

			load_extCss();

		});

		function load_googlefont(){

			if(wnw_first_google_css == false && typeof googlefont != undefined && googlefont != null && googlefont.length > 0){

				googlefont.forEach(function(src) {

					var load_css = document.createElement("link");

					load_css.rel = "stylesheet";

					load_css.href = src;

					load_css.type = "text/css";

					var godefer2 = document.getElementsByTagName("link")[0];

					if(godefer2 == undefined){

						document.getElementsByTagName("head")[0].appendChild(load_css);

					}else{

						godefer2.parentNode.insertBefore(load_css, godefer2);

					}

				});

				

				wnw_first_google_css = true;

			}

		}

		function load_all_js(){

			if(wnw_first_js == false && lazy_load_js.length > 0){
                
				load_intJS();
				load_extJS(0);
        
			}

			if(mousemoveloadimg == false){

				var top = this.scrollY;

				lazyloadimages(top);

				mousemoveloadimg = true;

			}

		}

        function load_innerJS(){
            if(wnw_first_inner_js == false){

                for(var key in lazyload_inner_js){

                    if(lazyload_inner_js[key] != ""){

                        var s = document.createElement("script");

                        s.innerHTML =lazyload_inner_js[key];

                        document.getElementsByTagName("body")[0].appendChild(s);

                    }

                }

                wnw_first_inner_js = true;

            }

        }
var inner_js_counter = -1;
		var s={};
         function load_extJS() {
			if(wnw_first_js){
				return;
			}
			console.log(inner_js_counter,lazy_load_js.length,"js loop");
            if(inner_js_counter+1 < lazy_load_js.length){				inner_js_counter++;				var script = lazy_load_js[inner_js_counter];				console.log(script,script["src"],"inner js");
				if(script["src"] !== undefined){					
					s[inner_js_counter] = document.createElement("script");
					s[inner_js_counter]["type"] = "text/javascript";
					for(var key in script){
						s[inner_js_counter].setAttribute(key, script[key]);
					}
					s[inner_js_counter].onload=function(){
						load_extJS();
					};
					console.log(s[inner_js_counter]);
					document.getElementsByTagName("head")[0].appendChild(s[inner_js_counter]);
					
				}else{
					load_extJS();
				}
			}else{
				console.log("after ext js",inner_js_counter);
				wnw_first_js = true;
				setTimeout(function(){load_innerJS();},100);

			}
			
        }
		var internal_js_loaded = false;
		function load_intJS() {

            if(wnw_int_first_js == false && internal_js.length > 0){
             page_is_scrolled = true;
				var s;
               internal_js.forEach(function(script) {
                    s = document.createElement("script");
                    s["type"] = "text/javascript";
					for(var key in script){
						console.log(key);
						s.setAttribute(key, script[key]);
					}
					console.log(s);
                    document.getElementsByTagName("head")[0].appendChild(s);

                });
				s.onload=function(){
					internal_js_loaded = true;
				};
				
				wnw_int_first_js = true;
            }

        }
	

    var exclude_lazyload = null;

    var win_width = screen.availWidth;

    function load_extCss(){

        if(wnw_first_css == false && lazy_load_css.length > 0){

            lazy_load_css.forEach(function(src) {

                var load_css = document.createElement("link");

                load_css.rel = "stylesheet";

                load_css.href = src;

                load_css.type = "text/css";

                var godefer2 = document.getElementsByTagName("link")[0];

				if(godefer2 == undefined){

					document.getElementsByTagName("head")[0].appendChild(load_css);

				}else{

					godefer2.parentNode.insertBefore(load_css, godefer2);

				}

            });

            wnw_first_css = true;

        }

    }



    

    window.addEventListener("scroll", function(event){

         var top = this.scrollY;

         lazyloadimages(top);

         lazyloadiframes(top);



    });

    setInterval(function(){lazyloadiframes(top);},8000);

    setInterval(function(){lazyloadimages(0);},3000);

    function lazyload_img(imgs,bodyRect,window_height,win_width){

        for (i = 0; i < imgs.length; i++) {



            if(imgs[i].getAttribute("data-class") == "LazyLoad"){

                var elemRect = imgs[i].getBoundingClientRect(),

                offset   = elemRect.top - bodyRect.top;

                if(elemRect.top != 0 && elemRect.top - window_height < 200 ){

                    /*console.log(imgs[i].getAttribute("data-src")+" -- "+elemRect.top+" -- "+window_height);*/

                    var src = imgs[i].getAttribute("data-src") ? imgs[i].getAttribute("data-src") : imgs[i].src ;

                    var srcset = imgs[i].getAttribute("data-srcset") ? imgs[i].getAttribute("data-srcset") : "";

					

                    imgs[i].src = src;

                    if(imgs[i].srcset != null & imgs[i].srcset != ""){

                        imgs[i].srcset = srcset;

                    }

                    delete imgs[i].dataset.class;

                    imgs[i].setAttribute("data-done","Loaded");

                }

            }

        }

    }

    function lazyload_video(imgs,top,window_height,win_width){

        for (i = 0; i < imgs.length; i++) {

            var source = imgs[i].getElementsByTagName("source")[0];

		    if(typeof source != "undefined" && source.getAttribute("data-class") == "LazyLoad"){

                var elemRect = imgs[i].getBoundingClientRect();

        	    if(elemRect.top - window_height < 0 && top > 0){

		            var src = source.getAttribute("data-src") ? source.getAttribute("data-src") : source.src ;

                    var srcset = source.getAttribute("data-srcset") ? source.getAttribute("data-srcset") : "";

                    imgs[i].src = src;

                    if(source.srcset != null & source.srcset != ""){

                        source.srcset = srcset;

                    }

                    delete source.dataset.class;

                    source.setAttribute("data-done","Loaded");

                }

            }

        }

    }

    function lazyloadimages(top){

        var imgs = document.getElementsByTagName("img");

        var ads = document.getElementsByClassName("lazyload-ads");

        var sources = document.getElementsByTagName("video");

        var bodyRect = document.body.getBoundingClientRect();

        var window_height = window.innerHeight;

        var win_width = screen.availWidth;

        lazyload_img(imgs,bodyRect,window_height,win_width);

        lazyload_video(sources,top,window_height,win_width);

    }

    

    lazyloadimages(0);

    function lazyloadiframes(top){

        var bodyRect = document.body.getBoundingClientRect();

        var window_height = window.innerHeight;

        var win_width = screen.availWidth;

        var iframes = document.getElementsByTagName("iframe");

        lazyload_img(iframes,bodyRect,window_height,win_width);

    }';

    return $script;

}

if(!empty($_REQUEST['set-opt'])){

    //echo 'asdfasd'; exit;

    if(empty($_POST['images123'])){

        exit;

    }

    global $full_url_without_param, $home_url, $secure,$image_home_url,$cache_folder_path;

    $images = json_decode(stripslashes($_POST['images123']));

    foreach($images as $image){

        if(!empty($image->url) /*&& !empty($image->width) && $image->width > 5*/ && (strpos($image->url,'.jpg') || strpos($image->url,'.png') || strpos($image->url,'.jpeg'))){

            $img_url = explode('?',$image->url);

            $path = parse_url($image->url, PHP_URL_PATH);

            $image_path = str_replace($home_url,'',$img_url[0]);

            $url_array = explode('/',trim(str_replace($secure,'',$image_path),'/'));

            $full_path = $document_root.$cache_folder_path.'/wnw-images';

            if(!file_exists($full_path)){

                mkdir($full_path);

            }

            for($i=0; $i < count($url_array); $i++){

                $full_path .= '/'.$url_array[$i];

                if($i+1 == count($url_array)){

                    break;

                }

                if(!file_exists($full_path)){

                    mkdir($full_path);

                    create_blank_file($full_path.'/index.php');

                }

            }

            $info = explode('.',$image_path);

            $extension = '.'.end($info);

            //$info = @getimagesize($document_root.$image_path);

            //$extension = image_type_to_extension($info[2]);

            if(!file_exists($full_path.'min'.$extension)){

                $info = @getimagesize($document_root.$image_path);

                if(!empty($info[0])){

                    $new_image1024 = optimize_image($info[0],$img_url[0]);

                }

                file_put_contents($full_path.'min'.$extension, $new_image1024);

            }

            echo $full_path.'min'.$extension;

        }

    }

    //print_r($images);

    exit;

}

function get_curl_url($url){

    $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_VERBOSE, true);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_REFERER, true);

    $User_Agent = 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31';

    $request_headers = array();

    $request_headers[] = 'User-Agent: '. $User_Agent;

    $request_headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

    curl_setopt($ch, CURLOPT_USERAGENT, $agent);

    curl_setopt($ch, CURLOPT_URL,$url);

    $result=curl_exec($ch);

    curl_close($ch);

    return $result;

}

function optimize_image($width,$url){

    $width = $width < 1920 ? $width : 1920;

	echo 'https://w3speedup.com/optimize/basic.php?width='.$width.'&url='.urlencode($url);

    return get_curl_url('https://w3speedup.com/optimize/basic.php?width='.$width.'&url='.urlencode($url));

}





function sanitize_output($buffer){



    $search = '/<!--(.|\s)*?-->/';



    $replace = '';



    $buffer = preg_replace($search, $replace, $buffer);



    return $buffer;

}





function create_log($data){

    global $document_root;

    $f = fopen($document_root.'/cache_log.txt','a');

    fwrite($f,$data."\n");

    fclose($f);

}



function gz_relative_to_absolute_path($url, $string){

    global $image_home_url, $home_url,$document_root,$secure,$fonts_api_links,$speedup_options;

	$url_arr = parse_url($url);

    $url = $home_url.$url_arr['path'];

    $matches = wnw_get_tags_data($string,'url(',')');

	$replaced = array();

    $replaced_new = array();

    $replace_array = explode('/',str_replace('\'','/',$url));

    $replace_array = array_reverse($replace_array);

    unset($replace_array[0]);

    //echo '<pre>'; print_r($matches); echo '</pre>';

    foreach($matches as $match){

		if(strpos($match,'data:') !== false || strpos($match,'(#') !== false){

            continue;

        }

        $org_match = $match;

        //echo '<pre>'; print_r($replace_array); echo '</pre>'; exit();

		$match1 = str_replace(array('url(',')',"url('","')",')',"'",'"','&#039;'), '', html_entity_decode($match));

        $match1 = trim($match1);

		if(strpos($match1,'//') > 7){

            $match1 = substr($match1, 0, 7).str_replace('//','/', substr($match1, 7));

        }

        

        $url_arr = parse_url($match1);

        //$match1 = $url_arr['path'];

		//echo $match1.'<br>';

        if(strpos($match,'fonts.googleapis.com') !== false){

            if(empty($speedup_options['google_fonts'])){

				continue;

			}

            $string = str_replace('@import '.$match.';','', $string);

            parse_str($url_arr['query'], $get_array);

            if(!empty($get_array['family'])){

                $font_array = explode('|',$get_array['family']);

                foreach($font_array as $font){

                    $font_split = explode(':',$font);

                    $fonts_api_links[$font_split[0]] = explode(',',$font_split[1]);

                }

            }

            $fonts_api_links[] = str_replace(array('family=','&'), '', $url_arr['query']);

            continue;

        }

		

		

        if(wnw_isexternal($match1)){

            continue;

        }

		$match1 = str_replace($home_url,$image_home_url,$match1);

		$match1 = $url_arr['path'];

		$match1= trim($match1);

        if(empty($match1)){

            $string = str_replace($org_match, '', $string);

            continue;

        }

        /*if(strpos($match1,'//') !== false ){

            continue;

        }*///commented due to image not optimizing.

        $url_array = explode('/',$match1);

        $image_name = end($url_array);

        $image_start_array = trim($url_array[0]);

        //echo '<pre>'; print_r($url_array); echo '</pre>';

        if(empty($image_start_array)){

            $replacement = $image_home_url.trim($match1);

            if(strpos($replacement,'.jpg') || strpos($replacement,'.png') || strpos($replacement,'.jpeg') ){

                $replacement = str_replace(array("'",$home_url),array('',$image_home_url), $replacement);

            }

        }else{

            $i=1;

            if(strpos($match1,'.jpg') === false && strpos($match1,'.png') === false && strpos($match1,'.jpeg') === false && strpos($match1,'.woff') === false && strpos($match1,'.woff2') === false && strpos($match1,'.svg') === false && strpos($match1,'.ttf') === false  && strpos($match1,'.eot') === false && strpos($match1,'.gif') === false && strpos($match1,'.webp') === false && strpos($match1,'.css') === false ){

                continue;

            }

            $replace_array1 = $replace_array;

            foreach($url_array as $key => $slug){

                $slug = str_replace("'", '', $slug);

                if($slug == '.' ){

					unset($url_array[$key]);

					$i--;

				}elseif($slug == '..' ){

                    if($url != $home_url){

                        unset($replace_array1[$i]);

                    }

                    unset($url_array[$key]);

                }



                $i++;

            }



            $replace_array1 = array_reverse($replace_array1);

            $replacement = trim(implode('/',$replace_array1),'/').'/'.trim(implode('/',$url_array),'/');

            if(strpos($replacement,'jpg') || strpos($replacement,'png') || strpos($replacement,'jpeg') ){

                $replacement = str_replace(array("'",$home_url),array('',$image_home_url), $replacement);

            }



        }

		if(!in_array($image_name , $replaced)){

			$replaced['org'] = $match;

			$replaced_new['match'][] = $match;

            $replaced_new['replacement'][] = 'url('.$replacement.')';

        }

        

    }

	$string = str_replace($replaced_new['match'], $replaced_new['replacement'], $string);



    return $string;

}



function get_random_string(){

    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';

     $string = '';

     $random_string_length = 20;

     $max = strlen($characters) - 1;

     for ($i = 0; $i < $random_string_length; $i++) {

          $string .= $characters[mt_rand(0, $max)];

     }

    return $string;

}



 function rrmdir($dir) {

  if (is_dir($dir)) {

    $objects = scandir($dir);

    foreach ($objects as $object) {

      if ($object != "." && $object != "..") {

        if (filetype($dir."/".$object) == "dir")

            rrmdir($dir."/".$object);

        else{

			/*if(time() - filemtime($dir."/".$object) > 86400 ){*/

				@unlink($dir."/".$object);

			//}

        }

      }

    }

    reset($objects);

    @rmdir($dir);

  }

 }



function wnw_remove_cache_files_hourly_event_callback() {

    global $document_root,$cache_folder_path, $wpdb;

	$wpdb->query("DROP TABLE IF EXISTS wp_speedup");

	wnw_opti_activation();

	rrmdir($document_root.$cache_folder_path.'/wnw-cache');

	Find_size_fun_call();

}



function wnw_remove_cache_redirect(){

	global $full_url;

	header("Location:".add_query_arg(array('delete_wp_speedup_cache'=>1),remove_query_arg('delete-wnw-cache',false)));

	exit;

}

if(!empty($_REQUEST['delete-wnw-cache'])){

	add_action('init','wnw_remove_cache_files_hourly_event_callback');

    add_action('init','wnw_remove_cache_redirect');

}



function get_image_cache_path($url, $title, $alt){

    global $home_url, $full_url_without_param, $image_home_url,$document_root, $optimize_image_array;;

    //echo '----'.$url.'------';

    $img_url = explode('?',$url);

    $parse_url = parse_url($url);

    $image_path = $parse_url['path'];

    $url_array = explode('/',trim($image_path,'/'));

    $full_path = '/wnw-images';

    for($i=0; $i < count($url_array); $i++){

        $full_path .= '/'.$url_array[$i];

    }

    $info = explode('.',$image_path);

    $extension = '.'.end($info);

    //$info = @getimagesize($document_root.$image_path);

    //$extension = image_type_to_extension($info[2]);

    $new_path = $full_path.'min'.$extension;

    if(!file_exists($document_root.$new_path) && ($extension == '.jpg' || $extension == '.jpeg' || $extension == '.png') && !wnw_isexternal($url) && count($optimize_image_array) < 30 ){

        $info = @getimagesize($document_root.$image_path);

        $optimize_image_array[] = array('url'=>$img_url[0],'width'=>$info[0],'title'=>'','alt'=>'','win_width'=>0);

    }elseif(file_exists($document_root.$new_path) ){

        $info = @getimagesize($document_root.$image_path);

        $info_new = @getimagesize($document_root.$new_path);

        //echo $info[0].' != '.$info_new[0].$new_path.'<br>';

        if($info[0] != $info_new[0] && $info_new[0] != 1920 ){

            unlink($document_root.$new_path);

        }



    }

    return $new_path;

}

function optimize_srcset($srcset){

    global $home_url, $full_url_without_param, $image_home_url,$document_root, $optimize_image_array, $secure;

    $images_array = explode(',',$srcset);

    $final_srcset_array = array();

    foreach($images_array as $images_full){

        $images = $secure.str_replace(array('https://','http://','//'),'',trim($images_full));

        $images = str_replace($image_home_url,$home_url,$images);

        $image = explode(' ',trim($images));

        $img_url = explode('?',$image[0]);

        if(wnw_isexternal($img_url[0])){

           $final_srcset_array[] = $images;

            continue;

        }

        $image_path = str_replace($home_url,'',$img_url[0]);

        $url_array = explode('/',trim($image_path,'/'));

        $full_path = '/wnw-images';

        for($i=0; $i < count($url_array); $i++){

            $full_path .= '/'.$url_array[$i];

        }

        $info = explode('.',$image_path);

        $extension = '.'.end($info);

        //$info = @getimagesize($document_root.$image_path);

        //$extension = image_type_to_extension($info[2]);

        $new_path = $full_path.'min'.$extension;

        if(!file_exists($document_root.$new_path) && ($extension == '.jpg' || $extension == '.jpeg' || $extension == '.png') && count($optimize_image_array) < 30){

            $optimize_image_array[] = array('url'=>$img_url[0],'width'=>$info[0],'title'=>'','alt'=>'','win_width'=>0);

            $final_srcset_array[] = $img_url[0].' '.$image[1];

        }else{

            $final_srcset_array[] = $images_full;

        }

    }

    return implode(', ',$final_srcset_array);

}

function compress_js($html){

    $html = JSMin::minify($html);

    return $html;

}

function wnw_microtime_float()

{

    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);

}

function wnw_setAllLinks($data){
	global $speedup_options;
    $comment_tag = wnw_get_tags_data($data,'<!--','-->');

    foreach($comment_tag as $comment){

        $data = str_replace($comment,'',$data);

    }
	if($speedup_options['js'] == 'on'){
		$script_tag = wnw_get_tags_data($data,'<script','</script>');
	}
	if(!empty($speedup_options['lazy_load'])){
		$img_tag = wnw_get_tags_data($data,'<img','>');
	}
	if($speedup_options['css'] == 'on'){
		$link_tag = wnw_get_tags_data($data,'<link','>');
	}
    //$style_tag = wnw_get_tags_data($data,'<style','</style>');

    $iframe_tag = wnw_get_tags_data($data,'<iframe','>');

    $video_tag = wnw_get_tags_data($data,'<video','</video>');

    return array('script'=>$script_tag,'img'=>$img_tag,'link'=>$link_tag,'style'=>$style_tag,'iframe'=>$iframe_tag,'video'=>$video_tag);





}



function wnw_get_tags_data($data,$start_tag,$end_tag){

    $data_exists = 0; $i=0;

    $tag_char_len = strlen($start_tag);

    $end_tag_char_len = strlen($end_tag);

    $script_array = array();

    while($data_exists != -1 && $i<500) {

        $data_exists = strpos($data,$start_tag,$data_exists);

        if(!empty($data_exists)){

            $end_tag_pointer = strpos($data,$end_tag,$data_exists);

            $script_array[] = substr($data, $data_exists, $end_tag_pointer-$data_exists+$end_tag_char_len);

            $data_exists = $end_tag_pointer;

        }else{

            $data_exists = -1;

        }

        $i++;

    }

    return $script_array;

}


if(!empty($_REQUEST['testing'])){
 echo get_site_optimized(file_get_contents(__DIR__.'/test.html')); exit;
}
 

function wnw_start_optimization_callback() {

	  /* global $wpdb, $sitename, $image_home_url,$home_url,$full_url,$full_url_without_param, $secure,$additional_img,$wnw_exclude_lazyload,$exclude_css,$full_url_array,$main_css_url, $current_user ;

	  echo home_url();

	  echo '--1---test ---';

	  echo $home_url;

	    echo '---test ---002';

	  echo $full_url ;

	  exit; */

	  

    ob_start("get_site_optimized");



}



function wnw_ob_end_flush() {

	if (ob_get_level() != 0) {

		ob_end_flush();

     }

}

if(!is_admin() && !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')){

	register_shutdown_function('wnw_ob_end_flush');

	add_action('wp_loaded', 'wnw_start_optimization_callback',1);

}



if(!empty($_REQUEST['optimize_image'])){

	global $document_root;

	$image_url = $_REQUEST['url'];

	$image_width = !empty($_REQUEST['width']) ? $_REQUEST['width'] : '';

	$url_array = parse_url($image_url);

	$image_size = !empty($image_width) ? array($image_width) : getimagesize($document_root.$url_array['path']);

    $optmize_image = optimize_image($image_size[0],$image_url);

    $optimize_image_size = @imagecreatefromstring($optmize_image);

    if(empty($optimize_image_size)){

        echo 'invalid image'; exit;

    }else{    

        $image_type = array('gif','jpg','png','jpeg');

        $type = explode('.',$image_url);

        $type = array_reverse($type);

        if(in_array($type[0],$image_type)){

            rename($document_root.$url_array['path'],$document_root.$url_array['path'].'org.'.$type[0]);

            file_put_contents($document_root.$url_array['path'],$optmize_image);

			chmod($document_root.$url_array['path'], 0775);

			echo $document_root.$url_array['path'];

        }

        

    }

    

   

   exit;

	

}

add_action( 'admin_bar_menu', 'toolbar_link_to_wp_speedup', 999 );



function toolbar_link_to_wp_speedup( $wp_admin_bar ) {

	global $full_url;

	$database_size = round(get_option('wp_speedup_database_size',false),2);

	$filesize = round(get_option('wp_speedup_filesize',false),2);

	$args = array(

		'id'    => 'wp_speedup',

		'title' => 'Delete Wp Speed cache <div class="cache_size"><div><span>Database Size</span>&nbsp;&nbsp;&nbsp;<span class="database_size">'.$database_size.'MB</span></div><div><span>File Size</span>&nbsp;&nbsp;&nbsp;<span class="cache_folder_size">'.$filesize.'MB</span></div></div><style>.wp-speedup-page .cache_size{display:none;}.wp-speedup-page:hover .cache_size{background: #000;padding: 5px 10px !important;display:block;}</style>',

		'href'  => add_query_arg(array('delete-wnw-cache'=>1),$full_url),

		'meta'  => array( 'class' => 'wp-speedup-page' )

	);

	$wp_admin_bar->add_node( $args );

}

function wp_speedup_register_settings() {

   add_option( 'myplugin_option_name', 'This is my option value.');

   register_setting( 'myplugin_options_group', 'myplugin_option_name', 'myplugin_callback' );

}

add_action( 'admin_init', 'wp_speedup_register_settings' );

function wp_speedup_register_options_page() {

  add_options_page('Wp Speedup', 'Wp Speedup', 'manage_options', 'wp_speedup', 'wp_speedup_options_page');

}

add_action('admin_menu', 'wp_speedup_register_options_page');

function wp_speedup_options_page()

{

	load_template( dirname( __FILE__ ) . "/includes/admin.php" );	

} 

function database_size(){

	global  $wpdb;

	$results = $wpdb->get_results( "SHOW TABLE STATUS LIKE 'wp_speedup'");

	

	return ($results[0]->Data_length / 1024) / 1024;

	

}

function get_cache_file_size(){

	//return foldersize($cache_folder_path.'/wnw-cache');

	global $document_root,$cache_folder_path;

	 $dir = $document_root.$cache_folder_path.'/wnw-cache';//exit;

	 $size = 0;

    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {

        $size += is_file($each) ? filesize($each) : folderSize($each);

    }

    return ($size / 1024) / 1024;

}



function foldersize($path) {

    $total_size = 0;

    $files = scandir($path);

    $cleanPath = rtrim($path, '/'). '/';



    foreach($files as $t) {

        if ($t<>"." && $t<>"..") {

            $currentFile = $cleanPath . $t;

            if (is_dir($currentFile)) {

                $size = foldersize($currentFile);

                $total_size += $size;

            }

            else {

                $size = filesize($currentFile);

                $total_size += $size;

            }

        }   

    }

    return $total_size;

}

if ( ! wp_next_scheduled( 'Find_size_fun' ) ) {

  wp_schedule_event( time(), 'hourly', 'Find_size_fun' );

}



add_action( 'Find_size_fun', 'Find_size_fun_call' );



function Find_size_fun_call() {

 //global $filesize,$database_size;

 $database_size = database_size();

$filesize = get_cache_file_size();

update_option('wp_speedup_database_size',$database_size,true);

update_option('wp_speedup_filesize',$filesize,true);



}