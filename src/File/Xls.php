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
class Xls extends Baogg\File
{
    static function formatContent($content,$filename='') {
		//header ( 'Content-type: application/doc' );
		//header ( 'Content-Disposition: attachment; filename="' . $fileName . '.doc"' );
		$content =  '<html xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:x="urn:schemas-microsoft-com:office:excel"
        xmlns="[url=http://www.w3.org/TR/REC-html40]http://www.w3.org/TR/REC-html40[/url]">
        <head>
        <meta http-equiv="expires" content="Mon, 06 Jan 1999 00:00:01 GMT">
        <meta http-equiv=Content-Type content="text/html; charset=UTF-8">
        <!--[if gte mso 9]><xml>
        <x:ExcelWorkbook>
        <x:ExcelWorksheets>
        <x:ExcelWorksheet>
        <x:Name></x:Name>
        <x:WorksheetOptions>
        <x:DisplayGridlines/>
        </x:WorksheetOptions>
        </x:ExcelWorksheet>
        </x:ExcelWorksheets>
        </x:ExcelWorkbook>
        </xml><![endif]-->
        </head>
        <body link=blue vlink=purple leftmargin=0 topmargin=0><table width="100%" border="0" cellspacing="0" cellpadding="0">'.$content.'</table></body></html>';
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