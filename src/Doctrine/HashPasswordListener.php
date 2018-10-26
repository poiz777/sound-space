<?php
	/**
	 * Created by PhpStorm.
	 * User: poiz
	 * Date: 19/03/18
	 * Time: 16:12
	 */
	
	namespace App\Doctrine;
	
	
	use App\Entity\User;
	use Doctrine\Common\EventSubscriber;
	use Doctrine\ORM\Event\LifecycleEventArgs;
	use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
	
	class HashPasswordListener implements EventSubscriber {
		/**
		 * @var UserPasswordEncoderInterface
		 */
		private $passwordEncoder;
		
		
		
		/**
		 * HashPasswordListener constructor.
		 * @var UserPasswordEncoderInterface $passwordEncoder
		 */
		public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
			$this->passwordEncoder = $passwordEncoder;
		}
		
		public function prePersist(LifecycleEventArgs &$args){
			$entity     = $args->getEntity();
			if(!$entity instanceof  User){
				return;
			}
			$this->encodePassword($entity);
		}
		
		public function preUpdate(LifecycleEventArgs &$args){
			$entity     = $args->getEntity();
			if(!$entity instanceof User){
				return;
			}
			$this->encodePassword($entity);
			$em     = $args->getEntityManager();
			$meta   = $em->getClassMetadata(get_class($entity));
			$em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
			
		}
		
		public function getSubscribedEvents() {
			
			return ['prePersist', 'preUpdate'];
		}
		
		/**
		 * @param User $entity
		 */
		private function encodePassword(User $entity) {
			$encoded = $this->passwordEncoder->encodePassword($entity, $entity->getPlainPassword());
			$entity->setPassword($encoded);
		}
		
	}