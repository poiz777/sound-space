<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
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
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $added_on;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modified_on;

    /**
     * @var Song
     * @ORM\ManyToOne(targetEntity="App\Entity\Song", inversedBy="comments")
     * @ORM\JoinColumn(fieldName="id", referencedColumnName="id")
     */
    private $song;

    public function getId() {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAddedOn(): ?\DateTimeInterface
    {
        return $this->added_on;
    }

    public function setAddedOn(\DateTimeInterface $added_on): self
    {
        $this->added_on = $added_on;

        return $this;
    }

    public function getModifiedOn(): ?\DateTimeInterface
    {
        return $this->modified_on;
    }

    public function setModifiedOn(\DateTimeInterface $modified_on): self
    {
        $this->modified_on = $modified_on;

        return $this;
    }
}
