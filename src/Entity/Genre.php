<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GenreRepository")
 */
class Genre
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var ArrayCollection | Song[]
     * @ORM\OneToMany(targetEntity="App\Entity\Song", mappedBy="genre")
     * @ORM\JoinColumn(fieldName="id", referencedColumnName="genre_id")
     */
    private $songs;

    /**
     * Genre constructor.
     */
    public function __construct()
    {
        $this->songs    = new ArrayCollection();
    }


    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSongs()
    {
        return $this->songs;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setSongs($songs)
    {
        $this->songs = $songs;
        return $this;
    }

    public function __toString() {
        return $this->getName();
    }


}
