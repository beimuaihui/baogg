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
class Doc extends Baogg\File
{
    static function formatContent($content,$fileName='') {
		//header ( 'Content-type: application/doc' );
		//header ( 'Content-Disposition: attachment; filename="' . $fileName . '.doc"' );
		$content = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
       xmlns:w="urn:schemas-microsoft-com:office:word" 
       xmlns="[url=http://www.w3.org/TR/REC-html40]http://www.w3.org/TR/REC-html40[/url]">
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>' . $fileName . '</title>
                </head>
                <body>'.$content.'</body></html>';
		return $content;
	}
	static function genFile($dir,$filename,$content)
	{
		$content=self::formatContent($content,$filename);
		self::mkdir(BAOGG_UPLOAD_DIR.$dir);
		file_put_contents(BAOGG_UPLOAD_DIR.$dir.$filename, $content);
		return BAOGG_FILE_URL.$dir.$filename;
	}
}