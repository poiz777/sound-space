<?php
	/**
	 * Author      : Poiz
	 * Date        : 17/08/16
	 * Time        : 20:20
	 * FileName    : DirectoryBrowser.php
	 * ProjectName : dir
	 */
	namespace App\CodePool\Files;

	class FolderBrowser {

		/**
		 * THE VALUE (ARRAY) TO BE RETURNED
		 * AFTER PROCESSING THE DIRECTORY CONTENTS
		 * @var array
		 */
		protected static $returnable    = array();

		/**
		 * A FLAG THAT DETERMINES WHETHER THE RETURNED ARRAY
		 * SHOULD BE NUMERICALLY INDEXED OR ASSOCIATIVE (IE WITH STRINGS)
		 * USE "int" FOR NUMERIC INDEXES OR "string" FOR ASSOCIATIVE.
		 * @var string
		 */
		protected static $returnIndex   = "int";

		/**
		 * A FLAG THAT DETERMINES WHETHER THE RETURNED VALUE
		 * SHOULD BE THE FULL PATH TO THE FILE OR JUST THE NORMAL BASENAME
		 * SET TO false TO BASENAME.
		 * @var string
		 */
		protected static $useFullPath   = true;

		/**
		 * A FLAG THAT DETERMINES WHETHER OR NOT
		 * TO RETURN THE RESULTING ARRAY AS A MULTIDIMENSIONAL ARRAY.
		 * FALSE    => RETURNS MULTIDIMENSIONAL ARRAY
		 * TRUE     => RETURNS FLAT ARRAY
		 * @var string
		 */
		protected static $flatReturn    = false;

		protected static $cluster       = null;
		protected static $lastKey       = null;
		protected static $asObject      = false;
		protected static $count         = -1;
		protected static $passes        = ['p'=>[]];
		protected static $pool          = "";
		protected static $block         = [];
		protected static $html          = [];
		protected static $openDir       = "<span class='fa fa-folder-open' style='font-size:18px;'aria-hidden='true' ></span>";
		protected static $fileIcon      = "<span class='fa fa-file-text' style='font-size:15px;' aria-hidden='true' ></span>";
		protected static $closedDir     = "<span class='fa fa-folder' aria-hidden='true' style='font-size:20px;' ></span>";
		protected static $rootDir;


		public function __construct(array $config=null) {
			self::setConfig($config);
		}

		public static function setConfig(array $config=null){
			if( isset($config['rootDir'])){
				self::$rootDir  = $config['rootDir'];
				self::$cluster  = self::deepScan(self::$rootDir);;
				self::$html     = self::buildHTMLTree(self::getDirectoryPrimaryChildren());
			}
			if( isset($config['asObject'])){
				self::$asObject = $config['asObject'];
			}
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY,
		 * EXTRACTING FILES WITH .png EXTENSION.
		 * THIS IS A SHORTCUT FOR PNG FILES ONLY.
		 *
		 * NOTE: THIS METHOD WILL SCAN ONLY THE GIVEN DIRECTORY.
		 * FOR, RECURSIVE SCANNING SET THE $recursive PARAMETER TO TRUE.
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $dir_full_path
		 *
		 * SET TO TRUE FOR RECURSIVE SCANNING.
		 * @param $recursive
		 *
		 * RETURNS AN ARRAY CONTAINING ALL PNG IMAGES
		 * @return array
		 */
		public static function extractPNGFiles($dir_full_path=null, $recursive=false){
			$dir_full_path      = ($dir_full_path)? $dir_full_path : self::$rootDir;
			if(!$dir_full_path){return [];}
			if($recursive){
				return self::deepScan($dir_full_path, "#\.png$#");
			}
			return self::extractFilesWithExtension($dir_full_path, 'png');
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY,
		 * EXTRACTING FILES WITH .jpg EXTENSION.
		 * THIS IS A SHORTCUT FOR JPG FILES ONLY
		 *
		 * NOTE: THIS METHOD WILL SCAN ONLY THE GIVEN DIRECTORY.
		 * FOR, RECURSIVE SCANNING SET THE $recursive PARAMETER TO TRUE.
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $dir_full_path
		 *
		 * SET TO TRUE FOR RECURSIVE SCANNING.
		 * @param $recursive
		 *
		 * RETURNS AN ARRAY CONTAINING ALL JPG IMAGES
		 * @return array
		 */
		public static function extractJPGFiles($dir_full_path=null, $recursive=false){
			$dir_full_path      = ($dir_full_path)? $dir_full_path : self::$rootDir;
			if(!$dir_full_path){return [];}
			if($recursive){
				return self::deepScan($dir_full_path, "#\.jpg$#");
			}
			return self::extractFilesWithExtension($dir_full_path, 'jpg');
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY,
		 * EXTRACTING FILES WITH .pdf EXTENSION.
		 * THIS IS A SHORTCUT FOR PDF FILES ONLY
		 *
		 * NOTE: THIS METHOD WILL SCAN ONLY THE GIVEN DIRECTORY.
		 * FOR, RECURSIVE SCANNING SET THE $recursive PARAMETER TO TRUE.
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $dir_full_path
		 *
		 * SET TO TRUE FOR RECURSIVE SCANNING.
		 * @param $recursive
		 *
		 * RETURNS AN ARRAY CONTAINING ALL PDF FILES
		 * @return array
		 */
		public static function extractPDFFiles($dir_full_path=null, $recursive=false){
			$dir_full_path      = ($dir_full_path)? $dir_full_path : self::$rootDir;
			if(!$dir_full_path){return [];}
			if($recursive){
				return self::deepScan($dir_full_path, "#\.pdf$#");
			}
			return self::extractFilesWithExtension($dir_full_path, 'pdf');
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY,
		 * EXTRACTING FILES WITH EXTENSION SPECIFIED BY $fileExtension
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $dir_full_path
		 *
		 * THE FILE-EXTENSION NAME (WITHOUT THE DOT [EG: png or jpg]
		 * @param string $fileExtension
		 *
		 * RETURNS AN ARRAY CONTAINING ALL FILES WITH THE EXTENSION SPECIFIED BY $fileExtension
		 * @return array
		 */
		public static function extractFilesWithExtension($dir_full_path=null, $fileExtension="png"){
			$dir_full_path      = ($dir_full_path)? $dir_full_path : self::$rootDir;
			if(!$dir_full_path){return [];}
			$files_in_dir   = scandir($dir_full_path);
			$returnable     = array();
			$reg_fx         = '/\.' . $fileExtension . '/';
			foreach($files_in_dir as $key=>$val){
				$opt_key = $dir_full_path . DIRECTORY_SEPARATOR . $val;
				if(is_file($dir_full_path . "/" . $val) && preg_match($reg_fx,  $val) ){
					$returnable[$val] = $val;
				}
			}
			return $returnable;
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY "RECURSIVELY",
		 * EXTRACTING FILES WITH EXTENSION SPECIFIED BY $fileExtension
		 * THIS IMPLIES THAT EVEN SUB-DIRECTORIES WILL BE SCANNED AS WELL
		 *
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $dir_full_path
		 *
		 * THE FILE-EXTENSION NAME (WITHOUT THE DOT [EG: png or jpg]
		 * @param string $fileExtension
		 *
		 * RETURNS AN ARRAY CONTAINING ALL FILES WITH THE EXTENSION SPECIFIED BY $fileExtension
		 * @return array
		 */
		public static function recursivelyExtractFilesWithExt($dir_full_path, $fileExtension="png"){
			$dir_full_path      = ($dir_full_path)? $dir_full_path : self::$rootDir;
			if(!$dir_full_path){return [];}
			$rxPNG  = "#\." . preg_quote($fileExtension) . "$#";
			return self::deepScan($dir_full_path, $rxPNG);
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY "RECURSIVELY",
		 * EXTRACTING ALL FILES REGARDLESS OF EXTENSION...
		 * THIS IMPLIES THAT EVEN SUB-DIRECTORIES WILL BE SCANNED AS WELL
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $directory
		 *
		 * USED INTERNALLY DURING THE RECURSIVE TRIPS. LEAVE AS NULL
		 * @param $k
		 *
		 * USED INTERNALLY DURING THE RECURSIVE TRIPS. LEAVE AS NULL
		 * @param $key
		 *
		 * RETURNS AN ARRAY CONTAINING ALL FILES WITH THE EXTENSION SPECIFIED BY $fileExtension
		 * @return array
		 */
		public static function recursivelyExtractAllFiles($directory=null, &$k=null, $key=null){
			$directory          = ($directory)? $directory : self::$rootDir;
			$iterator           = new \DirectoryIterator ($directory);
			$firstDir           = basename($directory);
			$dirs               = [];
			$dirs[$firstDir]    = [];

			if(!$key){  $key    = $firstDir;    }

			if(!$k){	$k      = &$dirs[$key]; }

			if($k && $key){
				$k[$key]    = [];
				$k          = &$k[$key];
			}

			foreach($iterator as $info) {
				$fileDirName        = $info->getFilename();
				if ($info->isFile () && !preg_match("#^\..*?#", $fileDirName)) {
					if(self::$returnIndex == "string") {
						$rKey       = self::buildArrayKey($fileDirName);
						if(self::$useFullPath){
							$k[$rKey]   = $directory . DIRECTORY_SEPARATOR . $fileDirName;
						}else{
							$k[$rKey]   = $fileDirName;
						}
					}else{
						if(self::$useFullPath){
							$k[]   = $directory . DIRECTORY_SEPARATOR . $fileDirName;
						}else{
							$k[]   = $fileDirName;
						}
					}
				}elseif ($info->isDir()  && !$info->isDot()) {
					$pathName           = $directory . DIRECTORY_SEPARATOR . $fileDirName;
					$k[$fileDirName]    = $pathName;
					$key                = $fileDirName;
					$it                 = &$k;
					self::recursivelyExtractAllFiles($pathName, $it, $key);
				}
			}
			$dirs   = self::removeEmptyEntries($dirs);
			if(self::$flatReturn){
				return self::getFlatTree($dirs);
			}
			return $dirs;
		}

		/**
		 * THIS STATIC METHOD SCANS A DIRECTORY "RECURSIVELY",
		 * EXTRACTING ALL IMAGE FILES REGARDLESS OF EXTENSION: jpg, png, tiff, gif...
		 * THIS IMPLIES THAT EVEN SUB-DIRECTORIES WILL BE SCANNED AS WELL
		 *
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $dir_full_path
		 *
		 * RETURNS AN ARRAY CONTAINING ALL FILES WITH THE EXTENSION SPECIFIED BY $fileExtension
		 * @return array
		 */
		public static function scan_folder_for_image_files($dir_full_path){
			$rxPNG  = "#(\.png$)|(\.jpg$)|(\.jpeg$)|(\.tiff$)|(\.gif$)#";
			return self::deepScan($dir_full_path, $rxPNG);
		}

		/**
		 * THIS STATIC METHOD CONVERTS THE MULTIDIMENSIONAL ARRAY
		 * TO ONE SINGLE BULK ARRAY AND RETURNS THE RESULT.
		 *
		 *
		 * THE ARRAY TO BE CONVERTED
		 * @param $data
		 *
		 * THIS PARAMETER IS USED INTERNALLY: LEAVE AS IS
		 * @param $bulk
		 *
		 *
		 * RETURNS THE RESULTING MULTIDIMENSIONAL ARRAY AS ONE BULK ARRAY
		 * @return array
		 */
		public static function getFlatTree(array &$items, &$bulk=array()){
			foreach($items as $key=>&$item){
				if(is_array($item)){
					self::getFlatTree($item, $bulk);
				}else{
					if(is_int($key)){
						$bulk[] = $item;
					}else{
						$bulk[$key] = $item;
					}
				}
			}
			return $bulk;
		}


		public static function buildHTMLTree(array $primaryChildren, &$bulk="", &$count=1){
			foreach($primaryChildren as $key=>&$item){
				if(is_array($item)){ //#790107
					$uKey  = ucfirst($key);
					$bulk .= "<ul class='pz-list-group fld-{$uKey} sub-folder level-{$count}'>" . PHP_EOL;  //list-unstyleds list-group
					$bulk .= "<li class='pz-list-item list-for-{$uKey}'>" . PHP_EOL;     //list-group-item
					$bulk .= "<span class='top-level spn-{$uKey} action-box' style='color:#004779;cursor:pointer;'>" . PHP_EOL;
					$bulk .= self::$closedDir . "&nbsp;&nbsp;{$uKey}</span>" . PHP_EOL;
					// CHECK IF FIRST ELEMENT IS ALSO AN ARRAY
					// IF NOT BUILD A LIST FROM ALL ELEMENT IN THE CURRENT ITEM
					$current    = current($item);
					if(!is_array($current)){
						// CURRENT ELEMENT IS NOT AN ARRAY
						// SO WE BUILD THE LISTS...
						$bulk  .= self::buildList($item, "#541A12", $key) . PHP_EOL;
						if(self::containsAnArray($item)){
							//$bulk  .= "</li>" . "</ul>" . PHP_EOL;
						}
					}
					$count++;
					self::buildHTMLTree($item, $bulk, $count);
					$bulk  .= "</li>" . "</ul>" . PHP_EOL;
					//$count  = 0;
				}
			}
			return $bulk;
		}


		public static function getDirectoryPrimaryChildren(){
			$primaryChildren    = current(self::$cluster);
			$return             = [];
			foreach($primaryChildren as $primaryChild=>$primaryData){
				$return[$primaryChild]       = $primaryData;
			}
			return $return;
		}

		private static function buildList(array $items, $color, $keyCssClass='', $levelNum=''){
			$level  = ($levelNum)?" base-level-{$levelNum}":"";
			$count  = 0;
			$cssCls = ($keyCssClass)? $keyCssClass . " " : "";
			$output = "<ul class='pz-list-group {$cssCls}sub-folder'>" . PHP_EOL;

			foreach($items as $item){
				if(!is_array($item)){
					$itemVal = basename($item);
					$dataTip = "Click to view \n{$itemVal}";
					$fileURI = self::path2URI($item);
					$output .= "<li class='pz-list-item list-nr-"  . $count . "{$level}'>" . PHP_EOL;
					$output .= "<span class='pz-file-object file-level level-"  . $count . "' ";
					$output .= "style='color:{$color};cursor:pointer;font-size:10px;' ";
					$output .= "data-path='{$item}' data-uri='{$fileURI}' " . PHP_EOL;
					$output .= "data-tip='{$dataTip}' data-processor='" . AJAX_URI . "' >" . PHP_EOL;
					$output .= self::$fileIcon . "&nbsp;&nbsp;{$itemVal}</span>" . PHP_EOL;
					$output .= "</li>" . PHP_EOL;
					$count++;
				}
			}
			$output.= "</ul>" . PHP_EOL;
			return $output;
		}

		public static function path2URI($path){
			$base   = basename(self::$rootDir);
			return MEDIA_BASE_URI . preg_replace("#" . preg_quote(realpath(self::$rootDir)) . "#", "", realpath($path));
		}

		public static function getDirectoryTree($path=null){
			if($path || self::$rootDir){
				$tree   = $path?$path:self::$rootDir;
				$return =  self::deepScan($tree);
				return $return;
			}
			return null;
		}


		/**
		 * THIS PRIVATE STATIC HELPER METHOD SCANS A DIRECTORY "RECURSIVELY",
		 * BUILDING AN ARRAY TREE OF ALL FILES AND FOLDERS AS IT GOES...
		 * THIS IMPLIES THAT EVEN SUB-DIRECTORIES WILL BE SCANNED AS WELL
		 *
		 *
		 * FULL-PATH TO THE DIRECTORY TO SCAN
		 * @param $directory
		 *
		 *
		 * IF YOU NEED TO SEARCH FOR SPECIFIC PATTERNS IN FILE NAMES,
		 * YOU COULD USE THIS PARAMETER TO SPECIFY THE REGEX PATTERN
		 * @param $regexExt
		 *
		 *
		 * USED INTERNALLY DURING THE RECURSIVE TRIPS. LEAVE AS NULL
		 * @param $k
		 *
		 *
		 * USED INTERNALLY DURING THE RECURSIVE TRIPS. LEAVE AS NULL
		 * @param $key
		 *
		 * RETURN VALUE AS FULL-PATH OR BASE NAME?
		 * @param $useFullPath
		 *
		 *
		 * RETURNS THE RESULTING ARRAY
		 * @return array
		 */
		private static function deepScan($directory, $regexExt=null, &$k=null, $key=null, $useFullPath=false) {
			$directory          = ($directory)? $directory : self::$rootDir;
			$iterator           = new \DirectoryIterator ($directory);
			$firstDir           = basename($directory);
			$objOutput          = new \stdClass();
			$dirs               = [];
			$dirs[$firstDir]    = [];
			$fullPath           = $useFullPath?$useFullPath:self::$useFullPath;

			if(!$key){  $key    = $firstDir;    }

			if(!$k){	$k      = &$dirs[$key]; }

			if($k && $key){
				$k[$key]    = [];
				$k          = &$k[$key];
			}

			foreach($iterator as $info) {
				$fileDirName        = $info->getFilename();
				if ($info->isFile () && !preg_match("#^\..*?#", $fileDirName)) {
					if(self::$returnIndex == "string") {
						$rKey       = self::buildArrayKey($fileDirName);
						if($regexExt) {
							if(preg_match($regexExt, $fileDirName)) {
								if (self::$useFullPath) {
									$k[$rKey] = $directory . DIRECTORY_SEPARATOR . $fileDirName;
								}
								else {
									$k[$rKey] = $fileDirName;
								}
							}
						}else{
							if(self::$useFullPath){
								$k[$rKey]   = $directory . DIRECTORY_SEPARATOR . $fileDirName;
							}else{
								$k[$rKey]   = $fileDirName;
							}
						}
					}else{
						if($regexExt) {
							if(preg_match($regexExt, $fileDirName)) {
								if ($fullPath) {
									$k[] = $directory . DIRECTORY_SEPARATOR . $fileDirName;
								}
								else {
									$k[] = $fileDirName;
								}
							}
						}else{
							if($fullPath){
								$k[]   = $directory . DIRECTORY_SEPARATOR . $fileDirName;
							}else{
								$k[]   = $fileDirName;
							}
						}
					}
				}elseif ($info->isDir()  && !$info->isDot()) {
					$pathName           = $directory . DIRECTORY_SEPARATOR . $fileDirName;
					$k[$fileDirName]    = $pathName;
					$key                = $fileDirName;
					$it                 = &$k;
					self::deepScan($pathName, $regexExt, $it, $key, $useFullPath);
				}
			}

			$dirs   = self::removeEmptyEntries($dirs);

			if(self::$flatReturn){
				$data           = self::getFlatTree($dirs);
			}else{
				$data           = $dirs;
			}
			self::$cluster      = $data;
			$objOutput->tree    = $data;
			return (self::$asObject)  ? $objOutput : $data;
		}

		/**
		 * THIS STATIC METHOD REMOVES/FILTERS EMPTY ENTRIES
		 * FROM THE RESULTING ARRAY TREE.
		 *
		 *
		 * THE ARRAY TO BE FILTERED
		 * @param $data
		 *
		 *
		 * RETURNS THE RESULTING FILTERED ARRAY
		 * @return array
		 */
		private static function removeEmptyEntries(array &$data){
			foreach($data as $key=>&$item){
				if(is_array($item)){
					if(empty($item)) {
						unset($data[$key]);
					}else{
						self::removeEmptyEntries($item);
					}
				}
			}
			foreach($data as $key=>&$item){
				if(is_array($item) && empty($item)) {
					unset($data[$key]);
				}
			}
			return $data;
		}

		/**
		 * THIS PRIVATE STATIC METHOD BUILDS THE KEY OF THE RETURN ARRAY,
		 * BASED ON THE THE NAME OF THE FILE... USING UNDERSCORES(_)
		 * IN THE PLACE OF DOTS AND REMOVING THE FILE EXTENSION
		 *
		 * THE NAME OF THE FILE
		 * @param $val
		 *
		 * RETURNS A STRING REPRESENTATION OF THE POSSIBLE KEY-NAME
		 * @return string
		 */
		private static function buildArrayKey($val){
			$regX_array         = ['#\..{2,5}$#', '#[\.\s]#'];
			$replace_array      = ["", "_"];
			return preg_replace($regX_array, $replace_array, $val);
		}


		private static function containsAnArray(array $item){
			foreach($item as $value){
				if(is_array($value)){
					return true;
				}
			}
			return false;
		}

		/**
		 * SET THE INDEX TYPE FOR OF THE RETURNED ARRAYS:
		 * USE "string" FOR ASSOCIATIVE ARRAYS.
		 * USE "int" FOR NUMERICALLY INDEXED ARRAYS.
		 * @param string $returnIndex
		 */
		public static function setReturnIndex($returnIndex) {
			self::$returnIndex = $returnIndex;
		}

		/**
		 * SET THE FLAG USED TO DETERMINE THE RETURN VALUE.
		 * TRUE     => RETURNS FULL-PATH TO FILE.
		 * FALSE    => RETURNS BASENAME OF FILE.
		 * @param string $useFullPath
		 */
		public static function setUseFullPath($useFullPath) {
			self::$useFullPath = $useFullPath;
		}

		/**
		 * SET THE FLAG USED TO DETERMINE WHETHER OR NOT
		 * TO RETURN THE RESULTING ARRAY AS A MULTIDIMENSIONAL ARRAY.
		 * FALSE    => RETURNS MULTIDIMENSIONAL ARRAY
		 * TRUE     => RETURNS FLAT ARRAY
		 * @param string $flatReturn
		 */
		public static function setFlatReturn($flatReturn) {
			self::$flatReturn = $flatReturn;
		}


		/**
		 * @param mixed $rootDir
		 * @return DirectoryBrowser
		 */
		public static function setRootDir($rootDir) {
			self::$rootDir = $rootDir;
			self::$cluster  = self::deepScan(self::$rootDir);;
			self::$html     = self::buildHTMLTree(self::getDirectoryPrimaryChildren());
		}

		/**
		 * @return string
		 */
		public static function getPool() {
			return self::$pool;
		}

		/**
		 * @return array
		 */
		public static function getHtml() {
			return self::$html;
		}

		/**
		 * @return array
		 */
		public static function getPasses() {
			return self::$passes;
		}

		/**
		 * @param string $directory    => DIRECTORY TO SCAN
		 * @param string $regex        => REGULAR EXPRESSION TO BE USED IN MATCHING FILE-NAMES
		 * @param string $get          => WHAT DO YOU WANT TO GET? 'dir'= DIRECTORIES, 'file'= FILES, 'both'=BOTH FILES+DIRECTORIES
		 * @param bool   $useFullPath  => DO YOU WISH TO RETURN THE FULL PATH TO THE FOLDERS/FILES OR JUST THEIR BASE-NAMES?
		 * @param array  $dirs         => LEAVE AS IS: USED DURING RECURSIVE TRIPS
		 * @return array
		 */
		public static function scanDirRecursive($directory, $regex=null, $get="file", $useFullPath=true,  &$dirs=[], &$files=[]) {
			$iterator               = new \DirectoryIterator ($directory);
			foreach($iterator as $info) {
				$fileDirName        = $info->getFilename();

				if ($info->isFile () && !preg_match("#^\..*?#", $fileDirName)) {
					if($get == 'file' || $get == 'both'){
						if($regex) {
							if(preg_match($regex, $fileDirName)) {
								if ($useFullPath) {
									$files[] = $directory . DIRECTORY_SEPARATOR . $fileDirName;
								}
								else {
									$files[] = $fileDirName;
								}
							}
						}else{
							if($useFullPath){
								$files[]   = $directory . DIRECTORY_SEPARATOR . $fileDirName;
							}else{
								$files[]   = $fileDirName;
							}
						}
					}
				}else if ($info->isDir()  && !$info->isDot()) {
					$fullPathName   = $directory . DIRECTORY_SEPARATOR . $fileDirName;
					if($get == 'dir' || $get == 'both') {
						$dirs[]     = ($useFullPath) ? $fullPathName : $fileDirName;
					}
					self::scanDirRecursive($fullPathName, $regex, $get, $useFullPath, $dirs, $files);
				}
			}

			if($get == 'dir') {
				return $dirs;
			}else if($get == 'file'){
				return $files;
			}
			return ['dirs' => $dirs, 'files' => $files];
		}


	}