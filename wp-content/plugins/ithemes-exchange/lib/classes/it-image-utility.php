<?php

/*
Utility functions for manipulating images
Written by Chris Jean for iThemes.com
Version 1.0.4

Version History
	1.0.0 - 2011-10-05 - Chris Jean
		Built from it-file-utility.php version 2.1.0
	1.0.1 - 2012-09-24 - Chris Jean
		create_favicon now forces the permissions to 644.
	1.0.2 - 2012-12-03 - Chris Jean
		Added compatibility fix for WordPress 3.5 to the resize_image function as the wp_load_image function is now
			deprecated.
	1.0.3 - 2013-01-09 - Chris Jean
		Removed unnecessary argument to the save_ico function call.
	1.0.4 - 2013-06-25 - Chris Jean
		Changed function declarations to "public static".
*/


if ( !class_exists( 'ITImageUtility' ) ) {
	class ITImageUtility {
		public static function get_image_dimensions( $file ) {
			it_classes_load( 'it-file-utility.php' );
			
			if ( is_numeric( $file ) ) {
				$file_info = ITFileUtility::get_file_attachment( $file );
				
				if ( false === $file_info )
					return new WP_Error( 'error_loading_image_attachment', "Could not find requested file attachment ($file)" );
				
				$file = $file_info['file'];
			}
			
			list ( $width, $height, $type ) = getimagesize( $file );
			
			return array( $width, $height );
		}
		
		public static function resize_image( $file, $max_w = 0, $max_h = 0, $crop = true, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {
			it_classes_load( 'it-file-utility.php' );
			
			if ( is_numeric( $file ) ) {
				$file_info = ITFileUtility::get_file_attachment( $file );
				
				if ( false === $file_info )
					return new WP_Error( 'error_loading_image_attachment', "Could not find requested file attachment ($file)" );
				
				$file = $file_info['file'];
			}
			else
				$file_attachment_id = '';
			
			if ( preg_match( '/\.ico$/', $file ) )
				return array( 'file' => $file, 'url' => ITFileUtility::get_url_from_file( $file ), 'name' => basename( $file ) );
			
			
			if ( version_compare( $GLOBALS['wp_version'], '3.4.9', '>' ) ) {
				// Compat code taken from pre-release 3.5.0 code.
				
				if ( ! file_exists( $file ) )
					return new WP_Error( 'error_loading_image', sprintf( __( 'File &#8220;%s&#8221; doesn&#8217;t exist?' ), $file ) );
				
				if ( ! function_exists('imagecreatefromstring') )
					return new WP_Error( 'error_loading_image', __( 'The GD image library is not installed.' ) );
				
				// Set artificially high because GD uses uncompressed images in memory
				@ini_set( 'memory_limit', apply_filters( 'image_memory_limit', WP_MAX_MEMORY_LIMIT ) );
				$image = imagecreatefromstring( file_get_contents( $file ) );
				
				if ( ! is_resource( $image ) )
					return new WP_Error( 'error_loading_image', sprintf( __( 'File &#8220;%s&#8221; is not an image.' ), $file ) );
			}
			else {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				
				$image = wp_load_image( $file );
				if ( ! is_resource( $image ) )
					return new WP_Error( 'error_loading_image', $image );
			}
			
			list( $orig_w, $orig_h, $orig_type ) = getimagesize( $file );
			$dims = ITImageUtility::_image_resize_dimensions( $orig_w, $orig_h, $max_w, $max_h, $crop );
			if ( ! $dims )
				return new WP_Error( 'error_resizing_image', "Could not resize image" );
			list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
			
			
			if ( ( $orig_w == $dst_w ) && ( $orig_h == $dst_h ) )
				return array( 'file' => $file, 'url' => ITFileUtility::get_url_from_file( $file ), 'name' => basename( $file ) );
			
			if ( ! $suffix )
				$suffix = "resized-image-${dst_w}x${dst_h}";
			
			$info = pathinfo( $file );
			$dir = $info['dirname'];
			$ext = $info['extension'];
			$name = basename( $file, ".${ext}" );
			
			if ( ! is_null( $dest_path ) && $_dest_path = realpath( $dest_path ) )
				$dir = $_dest_path;
			$destfilename = "${dir}/${name}-${suffix}.${ext}";
			
			
			if ( file_exists( $destfilename ) ) {
				if ( filemtime( $file ) > filemtime( $destfilename ) )
					unlink( $destfilename );
				else
					return array( 'file' => $destfilename, 'url' => ITFileUtility::get_url_from_file( $destfilename ), 'name' => basename( $destfilename ) );
			}
			
			
			// ImageMagick cannot resize animated PNG files yet, so this only works for
			// animated GIF files.
			$animated = false;
			if ( ITImageUtility::is_animated_gif( $file ) ) {
				$coalescefilename = "${dir}/${name}-coalesced-file.${ext}";
				
				if ( ! file_exists( $coalescefilename ) )
					system( "convert $file -coalesce $coalescefilename" );
				
				if ( file_exists( $coalescefilename ) ) {
					system( "convert -crop ${src_w}x${src_h}+${src_x}+${src_y}! $coalescefilename $destfilename" );
					
					if ( file_exists( $destfilename ) ) {
						system( "mogrify -resize ${dst_w}x${dst_h} $destfilename" );
						system( "convert -layers optimize $destfilename" );
						
						$animated = true;
					}
				}
			}
			
			
			if ( ! $animated ) {
				$newimage = imagecreatetruecolor( $dst_w, $dst_h );
				
				// preserve PNG transparency
				if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) ) {
					imagealphablending( $newimage, false );
					imagesavealpha( $newimage, true );
				}
				
				imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
				
				// we don't need the original in memory anymore
				if ( $orig_type == IMAGETYPE_GIF ) {
					if ( ! imagegif( $newimage, $destfilename ) )
						return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
				}
				elseif ( $orig_type == IMAGETYPE_PNG ) {
					if ( ! imagepng( $newimage, $destfilename ) )
						return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
				}
				else {
					// all other formats are converted to jpg
					$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
					if ( ! imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality ) ) )
						return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
				}
				
				imagedestroy( $newimage );
			}
			
			imagedestroy( $image );
			
			
			// Set correct file permissions
			$stat = stat( dirname( $destfilename ) );
			$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
			@ chmod( $destfilename, $perms );
			
			
			return array( 'file' => $destfilename, 'url' => ITFileUtility::get_url_from_file( $destfilename ), 'name' => basename( $destfilename ) );
		}
		
		// Customized image_resize_dimensions() from 2.6.3 wp-admin/includes/media.php (cheanged to resize to fill on crop)
		public static function _image_resize_dimensions( $orig_w, $orig_h, $dest_w = 0, $dest_h = 0, $crop = false ) {
			if ( ( $orig_w <= 0 ) || ( $orig_h <= 0 ) )
				return new WP_Error ( 'error_resizing_image', "Supplied invalid original dimensions ($orig_w, $orig_h)" );
			if ( ( $dest_w < 0 ) || ( $dest_h < 0 ) )
				return new WP_Error ( 'error_resizing_image', "Supplied invalid destination dimentions ($dest_w, $dest_h)" );
			
			
			if ( ( $dest_w == 0 ) || ( $dest_h == 0 ) )
				$crop = false;
			
			
			$new_w = $dest_w;
			$new_h = $dest_h;
			
			$s_x = 0;
			$s_y = 0;
			
			$crop_w = $orig_w;
			$crop_h = $orig_h;
			
			
			if ( $crop ) {
				$cur_ratio = $orig_w / $orig_h;
				$new_ratio = $dest_w / $dest_h;
				
				if ( $cur_ratio > $new_ratio ) {
					$crop_w = floor( $orig_w / ( ( $dest_h / $orig_h ) / ( $dest_w / $orig_w ) ) );
					$s_x = floor( ( $orig_w - $crop_w ) / 2 );
				}
				elseif ( $new_ratio > $cur_ratio ) {
					$crop_h = floor( $orig_h / ( ( $dest_w / $orig_w ) / ( $dest_h / $orig_h ) ) );
					$s_y = floor( ( $orig_h - $crop_h ) / 2 );
				}
			}
			else
				list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
			
			
			return array( 0, 0, $s_x, $s_y, $new_w, $new_h, $crop_w, $crop_h );
		}
		
		// Can only detect animated GIF files, which is fine because ImageMagick doesn't seem
		// to be able to resize animated PNG (MNG) files yet.
		public static function is_animated_gif( $file ) {
			$filecontents=file_get_contents($file);
			
			$str_loc=0;
			$count=0;
			while ($count < 2) # There is no point in continuing after we find a 2nd frame
			{
				$where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
				if ($where1 === FALSE)
				{
					break;
				}
				else
				{
					$str_loc=$where1+1;
					$where2=strpos($filecontents,"\x00\x2C",$str_loc);
					if ($where2 === FALSE)
					{
						break;
					}
					else
					{
						if ($where1+8 == $where2)
						{
							$count++;
						}
						$str_loc=$where2+1;
					}
				}
			}
			
			if ($count > 1)
				return(true);
			return(false);
		}
		
		public static function create_favicon( $dir_name, $image, $sizes = false ) {
			it_classes_load( 'it-file-utility.php' );
			
			require_once( dirname( __FILE__ ) . '/classes/class-php-ico.php' );
			
			
			if ( ! is_array( $sizes ) || ( false === $sizes ) ) {
				$sizes = array(
					array( 16, 16 ),
					array( 24, 24 ),
					array( 32, 32 ),
					array( 48, 48 ),
				);
			}
			
			
			$ico = new PHP_ICO( $image, $sizes );
			
			$path = ITFileUtility::get_writable_directory( $dir_name );
			
			while ( empty( $name ) || file_exists( "$path/$name" ) )
				$name = ITUtility::get_random_string( array( 6, 10 ) ) . '.ico';
			
			
			$result = $ico->save_ico( "$path/$name" );
			
			if ( true != $result )
				return false;
			
			
			@chmod( "$path/$name", 0644 );
			
			return "$path/$name";
		}
	}
}
