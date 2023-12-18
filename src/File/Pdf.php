<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: File.php 240 2011-06-13 13:02:06Z beimuaihui $
 * Importance!!! please disable selinux ,or this will cause error!,all function will be invalid if selinux enabled.
 */
namespace Baogg\File;
class Pdf extends Baogg\File
{    
	static function genFile($dir,$filename,$url)
	{
		self::mkdir(BAOGG_UPLOAD_DIR.$dir);
		$filename=self::fixFileName($filename);
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		   $bin=BAOGG_UPLOAD_DIR.'bin/wkhtmltopdf/wkhtmltopdf.exe';
		} else {
		   $bin='wkhtmltopdf';
		}
		
		$cmd=sprintf('"%s" -q "%s" "%s"',$bin,$url,BAOGG_UPLOAD_DIR.$dir.$filename);
		//echo $bin.' '.$url.' '.BAOGG_UPLOAD_DIR.$dir,$filename.'<br />';		
		exec($cmd);		
		
		return BAOGG_FILE_URL.$dir.$filename;
	}


	static function downloadByHtml($html=''){
		$pdf = new Baogg_File_WKPDF();

		$pdf->set_html($html);
		$pdf->args_add('--encoding','utf-8');
		//echo __FILE__.__LINE__.'<pre>';echo $html;exit;
		$pdf->render();

		$pdf->output(Baogg_File_WKPDF::$PDF_DOWNLOAD,'export.pdf');
	}
}