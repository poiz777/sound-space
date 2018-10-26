<?php
	
	namespace App\Traits;
	
	use App\Controller\Admin\AdminController;
	use App\Entity\CoverArt;
    use App\Entity\Song;
    use Doctrine\ORM\EntityManager;
    use Symfony\Component\Config\Definition\Exception\Exception;
    use Symfony\Component\Form\FormInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\File\UploadedFile;
	
	trait ExtraControllerTrait{
		
		protected function handleSongFormProcessing(FormInterface $form, Request $request, Song $song, $id=null){
			/**
			 * @var EntityManager $em
			 * @var UploadedFile $originalSongFile
			 * @var UploadedFile $file
			 * @var UploadedFile $songFile
			 * @var UploadedFile $imgFile
			 * @var Song $data
			 * @var Song $originalSong
			 */
			$em                     = $this->getDoctrine()->getManager();
			$uploadRepo             = AdminController::PUB_DIR;
			$originalSong           = null;
			$originalSongFile       = null;
			$originalPixFile        = null;
			$originalPixFilePath    = null;
			$originalSongFilePath   = null;
			$returnData             = false;

			if($song->getId() && $id){
				// WE KNOW IT'S AN UPDATE SCENARIO,
				// SO WE EXTRACT INFORMATION ABOUT THE EXISTING, UPLOADED FILES
				$originalSong           = $song;
				$originalSongFile       = $song->getFile();
				$originalPixFile        = $song->getCoverArt();
				$originalPixFilePath    = realpath($uploadRepo . $originalPixFile->getImage()); // STRING
				$originalSongFilePath   = realpath($originalSongFile->getPathname());
			}

			// NOW, WE HANDLE THE REQUEST(S)
			try{ $form->handleRequest($request); }catch (\Exception $e){}
			
			// NOW WE GO AHEAD HANDLE THE PROCESSING OF THE FORM & ITS DATA
			if ($form->isSubmitted()) {
				if(!$form->isValid()){ return false; }

				try{
					$data               = $form->getData();
					if($request->files->get('song')['cover_pix']){
						$imgFile        = $request->files->get('song')['cover_pix'];
						$imgFileName    = $this->generateUniqueFileName() . "." . pathinfo($imgFile->getClientOriginalName(), \PATHINFO_EXTENSION);    //""
						$imgFile->move(
							AdminController::PUB_DIR . '/images/cover_arts',
							$imgFileName
						);

						if(!$id){
							$coverArt   = new CoverArt();
							$coverArt->setImage("/images/cover_arts/" . $imgFileName);
							$em->persist($coverArt);
							$em->flush($coverArt);
							$data->setCoverArt($coverArt);
							$data->setCoverArtId($coverArt->getId());
						}else{
							$coverArt   = $originalSong->getCoverArt();
							$coverArt->setImage("/images/cover_arts/" . $imgFileName);
							$em->merge($coverArt);
							$em->flush($coverArt);
							$data->setCoverArt($coverArt);
							$this->deleteFileAtPath($originalPixFilePath);
						}
						$data->setCoverPix($imgFile);
					}

					if($request->files->get('song')['file']){
						$songFile       = $request->files->get('song')['file'];
						$songFileName   = $this->generateUniqueFileName() . "." . pathinfo($songFile->getClientOriginalName(), \PATHINFO_EXTENSION);    //""
						$songFile->move(
							AdminController::PUB_DIR . '/mp3',
							$songFileName
						);
						$data->setFile("/mp3/" . basename($songFileName));

						if($id){
							// WE ARE UPDATING SONG...SO WE DELETE PREVIOUSLY EXISTING SONG-FILE
							$this->deleteFileAtPath($originalSongFilePath);

							$em->merge($data);
							$this->addFlash('success', 'The Song "' . $originalSong->getName() . '" was successfully updated!');
						}else{
							$em->persist($data);
							$this->addFlash('success', 'The Song "' . $data->getName() . '" was successfully created!');
						}
						$em->flush($data);
					}else{
						if($id){
							$data->setFile("/mp3/" . basename($originalSongFilePath));
							$em->merge($data);
							$this->addFlash('success', 'The Song "' . $originalSong->getName() . '" was successfully updated!');
						}else{
							$em->persist($data);
							$this->addFlash('success', 'The Song "' . $data->getName() . '" was successfully created!');
						}
						$em->flush($data);
					}
					$returnData =  TRUE;
				}catch (\Exception $e){}

			}
			
			return $returnData;
		}

		protected function getFileMimeType($fullPath2File) {
			$fileExt = pathinfo( $fullPath2File, PATHINFO_EXTENSION );
			if ( file_exists( $fullPath2File ) ) {
				return mime_content_type( $fullPath2File );
			}

			$rayMimes   = [
				'jpg'   => 'image/jpeg',
				'png'   => 'image/png',
				'gif'   => 'image/gif',
				'bmp'   => 'image/bmp',
				'jpeg'  => 'image/jpeg',
				'webp'  => 'image/webp',

				'mp3'   => 'audio/mpeg',
				'mp4'   => 'audio/*',
				'ogg'   => 'audio/ogg',     // VIDEOS COULD HAVE ogg EXTENSION: THIS IS AN AMBIGUITY...(video/ogg)
				'wav'   => 'audio/wav',
				'aif'   => 'audio/*',
				'webm'  => 'audio/webm',    // VIDEOS COULD HAVE webm EXTENSION: THIS IS AN AMBIGUITY...(video/webm)
				'midi'  => 'audio/midi',

				'js'    => 'application/javascript',
				'css'   => 'text/css',
				'csv'   => 'text/csv',
				'pdf'   => 'application/pdf',
				'txt'   => 'text/plain',
				'xml'   => 'application/xml',
				'xls'   => 'application/vnd-ms-excel',
				'xlsx'  => 'application/vnd-ms-excel',
				'zip'   => 'application/octet-stream',
				'html'  => 'application/xhtml+xml',
				'xhtml' => 'application/xhtml+xml',
			];

			foreach($rayMimes as $ext=>$mime){
				if(strtolower($fileExt) == $ext){
					return $mime;
				}
			}
			return 'application/octet-stream';
		}

		private function generateUniqueFileName(){
			return self::generateRandomHash(32);
		}
		
		private function deleteFileAtPath($filePath){
			if(isset($filePath)){
				if(file_exists($filePath)){
					return unlink($filePath);
				}
			}
			return false;
		}

		protected function deleteMp3File($lusData){
			if(isset($lusData['path'])){
				$dFile  = $lusData['path'] . $lusData['file'];
				if(file_exists($dFile)){
					unlink($dFile);
				}
			}
		}
		
		public static function generateRandomHash($length = 6, $separator="-") {
			$characters     = '0123456789ABCDEF';
			$randomString   = '';
			
			for ($i = 0; $i < $length; $i++) {
				if($separator && $i%4 === 0 && $i != 0 && ($length-1) != $i){
					$randomString .= $separator;
				}
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}
			
			return $randomString;
		}
		
	}