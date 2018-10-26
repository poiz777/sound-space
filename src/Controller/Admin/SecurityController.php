<?php
	/**
	 * Created by PhpStorm.
	 * User: poiz
	 * Date: 19/03/18
	 * Time: 17:33
	 */
	
	namespace App\Controller\Admin;
	
	
	use App\Entity\Role;
	use App\Entity\User;
	use App\Form\LoginForm;
	use App\Form\UserRegistrationForm;
	use App\Traits\ExtraControllerTrait;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
	use Symfony\Bridge\Doctrine\Form\Type\EntityType;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

	/***
	 * Class SecurityController
	 * @package App\Controller\Admin
	 * __Security("is_granted('ROLE_ADMIN')")
	 */
	class SecurityController extends Controller {
		use ExtraControllerTrait;

		
		/**
		 * @Route({
		 *     "en": "/login",
		 *     "de": "/einloggen",
		 *     "fr": "/connexion"
		 * }, name="security_login")
		 * @return Response
		 *
		 */
		public function loginAction(Request $request){
			// IF WE ALREADY HAVE A LOGGED-IN USER, JUST REDIRECT TO THE REFERER OR MAIN-PAGE
			if ($this->getUser()) {
				$referer = $request->headers->get('referer');
				if($referer){
					return $this->redirect($referer);
				}
				return $this->redirectToRoute('rte_homepage');
			}
			$authenticationUtils    = $this->get('security.authentication_utils');
			$error                  = $authenticationUtils->getLastAuthenticationError();
			$lastUsername           = $authenticationUtils->getLastUsername();

			$form                   = $this->createForm(LoginForm::class,[
				'email' => !$lastUsername  ? '' : $lastUsername
			]);
			return $this->render('admin/logon.html.twig', [
				'email'         => !$lastUsername  ? '' : $lastUsername,
				'last_username' => !$lastUsername  ? '' : $lastUsername,
				'error'         => $error,
				'form'          => $form->createView()
			]);
		}
		
		/**
		 * @Route({
		 *     "en": "/register",
		 *     "de": "/anmeldung",
		 *     "fr": "/inscription"
		 * }, name="security_register")
		 * @return Response
		 */
		public function registerAction(Request $request, UserPasswordEncoderInterface $encoder){
			$form               = $this->createForm(UserRegistrationForm::class);
			
			$security           = $this->get('security.authorization_checker');
			// IF THE CURRENT USER IS NOT ADMIN, RESET THE ROLES TO ROLE_USER
			// AND DISABLE THE DROP-DOWN BOX -  SHOWING ONLY THE ROLE-USER
			// OTHERWISE SHOW THE ENTIRE DROP-DOWN OPTIONS (WITH MULTIPLE SELECT OPTIONS)
			// SO THE ADMIN CAN ASSIGN ROLES TO THE SAID USER....
			if(!$security->isGranted('ROLE_ADMIN', $this->getUser())){
				$form->add('roles', EntityType::class, [
					'class'  => Role::class,
					'multiple'=>false,
					'choice_value'=>"default",
					'empty_data' => '1',
					'disabled' => true,
					'data' => 'ROLE_USER',
					'attr' => array('value' => '1'),
				
				]);
			}
			$form->handleRequest($request);

			
			if($form->isSubmitted() && $form->isValid()) {
				/**@var User $user */
				$user       = $form->getData();
				$em         = $this->getDoctrine()->getManager();

				$user->setPassword( $encoder->encodePassword($user, $user->getPlainPassword()));

				$em->persist($user);
				$em->flush();
				
				$this->addFlash('success','Congratulations, ' . $user->getEmail() . ': You have been successfully registered. Please Login to continue.');
				return $this->redirectToRoute('rte_homepage');
				
			}
			return $this->render('user/register.html.twig', [
				'error'     => [],
				'form'      => $form->createView()
			]);
		}

		/**
		 * @Route({
		 *     "en": "/logout",
		 *     "de": "/abmeldung",
		 *     "fr": "/deconnexion"
		 * }, name="security_logout")
		 *
		 * @throws
		 */
		public function logoutAction(Request $request){
			throw new \Exception('This should not be reached...');
		}

		/**
		 * This is the route the login form submits to.
		 *
		 * But, this will never be executed. Symfony will intercept this first
		 * and handle the login automatically. See form_login in app/config/security.yml
		 *
		 * @Route("/login_check", name="security_login_check")
		 */
		public function loginCheckAction()
		{
			throw new \Exception('This should never be reached!');
		}
	}