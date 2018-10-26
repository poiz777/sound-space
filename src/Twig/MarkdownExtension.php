<?php
	/**
	 * Author      : Poiz
	 * Date        : 17.03.18
	 * Time        : 12:01
	 * FileName    : MarkdownExtension.php
	 * ProjectName : simf.pz
	 */
	
	namespace App\Twig;
	use App\Services\MarkdownTransformer;
	
	class MarkdownExtension extends \Twig_Extension {

		/**
		 * @var MarkdownTransformer
		 */
		private $transformer;

		public function __construct(MarkdownTransformer $transformer){
			$this->transformer  = $transformer;
		}

		public function getFilters() {
			return [
				new \Twig_SimpleFilter('markdownify', [$this, 'parseMarkdown'], ['is_safe'=>['html']])
			];
		}

		public function parseMarkdown($str){
			return $this->transformer->parse($str);
		}

	}