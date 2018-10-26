<?php
	
	namespace App\Repository;
	
	use App\Entity\Song;
	use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
	use Symfony\Bridge\Doctrine\RegistryInterface;
	
	/**
	 * @method Song|null find( $id, $lockMode = null, $lockVersion = null )
	 * @method Song|null findOneBy( array $criteria, array $orderBy = null )
	 * @method Song[]    findAll()
	 * @method Song[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
	 */
	class CoverArtRepository extends ServiceEntityRepository {
		public function __construct( RegistryInterface $registry ) {
			parent::__construct( $registry, Song::class );
		}
		
		public function findAllOrderedByArtist() {
			return $this->createQueryBuilder( 's' )
			            ->leftJoin( 's.artist', 'a' )
			            ->orderBy( 's.artist', 'DESC' )
			            ->getQuery()
			            ->getResult();
		}
	}
