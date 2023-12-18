<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: File.php 240 2011-06-13 13:02:06Z beimuaihui $
 */
namespace Baogg\File;
class Zip extends Baogg\File
{
   
	static function genFile($dir,$filename,$file_list=array())
	{
		
		//$content=self::formatContent($content,$filename);
		self::mkdir(BAOGG_UPLOAD_DIR.$dir);
		//file_put_contents(BAOGG_UPLOAD_DIR.$dir.$filename, $content);		
		
		$zip = new ZipArchive;
		$res = $zip->open(BAOGG_UPLOAD_DIR.$dir.$filename, ZIPARCHIVE::OVERWRITE);
		//echo '<pre>';print_r($file_list);var_dump(realpath(BAOGG_UPLOAD_DIR.$dir.$filename));var_dump( $res);
		if ($res === TRUE) {			
			foreach((array)$file_list as $v){
				if(is_string($v)){
					$zip->addFile(BAOGG_UPLOAD_DIR.$v);
				}else if(is_array($v) && isset($v['filename']) && isset($v['localname'])){
					//except for string
					$zip->addFile(BAOGG_UPLOAD_DIR.$v['filename'],$v['localname']);
				}else if(is_array($v) &&  isset($v['filename'])){
					$zip->addFile(BAOGG_UPLOAD_DIR.$v['filename']);
				}
			}
		    		   
		    $zip->close();		    
		} else {
		    return false;
		}
		return BAOGG_FILE_URL.$dir.$filename;
	}
}