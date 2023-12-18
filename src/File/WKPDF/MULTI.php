<?php	
	/**
	 * This class, which extends WKPDF is for generating a PDF with multiple pages without using CSS page-break.
	 */
	class Baogg_File_WKPDF_MULTI extends Baogg\File\WKPDF {
		/**
		 * An array of HTML files, this is used internally.
		 * @var array Array of URLs to files.
		 */
		private $html_files=array();
		/**
		 * An array of URLs.
		 * @var array Array of URLs to pages.
		 */
		private $html_urls=array();
		/**
		 * This function doesn't make sense in this context.
		 */
		public function set_html($html){
			die('Calling set_html() not allowed for WKPDF_MULTI class.');
		}
		/**
		 * This function doesn't make sense in this context.
		 */
		public function set_url($url){
			die('Calling set_url() not allowed for WKPDF_MULTI class.');
		}
		/**
		 * Add a new HTML page.
		 * @param string $html Content of HTML page.
		 */
		public function add_html($html){
			do{
				$file=$GLOBALS['WKPDF_BASE_PATH'].'tmp/'.mt_rand().'.html';
			} while(file_exists($file));
			if(!file_put_contents($file,$html))throw new Exception('WKPDF write temporary file failed.');
			$this->html_urls[]=$GLOBALS['WKPDF_BASE_SITE'].$GLOBALS['WKPDF_BASE_PATH'].'tmp/'.basename($file);
			$this->html_files[]=$file;
		}
		/**
		 * Add a new page from URL.
		 * @param string $html URL to HTML page.
		 */
		public function add_url($url){
			$this->html_urls[]=$url;
		}
		/**
		 * Cleans TMP folder from used files.
		 */
		protected function clean_tmp(){
			foreach($this->html_files as $file)unlink($file);
		}
		/**
		 * Convert HTML pages to PDF.
		 */
		public function render(){
			$urls='"'.implode('" "',$this->html_urls).'"';
			if($urls=='""'){
				$this->add_html('<html><body><!--EMPTY PDF--></body></html>');
				$urls='"'.implode('" "',$this->html_urls).'"';
			}
			$this->pdf=self::_pipeExec(
				$this->cmd
				.(($this->copies>1)?' --copies '.$this->copies:'')				// number of copies
				.' --orientation '.$this->orient								// orientation
				.' --page-size '.$this->size									// page size
				.($this->toc?' --toc':'')										// table of contents
				.($this->grayscale?' --grayscale':'')							// grayscale
				.(($this->title!='')?' --title "'.$this->title.'"':'')			// title
				.' '.$urls.' -'													// URL and use STDOUT
			);
			if($this->pdf['stdout']=='')self::_retError('WKPDF program error.',$this->pdf);
			if(((int)$this->pdf['return'])>1)self::_retError('WKPDF shell error.',$this->pdf);
			$this->status=$this->pdf['stderr'];
			$this->pdf=$this->pdf['stdout'];
			$this->clean_tmp();
		}
	}