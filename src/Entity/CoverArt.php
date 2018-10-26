<?php
	
	namespace App\Entity;
	
	use Doctrine\Common\Collections\ArrayCollection;
	use Doctrine\ORM\Mapping as ORM;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\Form\Extension\Core\Type\FileType;
	
	/**
	 * @ORM\Entity(repositoryClass="App\Repository\CoverArtRepository")
	 * @ORM\Table(name="cover_art")
	 */
	class CoverArt {
		/**
		 * @ORM\Id()
		 * @ORM\GeneratedValue()
		 * @ORM\Column(type="integer")
		 */
		private $id;

		/**
		 * @Assert\NotBlank(message="Please, upload the Image File as a JPG or PNG...")
		 * @Assert\File(mimeTypes={ "image/jpeg","image/png" })
		 * @ORM\Column(type="string", options={"default" : "/images/songs_cover/no-cover-art.jpg"})
		 */
        private $image  = "/images/songs_cover/no-cover-art.jpg";

		
		/**
		 * @var ArrayCollection | Song[]
         * @ORM\OneToMany(targetEntity="App\Entity\Song", mappedBy="coverArt")
		 * @ORM\JoinColumn(fieldName="id", referencedColumnName="cover_art_id")
		 */
		private $songs;



        /**
         * CoverArt constructor.
         */
        public function __construct()
        {
            $this->songs    = new ArrayCollection();
        }


        public function getId() {
			return $this->id;
		}

        /**
         * @return mixed
         */
        public function getImage()
        {
            return $this->image;
        }

        /**
         * @return Song[]|ArrayCollection
         */
        public function getSongs()
        {
            return $this->songs;
        }

        /**
         * @param mixed $id
         */
        public function setId($id): void
        {
            $this->id = $id;
        }

        /**
         * @param mixed $image
         */
        public function setImage($image): void
        {
            $this->image = $image;
        }

        /**
         * @param Song[]|ArrayCollection $songs
         */
        public function setSongs($songs): void
        {
            $this->songs = $songs;
        }

		public function __toString() {
			return $this->getImage();
		}
		
		
		
	}
