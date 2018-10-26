<?php
	/**
	 * Created by PhpStorm.
	 * User: poiz
	 * Date: 19/03/18
	 * Time: 17:40
	 */
	
	namespace App\Security;
	
	use App\Form\LoginForm;
	use Doctrine\Common\Persistence\ObjectManager;
	use Doctrine\ORM\EntityManager;
	use Symfony\Component\Form\FormFactoryInterface;
	use Symfony\Component\HttpFoundation\RedirectResponse;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Routing\RouterInterface;
	use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
	use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
	use Symfony\Component\Security\Core\Exception\AuthenticationException;
	use Symfony\Component\Security\Core\Security;
	use Symfony\Component\Security\Core\User\UserInterface;
	use Symfony\Component\Security\Core\User\UserProviderInterface;
	use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
	use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
	use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
	
	class LoginFormAuthenticator extends AbstractGuardAuthenticator  { //  AbstractGuardAuthenticator{    // AbstractFormLoginAuthenticator
		
		private $formFactory;
		/**
		 * @var EntityManager
		 */
		private $em;
		/**
		 * @var RouterInterface
		 */
		private $router;
		/**
		 * @var UserPasswordEncoder
		 */
		private $passwordEncoder;
		
		/**
		 * LoginFormAuthenticator constructor.
		 * @param FormFactoryInterface $formFactory
		 * @param ObjectManager $em
		 * @param RouterInterface $router
		 * @param UserPasswordEncoderInterface $passwordEncoder
		 */
		public function __construct(FormFactoryInterface $formFactory, ObjectManager $em, RouterInterface $router, UserPasswordEncoderInterface $passwordEncoder) {
			$this->formFactory      = $formFactory;
			$this->em               = $em;
			$this->router           = $router;
			$this->passwordEncoder  = $passwordEncoder;
		}
		
		public function getCredentials(Request $request) {
			$isLoginSubmit      = $request->get('_route') == 'security_login' && $request->isMethod('POST');
			if(!$isLoginSubmit){
				return null;
			}
			
			$form               = $this->formFactory->create(LoginForm::class);
			$form->handleRequest($request);
			$data               = $form->getData();

			return $data;
		}
		
		public function getUser($credentials, UserProviderInterface $userProvider) {
			# $username   = $credentials['_username'];
			$username   = $credentials['email'];
			return $this->em->getRepository('AppBundle:User')->findOneBy(['email'=>$username]);
		}
		
		public function checkCredentials($credentials, UserInterface $user) {
			# $password   = $credentials['_password'];
			$password   = $credentials['password'];

			if($this->passwordEncoder->isPasswordValid($user, $password)){
				return true;
			}
			return false;
		}
		
		protected function getLoginUrl() {
			return $this->router->generate('security_login');
		}
		
		protected function getDefaultSuccessRedirectUrl() {
			return $this->router->generate('rte_songs_list');
		}
		
		public function supports(Request $request) {
			// TODO: Implement supports() method.
		}
		
		public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
			return $this->router->generate('rte_songs_list');
		}

		public function start( Request $request, AuthenticationException $authException = null ) {
			# $url = $this->router->generate('rte_homepage');     //false; //$this->getLoginUrl();
			# return new RedirectResponse($url);
			return new RedirectResponse($this->router->generate('rte_homepage'));
		}

		public function onAuthenticationFailure( Request $request, AuthenticationException $exception )
		{
			// TODO: Implement onAuthenticationFailure() method.
		}

		public function supportsRememberMe()
		{
			// TODO: Implement supportsRememberMe() method.
		}


	}