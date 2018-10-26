<?php
	/**
	 * Author      : Poiz
	 * Date        : 10.03.18
	 * Time        : 21:26
	 * FileName    : AppFixtures.php
	 * ProjectName : simf.pz
	 */
	
	namespace App\DataFixtures\ORM;
	


	use App\Entity\Artist;
	use App\Entity\Genus;
	use App\Entity\Song;
	use App\Services\Helpers\FixtureHelper;
	use App\Faker\Provider\NamesProvider;
	use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
	use Doctrine\Common\DataFixtures\FixtureInterface;
	use Doctrine\Common\Persistence\ObjectManager;
	use Doctrine\DBAL\Connection;
	use Doctrine\ORM\EntityManager;
	use Faker\Generator;
	use Nelmio\Alice\Loader\NativeLoader;
	use Faker\Factory as FakerFactory;

	class AppFixtures implements FixtureInterface {
		
		const ARTISTS_MAP   = [
			"Poiz",
			"Bob Marley",
			"2Pac",
			"50Cent",
		];
		
		public function load(ObjectManager $manager){
			$fixHelper  = new FixtureHelper("en");
			$generator  = FakerFactory::create(FakerFactory::DEFAULT_LOCALE);

			$generator->addProvider(new NamesProvider($generator));
			$generator->addProvider($this);
			$loader     = new NativeLoader($generator);
			$objectSet  = $loader->loadFile(__DIR__.'/fixtures.yml')->getObjects();

			if($objectSet) {
				foreach ($objectSet as $entityObj) {
					/**@var Connection $conn*/
					/**@var EntityManager $manager*/
					/**@var Song $entityObj*/
					$manager->persist($entityObj);
					
					/*$artist = $manager->getRepository("App:Artist")->findOneBy(['id'=>$entityObj->getArtistID()]);
					$conn   = $manager->getConnection();
					$artist = new Artist();
					$artist->setName(self::ARTISTS_MAP[mt_rand(0, count(self::ARTISTS_MAP)-1)]);
					$manager->persist($artist);
					$entityObj->setArtist($artist);
					$manager->persist($entityObj);
					dump($entityObj);*/
				}
				$manager->flush();
			}
		}

		public static function genus(){
			$genera = [
				'Octopus',
				'Balaena',
				'Orcinus',
				'Hippocampus',
				'Asterias',
				'Amphiprion',
				'Carcharodon',
				'Aurelia',
				'Cucumeria',
				'Balistoides',
				'Chelonia',
				'Trichechus',
				'Eumetopias',
			];
			$key    = array_rand($genera);
			return $genera[$key];
		}

		public static function foo(){
			return self::genus();
		}

	}