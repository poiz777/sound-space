<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 13.09.18
 * Time: 15:45
 */

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="`users`")
 */
class Users implements UserInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", unique=true)
	 */
	private $username;

	/**
	 * @ORM\Column(type="string", unique=true)
	 */
	private $apiKey;

	public function getUsername()
	{
		return $this->username;
	}

	public function getRoles()
	{
		return array('ROLE_USER');
	}

	public function getPassword()
	{
	}
	public function getSalt()
	{
	}
	public function eraseCredentials()
	{
	}
}
