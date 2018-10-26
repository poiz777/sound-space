<?php
	/**
	 * Author      : Poiz
	 * Date        : 10.03.18
	 * Time        : 22:18
	 * FileName    : FixtureHelper.php
	 * ProjectName : simf.pz
	 */
	
	namespace App\Services\Helpers;

	use App\Helpers\Builders\DB;
	
	
	class FixtureHelper {
		/**
		 * @var \PDO
		 */
		private $conn;
		private $lang;
		private $tblWords   = "words";
		private $maxIDMap   = [
			'de'    => 165806,
			'en'    => 194333,
			'fr'    => 208824,
			'words' => 569252,
		];
		private $langMap    = [
			'de'    => 'german',
			'en'    => 'english',
			'fr'    => 'french',
		];



		const PZ_RAND       = 0;
		private $charBank;
		private $charLength;
		private $wordLength;
		private $sentenceLength;


		/**
		 * CharGenerator constructor.
		 * @param string $lang
		 */
		public function __construct($lang="en") {
			$this->lang         = $lang;
			$this->tblWords     = "words";
			$this->conn         = DB::getInstance();
		}





		public function generateSentence($wordsCount){
			$sentence   = [];
			$firstPass  = true;
			while(sizeof($sentence) < $wordsCount){
				if($firstPass){
					$sentence[] = $this->generateWord(true);
					$firstPass  = false;
				}else{
					$sentence[] = $this->generateWord(false);
				}
			}
			return implode(" ", $sentence) . ".";
		}

		public function fetchWordsArray($wordsCount){
			$wordsArray = [];
			$cue        = 0;
			while($cue < $wordsCount){
				$wordsArray[] = $this->generateWord(true);
				$cue++;
			}
			return $wordsArray;

		}

		public function generateWord($ucFirst=false){
			#	$randTID    = mt_rand(1, $this->tblMaxID[$this->lang]);
			$targetTBL  = $this->langMap[$this->lang];
			$query      = "SELECT * FROM `{$targetTBL}` AS TT WHERE TT.id=:TID";
			$statement  = $this->conn->prepare($query);
			$statement->execute(['TID'=>mt_rand(1, $this->maxIDMap[$this->lang])]);
			$word       = ($w = $statement->fetchObject())  ? $w->word : "";
			return  $ucFirst ? ucfirst($word) : $word;
		}





	}