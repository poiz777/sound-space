<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtistRepository")
 * @ORM\Table(name="artist",uniqueConstraints={@UniqueConstraint(name="unique_fields_idx", columns={"name"})})

 */
class Artist
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
	
	
	/**
     * @Assert\NotBlank()
	 * @ORM\Column(type="string")
	 */
	private $name;
	
	/**
	 * @var ArrayCollection | Song[]
	 * @ORM\OneToMany(targetEntity="App\Entity\Song", mappedBy="artist")
	 * @ORM\JoinColumn(fieldName="id", referencedColumnName="artist_id")
	 */
	private $songs;


    public function getId() {
        return $this->id;
    }
	
	
	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}
	
	public function getSongs() {
		return $this->songs;
	}
	
	/**
	 * @param mixed $name
	 * @return Artist
	 */
	public function setName($name) {
		$this->name = $name;
		
		return $this;
	}
	public function __toString() {
		return ($n=$this->getName())?$n:"Artist";
	}
	
	public function setSongs($songs) {
		$this->songs = $songs;
		
		return $this;
	}
	
}
