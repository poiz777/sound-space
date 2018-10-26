<?php

	namespace App\Twig;

	use App\Extensions\PoizTokenParser;
	use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

	class PoizDumper extends \Twig_Extension {


		/**
		 * @var \Twig_Environment
		 */
		protected $twigEnv;

		/**
		 * @var UrlGeneratorInterface
		 */
		protected $urlGenerator;

		public function __construct(\Twig_Environment $twigEnv, UrlGeneratorInterface $urlGenerator){
			$this->twigEnv      = $twigEnv;
			$this->urlGenerator = $urlGenerator;
			$this->twigEnv->addTokenParser(new PoizTokenParser());
		}

		/**
		 * @return array|\Twig_Filter[]
		 */
		public function getFilters() {
			return [
				new \Twig_SimpleFilter('stripATSymbol', [$this, 'stripATSymbolFromEmail'], ['is_safe'=>['html']]),
			];
		}

		/**
		 * @return array|\Twig_Function[]
		 */
		public function getFunctions() {
			return [
				new \Twig_SimpleFunction('poiz_dump', [$this, 'dumpVars'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('p_dump', [$this, 'dumpVars'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('pDump', [$this, 'dumpVars'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('poizDump', [$this, 'dumpVars'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('pzDump', [$this, 'dumpVars'], ['is_safe'=>['html'], 'needs_context' => true, 'needs_environment' => true]),
				new \Twig_SimpleFunction('buildRoute', [$this, 'buildRoute'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('overRideArrayVal', [$this, 'overRideArrayVal'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('exec', [$this, 'execClosure'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('execClosure', [$this, 'execClosure'], ['is_safe'=>['html']]),
				new \Twig_SimpleFunction('php', [$this, 'executeArbitraryPHPCode'], ['is_safe'=>['html']]),
			];
		}

		public function useFilterNr1($str){
			return strtolower($str);
		}

		public function execClosure($closure, ...$data){
			$output = null;
			if($data) {
				try {
					$output = call_user_func_array($closure, $data);
				} catch ( \Exception $e ) {
					$output = $e->getMessage();
				}
			}else{
				$output = call_user_func_array($closure, []);
			}
			if ( is_array( $output ) || is_object( $output ) ) {
				$output = var_export( $output, true );
			}
			return $output;
		}

		public function executeArbitraryPHPCode($phpCodeAsString='var_dump(2)'){
			return eval($phpCodeAsString);
		}


		public function stripATSymbolFromEmail($strEmail){
			return ucfirst( preg_replace("#@.+$#", "", $strEmail));
		}


		public function dumpVars(\Twig_Environment $env, $context, ...$vars){  #public function dumpVars($data){
			if (!$this->twigEnv->isDebug()) {
				return;
			}

			ob_start();

			if (!$vars) {
				$vars = array();
				foreach ($context as $key => $value) {
					if (!$value instanceof \Twig_Template) {
						$vars[$key] = $value;
					}
				}

				dump($vars);
			} else {
				dump(...$vars);
			}
			return ob_get_clean();
		}

		public function buildRoute($routeName, $params=['_locale'=>'en']){
			/** @var UrlGeneratorInterface $urlGenerator */
			return $this->urlGenerator->generate($routeName, $params);
		}

		public function overRideArrayVal($array, $keyValPairs=[]){
			foreach($keyValPairs as $key=>$val){
				if(array_key_exists($key, $array)){
					$array[$key]    = $val;
				}
			}
			return $array;
		}

	}
