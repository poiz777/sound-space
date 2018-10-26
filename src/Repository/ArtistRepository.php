<?php
	
	namespace App\Repository;
	
	use App\Entity\Artist;
	use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
	use Symfony\Bridge\Doctrine\RegistryInterface;
	
	/**
	 * @method Artist|null find( $id, $lockMode = null, $lockVersion = null )
	 * @method Artist|null findOneBy( array $criteria, array $orderBy = null )
	 * @method Artist[]    findAll()
	 * @method Artist[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
	 */
	class ArtistRepository extends ServiceEntityRepository {
		public function __construct( RegistryInterface $registry ) {
			parent::__construct( $registry, Artist::class );
		}
	}
