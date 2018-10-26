<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 18/03/18
 * Time: 14:24
 */

namespace App\Controller;


use App\Form\SongSearchType;
use App\Form\SongSortingType;
use App\Traits\ExtraControllerTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JoyController extends Controller {
	use ExtraControllerTrait;

	/**
	 * JoyController constructor.
	 * __Security("is_granted('ROLE_USER')")
	 */
	public function __construct() {
		$container = new ContainerBuilder();
		$container->register( 'greeter', 'Greeter' )
		          ->addArgument( 'Hi' );
	}

	/**
	 * @Route({
	 *     "en": "/",
	 *     "de": "/",
	 *     "fr": "/"
	 * }, name="rte_main")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function mainPage() {
		$em    = $this->getDoctrine()->getManager();
		$songs = $em->getRepository( 'App:Song' )->findAllOrderedByArtist();

		return $this->render( 'pod/welcome.html.twig', [
			'songs' => $songs,
		] );
	}

	/**
	 * @Route({
	 *     "en": "/pod/",
	 *     "de": "/pod/",
	 *     "fr": "/pod/"
	 * }, name="rte_homepage")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function homePage() {
		$em    = $this->getDoctrine()->getManager();
		$songs = $em->getRepository( 'App:Song' )->findAllOrderedByArtist();

		return $this->render( 'pod/welcome.html.twig', [
			'songs' => $songs,
		] );
	}

	/**
	 * @Route({
	 *     "en": "/pod/list",
	 *     "de": "/pod/liste",
	 *     "fr": "/pod/liste"
	 * }, name="rte_songs_list")
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function songList( Request $request ) {
		$em    = $this->getDoctrine()->getManager();
		$query = $em->createQuery( "
                              SELECT s
                              FROM App\Entity\Song s
                              INNER JOIN s.artist a WHERE s.id>?1
                              GROUP BY s.id"
		);
		$query->setParameter( 1, 0 );
		$form       = $this->createForm( SongSortingType::class );
		$searchForm = $this->createForm( SongSearchType::class );

		$form->handleRequest( $request );
		$searchForm->handleRequest( $request );

		$songs      = $em->getRepository( 'App:Song' )->findAllOrderedByArtist();
		if ( $form->isSubmitted() && $form->isValid() ) {
			$data   = $form->getData();
			$songs  = $em->getRepository( 'App:Song' )->findBy( [], [ $data['order_by'] => $data['order_direction'], ] );
		}

		if ( $searchForm->isSubmitted() && $searchForm->isValid() ) {
			/**@var QueryBuilder $qb */
			$data   = $searchForm->getData();
			$qb     = $em->createQueryBuilder();

			$qb->select( 't1' );
			$qb     = $this->buildSongSearchSQL( $data, $qb );
			$songs  = $qb->getQuery()->getResult();
		}

		return $this->render( 'pod/song-list.html.twig', [
			'form'          => $form->createView(),
			'searchForm'    => $searchForm->createView(),
			'songs'         => $songs,
			'isArtistSongs' => false,
		] );
	}

	/**
	 * @Route({
	 *     "en": "/pod/artist-songs-list/{id}",
	 *     "de": "/pod/kuenstlerlieder/{id}",
	 *     "fr": "/pod/chansons-de-l-artiste/{id}"
	 * }, name="rte_artist_songs")
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function artistSongsList( Request $request, $id ) {
		$em         = $this->getDoctrine()->getManager();
		$songs      = $em->getRepository( 'App:Song' )->findBy( [ 'artistID' => $id ], [ 'name' => 'ASC' ] );
		$form       = $this->createForm( SongSortingType::class );
		$searchForm = $this->createForm( SongSearchType::class );
		$ss         = $this->get( "session" );

		$form->handleRequest( $request );
		$searchForm->handleRequest( $request );
		if ( $form->isSubmitted() && $form->isValid() ) {
			$data = $form->getData();
			$qb   = $em->createQueryBuilder();
			if ( $ss->has( 'lastQr' ) ) {
				$qb->select( 't1' );
				$qb = $this->buildConditionalDQL( $qb, $ss->get( 'lastQr' ) );
				$qb->orderBy( 't1.' . $data['order_by'], $data['order_direction'] );
				$songs = $qb->getQuery()->getResult();
			} else {
				$songs = $em->getRepository( 'App:Song' )->findBy( [ 'artistID' => $id ], [ $data['order_by'] => $data['order_direction'], ] );
			}
			$ss->remove( 'lastQr', $data );
		}
		if ( $searchForm->isSubmitted() && $searchForm->isValid() ) {
			/**@var QueryBuilder $qb */
			$data = $searchForm->getData();
			$qb   = $em->createQueryBuilder();

			$qb->select( 't1' );
			$qb = $this->buildConditionalDQL( $qb, $data );
			$ss->set( 'lastQr', $data );
			$songs = $qb->getQuery()->getResult();
		}

		return $this->render( 'pod/song-list.html.twig', [
			'form'          => $form->createView(),
			'searchForm'    => $searchForm->createView(),
			'songs'         => $songs,
			'isArtistSongs' => true,
		] );
	}

	/**
	 * @Route({
	 *     "en": "/pod/artists",
	 *     "de": "/pod/kuenstlern",
	 *     "fr": "/pod/artistes"
	 * }, name="rte_artist_list")
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function artistsList() {
		/**@var QueryBuilder $qb */
		/**@var Connection $conn */
		$em = $this->getDoctrine()->getManager();
		$qb = $em->createQueryBuilder();

		$qb->select( 't1' );
		$artists = $qb->from( 'App:Artist', 't1' )
		              ->leftJoin( 't1.songs', 't2' )
		              ->orderBy( 't2.name', 'DESC' )
		              ->getQuery()
		              ->getResult();

		return $this->render( 'pod/artists-list.html.twig', [
			'artists' => $artists,
		] );
	}

	private function buildConditionalDQL( QueryBuilder $qb, $data ) {
		switch ( $data['search_table'] ) {
			case 'App\Entity\Artist':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't2.name', ':searchTerm' ) )
				   ->setParameter( 'searchTerm', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name, t1.name', 'DESC' );
				break;
			case 'App\Entity\Song':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't1.name', ':searchTerm' ) )
				   ->setParameter( 'searchTerm', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name', 'DESC' );
				break;
			case 'App\Entity\Genre':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't1.name', ':searchTerm' ) )
				   ->setParameter( 'searchTerm', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name', 'DESC' );
				break;
			case 'Global':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't1.name', ':searchTerm1' ) )
				   ->orWhere( $qb->expr()->like( 't2.name', ':searchTerm2' ) )
				   ->setParameter( 'searchTerm1', '%' . $data['search_term'] . '%' )
				   ->setParameter( 'searchTerm2', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name', 'DESC' );
				break;
		}

		return $qb;
	}

	private function buildSongSearchSQL( array $data, QueryBuilder $qb ): QueryBuilder {
		switch ( $data['search_table'] ) {
			case 'App\Entity\Artist':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't2.name', ':searchTerm' ) )
				   ->setParameter( 'searchTerm', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name', 'DESC' );
				break;
			case 'App\Entity\Song':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't1.name', ':searchTerm' ) )
				   ->setParameter( 'searchTerm', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name', 'DESC' );
				break;
			case 'App\Entity\Genre':
				$genre  = $this->getDoctrine()->getManager()->getRepository('App\Entity\Genre')->findOneBy(['name'=>$data['search_term'] ]);
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->eq( 't1.genre', ':genreID' ) )
				   ->leftJoin( 't1.genre', 't2' )
				   ->setParameter( 'genreID', $genre)
				   ->orderBy( 't2.name', 'DESC' );
				break;
			case 'Global':
				$qb->from( 'App:Song', 't1' )
				   ->where( $qb->expr()->like( 't1.name', ':searchTerm1' ) )
				   ->orWhere( $qb->expr()->like( 't2.name', ':searchTerm2' ) )
				   ->setParameter( 'searchTerm1', '%' . $data['search_term'] . '%' )
				   ->setParameter( 'searchTerm2', '%' . $data['search_term'] . '%' )
				   ->leftJoin( 't1.artist', 't2' )
				   ->orderBy( 't2.name', 'DESC' );
				break;
		}

		return $qb;
	}
}