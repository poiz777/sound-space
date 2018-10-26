<?php

namespace App\Repository;

use App\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Song|null find($id, $lockMode = null, $lockVersion = null)
 * @method Song|null findOneBy(array $criteria, array $orderBy = null)
 * @method Song[]    findAll()
 * @method Song[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoverArtRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Song::class);
    }
    
    public function findAllOrderedByArtist(){
        $qb     = $this->createQueryBuilder('s');
	    return $qb
                    #->select("COUNT(s.id)")->expr()
                    #->where($qb->expr()->gt($qb->expr()->count('s.id'), 0))
                    #->andWhere('s.exampleField = :val')
		            ->leftJoin('s.artist', 'a')
                    #->andWhere('(s.id) > 0')
                    #->setParameter('val', $one)
                    ->orderBy('s.artist', 'DESC')
	                #->setMaxResults(10)
	                ->getQuery()
	                ->getResult();
    	
    }

//    /**
//     * @return Song[] Returns an array of Song objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Song
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
