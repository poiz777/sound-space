<?php
	/**
	 * Author      : Poiz
	 * Date        : 15.03.18
	 * Time        : 22:55
	 * FileName    : MarkdownTransformer.php
	 * ProjectName : simf.pz
	 */
	
	namespace App\Services;
	
	
	use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
	use  \Doctrine\Common\Cache\Cache;
	use Knp\Bundle\MarkdownBundle\Parser\Preset\Max;

	class MarkdownTransformer {


		private $markdownParser;

		private $cache;

		/**
		 * MarkdownTransformer constructor.
		 * @param $markdownParser
		 */
		/*
		public function __construct(MarkdownParserInterface $markdownParser, Cache $cache) {     //Max
			$this->markdownParser   = $markdownParser;
			$this->cache            = $cache;
		}

		public function parse($str){
			$key        = md5($str);

			if($this->cache->contains($key)){
				return $this->cache->fetch($key);
			}
			sleep(1);
			$str        = $this ->markdownParser
								->transformMarkdown($str);
			$this->cache->save($key, $str);
			return $str;
		}
		*/
	}