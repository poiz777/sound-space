<?php
	/**
	 * Author      : Poiz
	 * Date        : 10.03.18
	 * Time        : 08:36
	 * FileName    : EntityHelper.php
	 * ProjectName : simf.pz
	 */
	
	namespace App\Helpers\Traits;
	
	
	trait EntityHelper {

		public function objectToArrayRecursive($object, &$return_array=null){
			if(!is_object($object) || empty($object)) return null;
			$return_array = (!$return_array) ? [] : $return_array;
			foreach($object as $key=>$val){
				if(is_object($val)){
					$return_array[$key] = [];
					$this->objectToArrayRecursive($val, $return_array[$key]);
				}else{
					$return_array[$key]		= $val;
				}
			}
			return $return_array;
		}
		
		public  function rinseFieldName($fieldName){
			$arrName    = preg_split("#[_\-\s]+#",    $fieldName);
			$arrName    = array_map("ucfirst", array_filter($arrName));
			$strName    = implode("", $arrName);
			return $strName;
		}
		
		public function arrayToObjectRecursive($array, &$objReturn=null){
			if(!is_array($array) || empty($array)) return null;
			$objReturn = (!$objReturn) ? new \stdClass() : $objReturn;
			foreach($array as $key=>$val){
				if(is_array($val)){
					$objReturn->$key = new \stdClass();
					$this->arrayToObjectRecursive($val, $objReturn->$key);
				}else{
					$objReturn->$key		= $val;
				}
			}
			return $objReturn;
		}

		public function recursiveArrayFind($key, $data){
			if(array_key_exists($key, $data)){
				return $data[$key];
			}else{
				if(is_array($data)){
					foreach($data as $k=>$value){
						if($k == $key){
							return $value;
						}else if(is_array($value)){
							return $this->recursiveArrayFind($key, $value);
						}
					}
				}
			}
			return null;
		}

		public function generateRandomHash($length = 6) {
			$characters     = '0123456789ABCDEF';
			$randomString   = '';

			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}

			return $randomString;
		}

		public function getEntityBank() {
			return $this->entityBank;
		}

		public function autoSetClassProps($props){
			if( (is_array($props) || is_object($props)) && $props ){
				foreach($props as $propName=>$propValue){
					$gsName                     = $this->rinseFieldName($propName);
					$setterMethod               = "set" . $gsName;
					$getterMethod               = "get" . $gsName;
					if(property_exists($this, $propName)){
						if(method_exists($this, $setterMethod)){
							$this->$setterMethod($propValue);
						}else{
							$this->$propName			= $propValue;
						}
						$this->entityBank[$propName]	= $propValue;
					}
				}
			}
		}

		public function initializeProperties($object){
			foreach ($object as $prop=>$propVal) {
				if(property_exists($this, $prop)){
					if($prop == "entityBank" || preg_match("#^_#", $prop)){ continue; }
					$this->$prop				= $propVal;
					$this->entityBank[$prop]	= $propVal;
				}
			}
			return $this;
		}

		public function autoSetClassProperties($arrData){
			if(!is_null($arrData)){
				foreach($arrData as $prop=>$val){
					if(property_exists($this, $prop)){
						if($prop == 'id'){

						}elseif($prop == ''){

						}
						$this->$prop				= $val;
						$this->entityBank[$prop]    = $val;
					}
				}
			}
		}

		protected function getClassProperties($fullyQualifiedClassName){
			$arrClassProps                  = [];
			$refClass                       = new \ReflectionClass($fullyQualifiedClassName);

			foreach ($refClass->getProperties() as &$refProperty) {
				$arrClassProps[]        = $refProperty->getName();
			}
			return $arrClassProps;
		}

		public function initializeEntityBank(){
			$refClass					= new \ReflectionClass($this);
			foreach ($refClass->getProperties() as &$refProperty) {
				$key					= $refProperty->getName();
				$this->entityBank[$key]	= $this->$key;
			}
			return $this->entityBank;
		}

		public function __get($name) {
			if(property_exists($this, $name)){
				return $this->$name;
			}else{
				if(property_exists($this, 'entityBank')) {
					if (array_key_exists($name, $this->entityBank)) {
						return $this->entityBank[$name];
					}
				}
			}
			return null;
		}

		public function __set($name, $value) {
			if(property_exists($this, $name)){
				$this->$name     = $value;
				if($name == 'entityBank'){
					if(!empty($value)){
						$this->autoSetClassProps($value);
					}
				}else{
					if(property_exists($this, 'entityBank')) {
						$this->entityBank[$name] = $value;
					}
				}
			}else{
				if(property_exists($this, 'entityBank')) {
					$this->entityBank[$name] = $value;
				}
			}
			return $this;
		}
		
	}