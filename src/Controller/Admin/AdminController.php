<?php
	/**
	 * Created by PhpStorm.
	 * User: poiz
	 * Date: 19/03/18
	 * Time: 17:33
	 */
	
	namespace App\Controller\Admin;

	use App\Entity\Song;
	use App\Form\ArtistType;
	use App\Form\SongType;
	use App\Traits\ExtraControllerTrait;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\File\UploadedFile;
	
	/***
	 * Class AdminController
	 * @package App\Controller\Admin
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	class AdminController extends Controller {
		use ExtraControllerTrait;

		const ROOT_DIR  = __DIR__ . "/../../..";
		const PUB_DIR   = __DIR__ . "/../../../public";
		
		/**
		 * @Route("/admin/add-song", name="rte_add_song")
		 * @param Request $request
		 * @return Response
		 */
		public function addSong(Request $request){
			$song       = new Song();
			$form       = $this->createForm(SongType::class, $song);
			
			if($this->handleSongFormProcessing($form, $request, $song)){
				return $this->redirectToRoute('rte_songs_list');
			}

			$errors     = $form->getErrors();
			// only handles data on POST
			return $this->render('admin/new-song.html.twig', [
				'errors'     => $errors,
				'form'      => $form->createView()
			]);
			
		}

		/**
		 * @Route({
		 *     "en": "/admin/song/{id}/edit",
		 *     "de": "/admin/lied/{id}/bearbeiten",
		 *     "fr": "/admin/chanson/{id}/editer"
		 * }, name="rte_song_edit")
		 *
		 * @param Request $request
		 * @param $id
		 * @return Response
		 */
		public function editSong(Request $request, $id){
			/**@var Song $song*/
			$error      = "";
			$song       = ($s = $this->getDoctrine()->getManager()->getRepository('App:Song')->find(intval($id))) ? $s : new Song();
			$songFile   = new UploadedFile(self::PUB_DIR . $song->getFile(), $this->getFileMimeType(self::PUB_DIR . $song->getFile()));
			$song->setFile($songFile);
			$form       = $this->createForm(SongType::class, $song);

			if($this->handleSongFormProcessing($form, $request, $song, $id)){
				return $this->redirectToRoute('rte_songs_list');
			}

			return $this->render('admin/edit-song.html.twig', [
				'error'     => $error,
				'errors'    => $error,
				'song'      => $song,
				'form'      => $form->createView()
			]);
		}
		
		/**
		 * @Route({
		 *     "en": "/admin/artist/{id}/edit",
		 *     "de": "/admin/kuenstler/{id}/bearbeiten",
		 *     "fr": "/admin/artiste/{id}/editer"
		 * }, name="rte_artist_edit")
		 *
		 * @param Request $request
		 * @param $id
		 * @return Response
		 */
		public function editArtist(Request $request, $id){
			$em         = $this->getDoctrine()->getManager();
			$artist     = $em->getRepository('App:Artist')->find(intval($id));
			$form       = $this->createForm(ArtistType::class, $artist);

			$form->handleRequest($request);
			if($form->isSubmitted() && $form->isValid()){
				$artist     = $form->getData();
				$em->merge($artist);
				$em->flush();
				$this->addFlash('success', "The Artist, \"" . $artist->getName() . '" was successfully updated.');
				return $this->redirectToRoute('rte_artist_list');
			}
			return $this->render('admin/new-artist.html.twig', [
				'error' => null,
				'form'  => $form->createView(),
			]);

		}

		/**
		 * @Route({
		 *     "en": "/admin/new-artist",
		 *     "de": "/admin/neue-kuenstler",
		 *     "fr": "/admin/nouvelle-artiste"
		 * }, name="rte_new_artist")
		 * @return \Symfony\Component\HttpFoundation\Response
		 */
		public function newArtList(Request $request){
			$em     = $this->getDoctrine()->getManager();
			$form   = $this->createForm(ArtistType::class);
			$error  = null;

			$form->handleRequest($request);
			if($form->isSubmitted() && $form->isValid()){
				$artist     = $form->getData();
				try{
					$em->persist($artist);
					$em->flush();
					$this->addFlash('success', "The Artist, \"" . $artist->getName() . '" was successfully created.');
				}catch (\Exception $e){
					$error = "Could not create the new Artist: «" . $e->getMessage() . "».";
					$this->addFlash('error', $error);
				}
				return $this->redirectToRoute('rte_artist_list');
			}
			return $this->render('admin/new-artist.html.twig', [
				'error' => $error,
				'form'  => $form->createView(),
			]);
		}

		/**
		 * @Route({
		 *     "en": "/admin/song/{id}/delete",
		 *     "de": "/admin/song/{id}/loeschen",
		 *     "fr": "/admin/song/{id}/effacer"
		 * }, name="rte_song_delete")
		 *
		 * @var int $id
		 * @return Response
		 */
		public function deleteSong($id){
			/**
			 * @var Song $song
			 * @var CoverArt $cover
			 */
			$em     = $this->getDoctrine()->getManager();
			$song   = $em->getRepository('App:Song')->find((int)$id);

			if($song) {
				$cover  =  $song->getCoverArt();
				$this->deleteMp3File(['path'=>self::PUB_DIR, 'file'=> $song->getFile()]);
				$this->deleteFileAtPath(self::PUB_DIR . $cover->getImage());
				$this->addFlash('success', "The Song, \"" . $song->getName() . '" was successfully deleted.');
				$em->remove($cover);
				$em->remove($song);
				$em->flush();
			}
			return $this->redirectToRoute('rte_artist_songs', ['id'=>$song->getArtistId()]);
		}

		/**
		 * @Route({
		 *     "en": "/admin/artist/{id}/delete",
		 *     "de": "/admin/kuenstler/{id}/loeschen",
		 *     "fr": "/admin/artiste/{id}/effacer"
		 * }, name="rte_artist_delete")
		 *
		 * @var int $id
		 * @return Response
		 */
		public function deleteArtist($id){
			/**
			 * @var Song $song
			 * @var Artist $artist
			 * @var CoverArt $cover
			 */
			$em     = $this->getDoctrine()->getManager();
			$artist = $em->getRepository('App:Artist')->find((int)$id);

			if($artist) {
				$songs  = $artist->getSongs();
				if($songs){
					// IF WE HAVE SONGS, LOOP THROUGH ALL OF THEM AND
					// DELETE EACH ONE WITH THE ASSOCIATED PHYSICAL FILE
					// AS WELL AS THE COVER ART(S)...
					foreach($songs as $song){
						$cover  =  $song->getCoverArt();
						$this->deleteMp3File(['path'=>self::PUB_DIR, 'file'=> $song->getFile()]);
						$this->deleteFileAtPath(self::PUB_DIR . $cover->getImage());
						$em->remove($cover);
						$em->remove($song);
					}
				}
				$em->remove($artist);
				$em->flush();
			}
			return $this->redirectToRoute('rte_artist_list');
		}
	}