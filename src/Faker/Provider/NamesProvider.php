<?php
	/**
	 * Author      : Poiz
	 * Date        : 15.03.18
	 * Time        : 07:41
	 * FileName    : NamesProvider.php
	 * ProjectName : simf.pz
	 */
	
	namespace App\Faker\Provider;

	use Doctrine\ORM\EntityManager;
	use Faker\Provider\Base as BaseProvider;

	final class NamesProvider extends BaseProvider {
		
		protected $generator;
		/**
		 * @var array List of Genus Names.
		 */
		const ARTISTS_MAP           = [
			"Poiz",
			"Bob Marley",
			"2Pac",
			"50Cent",
			"Jimmy Cliff",
			"Brian Adams",
			"Michael Bolton",
			"Sting",
		];
		
		const SF_MAP                = [
			"/mp3/Love in the Street-Lights.mp3",
			"/mp3/D Way I Am.mp3",
			"/mp3/Thank GOD.mp3",
			"/mp3/Run 2 Me Arms.mp3",
			"/mp3/Rise To Ya Feet.mp3",
			"/mp3/I Let Go.mp3",
			"/mp3/Till We meet Again.mp3",
			"/mp3/When We Are One.mp3",
		];
		
		const ROLE_DESCRIPTION_MAP  = [
			"ROLE_USER"             => "For normal Visitors browsing our Site" ,
			"ROLE_EDITOR"           => "For Editors working on the Project...",
			"ROLE_ADMIN"            => "For Administrators working on the Project...",
			"ROLE_SUPER_USER"       => "For the Super-User working on the Project... conventionally, only 1 Super-User is allowed per Site",
			"ROLE_MODERATOR"        => "For Moderators working on the Project...",
			"ROLE_CONTRIBUTOR"      => "For Contributors working on the Project...",
			"ROLE_MANAGER"          => "For Managers working on the Project...",
			"ROLE_DESIGNER"         => "For Designers working on the Project...",
			"ROLE_CODER"            => "For Programmers and Coders working on the Project...",
			"ROLE_GUEST"            => "For Visitors that have patronized us at the least once... though not registered.... we tracking them with their IPs using an ML Suitcase..." ,
			"ROLE_DIRECTOR"         => "For Site and Project Owners / Leaders + Directors",
		];
		
		public static $R = 0;
		public static $D = 0;
		public static $S = 0;
		public static $T = 0;
		protected static $em;
	
		/**
		 * NamesProvider constructor.
		 * @param string $generator
		 */
		public function __construct($generator) {
			#$this->generator = $generator;
			$this->generator = $this;       //'\App\Services\Providers\NamesProvider';
			#self::$em = $em;
		}




		/**
		 * @return string
		 */
		public static function songArtistID(){
			$id     = self::randomElement(range(1, 4));
			return $id;
		}


		/**
		 * @return string
		 */
		public static function songName(){
			$song       = self::ARTISTS_MAP[self::$S];
			dump($song);
			self::$S++;
			return $song;
		}

		/**
		 * @return string
		 */
		public static function songFileName(){
			return self::randomElement(self::SF_MAP);
		}

		/**
		 * @return string
		 */
		public static function getRoleName(){
			$roles          = array_keys(self::ROLE_DESCRIPTION_MAP);
			$roleName       = $roles[self::$R];
			dump($roleName);
			self::$R++;
			return $roleName;
		}

		/**
		 * @return string
		 */
		public static function getRoleDescription(){
			$descriptions   = array_values(self::ROLE_DESCRIPTION_MAP);
			$roleDesc       = $descriptions[self::$D];
			dump($roleDesc);
			self::$D++;
			return $roleDesc;
		}
	}
