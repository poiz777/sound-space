<?php
	
	namespace App\Entity;
	
	use Doctrine\Common\Collections\ArrayCollection;
	use Doctrine\ORM\Mapping as ORM;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\Form\Extension\Core\Type\FileType;
	
	/**
	 * @ORM\Entity(repositoryClass="App\Repository\SongRepository")
	 * @ORM\Table(name="song")
	 */
	class Song {
		/**
		 * @ORM\Id()
		 * @ORM\GeneratedValue()
		 * @ORM\Column(type="integer")
		 */
		private $id;
		
		/**
		 * @Assert\NotBlank()
		 * @ORM\Column(type="string", length=255)
		 */
		private $name;
		
		/**
		 * ## Assert\NotBlank(message="Please, upload the Song File as an MP3.")
		 * ## Assert\File(mimeTypes={ "audio/mpeg" })
		 * @ORM\Column(type="string")
		 */
		private $file;

        /**
         * ## Assert\NotBlank(message="Please, upload the COVER PHOTO.")
         * ## Assert\File(mimeTypes={ "image/*" })
         */
        private $cover_pix;

        /**
         * @var CoverArt | string
         * @ORM\ManyToOne(targetEntity="App\Entity\CoverArt", inversedBy="songs")
         * @ORM\JoinColumn(fieldName="cover_art_id", referencedColumnName="id")
         */
        private $coverArt;

		/**
		 * @ORM\Column(type="integer")
		 */
		private $artistID;

		/**
		 * @ORM\Column(type="integer")
		 */
		private $genre_id;

		/**
		 * @ORM\Column(type="integer")
		 */
		private $cover_art_id;
		
		/**
		 * @var Artist
		 * @ORM\ManyToOne(targetEntity="App\Entity\Artist", inversedBy="songs")
		 * @ORM\JoinColumn(fieldName="artist_id", referencedColumnName="id")
		 */
		private $artist;

		/**
		 * @var Genre
		 * @ORM\ManyToOne(targetEntity="App\Entity\Genre", inversedBy="songs")
		 * @ORM\JoinColumn(fieldName="genre_id", referencedColumnName="id")
		 */
		private $genre;
		
		/**
		 * @var ArrayCollection | Comment[]
		 * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="song")
		 * @ORM\JoinColumn(fieldName="id", referencedColumnName="song_id")
		 */
		private $comments;


		
		public function getId() {
			return $this->id;
		}
		
		public function getArtistID() {
			return $this->artistID;
		}

        public function getCoverArtId()
        {
            return $this->cover_art_id;
        }
		
		public function getName(): ?string {
			return $this->name;
		}
		
		public function setName(string $name): self {
			$this->name = $name;
			
			return $this;
		}
		
		public function getFile() {
			return $this->file;
		}

        /**
         * @return CoverArt
         */
        public function getCoverArt():?CoverArt
        {
            return $this->coverArt;
        }
		
		public function getComments() {
			return $this->comments;
		}


        /**
         * @return mixed
         */
        public function getGenreId()
        {
            return $this->genre_id;
        }

        /**
         * @return Genre
         */
        public function getGenre(): ?Genre
        {
            return $this->genre;
        }


		
		public function getArtist(): ?Artist {
			return $this->artist;
		}
		
		
		public function setArtistID(int $artistID):self{
			$this->artistID = $artistID;
			return $this;
		}

        public function setCoverArtId($cover_art_id): void
        {
            $this->cover_art_id = $cover_art_id;
        }
		
		public function setArtist(Artist $artist): self {
			$this->artist = $artist;
			
			return $this;
		}

        public function setId($id)
        {
            $this->id = $id;
            return $this;
        }

        public function getCoverPix(): ?string
        {
            return $this->cover_pix;
        }

        public function setCoverArt(?CoverArt $coverArt): Song
        {
            $this->coverArt = $coverArt;
            return $this;
        }
		
		public function setFile($file) {
			$this->file = $file;
			
			return $this;
		}
		
		public function setComments($comments) {
			$this->comments = $comments;
			
			return $this;
		}
        /**
         * @param mixed $genre_id
         * @return Song
         */
        public function setGenreId($genre_id)
        {
            $this->genre_id = $genre_id;
            return $this;
        }


        /**
         * @param Genre $genre
         * @return Song
         */
        public function setGenre(?Genre $genre): ?Song
        {
            $this->genre = $genre;
            return $this;
        }

        public function setCoverPix(?string $cover_pix): void
        {
            $this->cover_pix = $cover_pix;
        }



		public function __toString() {
			return $this->getName();
		}
		
		
		
	}
