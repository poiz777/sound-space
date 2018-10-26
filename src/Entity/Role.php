<?php
	/**
	 * Created by PhpStorm.
	 * User: poiz
	 * Date: 20/03/18
	 * Time: 05:46
	 */
	
	namespace App\Entity;
	
	
	use Doctrine\ORM\Mapping as ORM;
	
	/**
	 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
	 * @ORM\Table(name="role")
	 */
	class Role {
		
		/**
		 * @ORM\Id
		 * @ORM\GeneratedValue(strategy="AUTO")
		 * @ORM\Column(type="integer")
		 */
		private $id;
		
		/**
		 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
		 */
		private $name;
		
		/**
		 * @ORM\Column(type="text", nullable=true)
		 */
		private $description;
		
		
		
		public function __construct(){
		
		}
		
		/**
		 * @return mixed
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * @return mixed
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * @return mixed
		 */
		public function getDescription() {
			return $this->description;
		}
		
		
		
		
		
		/**
		 * @param mixed $id
		 * @return Role
		 */
		public function setId($id) {
			$this->id = $id;
			
			return $this;
		}
		
		/**
		 * @param mixed $name
		 * @return Role
		 */
		public function setName($name) {
			$this->name = $name;
			
			return $this;
		}
		
		/**
		 * @param mixed $description
		 * @return Role
		 */
		public function setDescription($description) {
			$this->description = $description;
			
			return $this;
		}
		
		
		public function __toString() {
			return $this->getName();
		}
		
		public function getDefault() {
			return "1";
		}
		
		
		
		
		
		
		
	}