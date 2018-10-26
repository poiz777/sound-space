<?php
	/**
	 * Author      : Poiz
	 * Date        : 02/11/16
	 * Time        : 22:04
	 * FileName    : ControllerHelper.php
	 * ProjectName : pz-jobs
	 */
	
	namespace App\CodePool\Traits;

	use Joomla\Session\Session;
	use App\CodePool\DataObjects\User;
	use Doctrine\ORM\EntityManager;
	use App\CodePool\Base\Poiz\HTML\FormBaker;

	trait ControllerHelper {

		protected function getBakedForm(FormBaker $formBaker, string $entity='\App\Entities\JobListing', array $config=array(), array $arrPayload=[] ){
			/**
			 * @var FormBaker $formBaker
			 */
			$opts           = [
				'method'     => 'POST',
				'class'      => 'form-horizontal',
				'enctype'    => 'multipart/form-data',
				'id'         => 'job_form',
				'name'       => 'job_form',
				'action'     => '/admin/save-job',
				'submitText' => 'Go',
				'submitID'   => 'rock_on',
			];
			$formOpts       = array_merge($opts, $config);

			$formBaker->setFullyQualifiedClassName($entity);

			if($arrPayload && !empty($arrPayload)){
				$formBaker-> setArrPostValues($arrPayload);
			}

			$formObj                = $formBaker->buildFormFromClass($formOpts, true);
			$returns                = new \stdClass();
			$returns->formBaker     = $formBaker;
			$returns->formObject    = $formObj;
			return $returns;
		}

		protected function generateRandomHash($length = 6) {
			$characters = '0123456789ABCDEF';
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}
			return $randomString;
		}

		protected function getAdminMenuBar(){
			// icon-add-to-list icon-list icon-suitcase4
			$menu   =<<<MN
	<ul class='list-group list-unstyled pz-menu-group'>
		<li class='list-group-item pz-menu-group-item'><a href='/admin/app-users' data-tip='Users'><span class='pz-icon icon-users'></span></a></li>
		<li class='list-group-item pz-menu-group-item'><a href='/admin/app-user-roles' data-tip='User Roles'><span class='pz-icon icon-list'></span></a></li>
		<li class='list-group-item pz-menu-group-item'><a href='/admin/jobs-categories' data-tip='Categories'><span class='pz-icon icon-tree'></span></a></li>

		<li class='list-group-item pz-menu-group-item'><a href='/admin/new-job' data-tip='New Job'><span class='pz-icon icon-suitcase4'></span></a></li>
		<li class='list-group-item pz-menu-group-item'><a href='/admin/new-app-user'data-tip='New User'><span class='pz-icon icon-user-plus'></span></a></li>
		<li class='list-group-item pz-menu-group-item'><a href='/admin/new-role' data-tip='New User Role'><span class='pz-icon icon-archive'></a></li>
		<li class='list-group-item pz-menu-group-item'><a href='/admin/new-category' data-tip='New Category'><span class='pz-icon icon-add-to-list'></a></li>
	</ul>
MN;

			return $menu;
		}

		public static function getAllCategories(){
			$dBal       = self::$em->getConnection();
			$query      = $dBal->executeQuery('select * from pz_job_categories', []);
			$cats       = $query->fetchAll(\PDO::FETCH_OBJ);
			//$cats       = self::$em->getRepository('\App\Entities\JobCategory')->findBy([], ['id'=>'DESC']);
			$arrCats    = [''=>'Choose a Category'];
			if($cats){
				foreach($cats as $iKey=>$cat){
					$arrCats[$cat->id]  = $cat->title;
				}
			}
			return $arrCats;
		}


		public function redirect($path){
			/**@var RedirectResponse $redirectResponse */
			$redirectResponse   = $this->container->get('Symfony.Component.HttpFoundation.RedirectResponse');
			$redirectResponse->setTargetUrl($path);
			return $redirectResponse->send();
		}

		public static function getJobStatusArray(){
			return [
				'1' => "Active",
				'2' => "Pending",
				'3' => "Inactive",
				'4' => "Archived",
				'5' => "Deactivated",
				'6' => "Deleted",
			];
		}

		public static function getAllAppUserRoles(){
			$dBal       = self::$em->getConnection();
			$query      = $dBal->executeQuery('select * from app_user_role', []);
			$auRoles    = $query->fetchAll(\PDO::FETCH_OBJ);

			$arrAuRoles = [''=>'Choose a User Role'];
			if($auRoles){
				foreach($auRoles as $iKey=>$auRole){
					$arrAuRoles[$auRole->id]  = $auRole->description;
				}
			}
			return $arrAuRoles;
		}

		protected function userIsLoggedIn(Session $session){
			//$session->set('users', [1=>'Django']);
			//var_dump($session);
			var_dump($session->all());
		}

		protected static function parseEnvFile(){
			$envData            =  file_get_contents(__DIR__ . "/../../../.env");
			$envObject          = new \stdClass();
			if($envData){
				$envData        = explode("\n", $envData);
				foreach($envData as $intDex=>$data){
					$tmpData    = explode("=", $data);
					if(count($tmpData)>1){
						$key    = trim($tmpData[0]);
						$envObject->$key = trim($tmpData[1]);
					}
				}
			}
			return $envObject;

		}

		public static function extractExcerpt($string, $length=100) {
			return ($len = strlen($string)>$length ) ? substr($string, 0, $length) . "..." : $string;
		}

		public function password_is_correct($raw_password, $hashed_password){
			return  password_verify($raw_password, $hashed_password);
		}

		public function generate_cryptic_pass($raw_password, $algorithm=PASSWORD_BCRYPT){
			$options    = array(
				"cost"  => 12,
			);
			return  password_hash($raw_password, $algorithm, $options);
		}

		public function authenticateUser(User $userPayload, EntityManager $em, Session $session, $asAdmin=false){
			$userIsAuthentic    = false;
			$dBal               = $em->getConnection();

			$username           = $userPayload->getUsername();
			$password           = $userPayload->getPassword();
			$sql                = " SELECT U.*, R.* FROM " .  TBL_USER . " AS U ";
			$sql               .= " LEFT JOIN " . TBL_ROLE . " AS R ";
			$sql               .= " ON U.role_id=R.id ";
			$sql               .= " WHERE U.username=:UN ";

			$query              = $dBal->executeQuery($sql, ['UN'=>trim($username)]);
			$arrUserObjects     = $query->fetchAll(\PDO::FETCH_OBJ);

			if($arrUserObjects){
				foreach($arrUserObjects as $userObject){
					$cryptPass          = @$userObject->password;
					$userIsAuthentic    = $this->password_is_correct($password, $cryptPass);
					$userPayload->auto_map_properties($userObject);

					if($userIsAuthentic){
						$avatar         = $userObject->avatar;
						$strRole        = $userPayload->getRoleName();
						$welcomeString  = "<div class='col-md-12 welcome-pod no-lr-pad'>";
						# $welcomeString .= "<div class='col-md-10 no-l-pad'>";
						$welcomeString .= "<p class='pz-welcome-p'>Welcome back, ";
						$welcomeString .= "<strong class='welcome-user'>{$userPayload->getFullName()}!</strong><br />";
						$welcomeString .= "You are currently logged in as <em class='pz-user-role'>{$strRole}.</em>";
						$welcomeString .= "</p>";
						$welcomeString .= "</div>";
						# $welcomeString .= "<div class='col-md-2 avatar-box no-r-pad'>";
						# $welcomeString .= "<img src='{$avatar}' class='thumbnail avatar' alt='Avatar: {$userPayload->getFullName()}' />";
						# $welcomeString .= "</div>";
						$welcomeString .= "</div>";
						$session->set('user',               $userPayload,                       'poiz_magic_shop_users');
						$session->set('user_is_in',         intval($userPayload->getId()),      'poiz_magic_shop_users');
						$session->set('user_log_action',    'Logout',                           'poiz_magic_shop_users');
						$session->set('user_log_greeting',  $welcomeString,                     'poiz_magic_shop_users');
						return $userPayload->getFullName();
					}else{
						$session->set('user_is_in',         false,      'poiz_magic_shop_users');
						$session->set('user_log_greeting',  '',         'poiz_magic_shop_users');
						$session->set('user_log_action',    'login',    'poiz_magic_shop_users');
					}
				}
			}
			return $userIsAuthentic;
		}

		public function deAuthenticateUser(Session $session){
			if($session->has('user_is_in', 'poiz_magic_shop_users')){
				$session->clear('user',                             'poiz_magic_shop_users');
				$session->clear('user_is_in',                       'poiz_magic_shop_users');
				$session->clear('user_log_greeting',                'poiz_magic_shop_users');
				$session->set('user_log_action',        'Logout',   'poiz_magic_shop_users');
			}
			return $this->redirect('/admin/login');
		}

		public function checkUserStatus(Session $session){
			$user               = $session->get('user',               null, 'poiz_magic_shop_users');
			$userIsIn           = $session->get('user_is_in',         null, 'poiz_magic_shop_users');
			$userLogAction      = $session->get('user_log_action',    null, 'poiz_magic_shop_users');
			$userLogGreeting    = $session->get('user_log_greeting',  null, 'poiz_magic_shop_users');
			if(!$userIsIn && stristr($_SERVER['REQUEST_URI'], '/admin/')){
				return $this->redirect('/admin/login');
			}else if(!$userIsIn && !stristr($_SERVER['REQUEST_URI'], '/admin/')){
				return $this->redirect('/page-not-found');
			}

			$payload    = array(
				'user'              => $user,
				'userIsIn'          => $userIsIn,
				'menuPod'           => $this->getBackendMenu(),
				'pageTitle'         => 'Backend Dashboard',
				'userLogAction'     => $userLogAction,
				'userLogGreeting'   => $userLogGreeting,
			);
			return $payload;
		}

		public function getBackEndMenu(){
			// icon-add-to-list icon-list icon-suitcase4
			$menu   =<<<MN
			<ul class='list-group list-unstyled pz-menu-group'>
				<li class='list-group-item pz-menu-group-item'><a href='/admin/logout' data-tip='Logout'><span class='pz-icon fa fa-sign-out'></span></a></li>
				<li class='list-group-item pz-menu-group-item'><a href='/admin/categories' data-tip='Product Categories'><span class='pz-icon icon-list'></span></a></li>
				<!-- <li class='list-group-item pz-menu-group-item'><a href='/admin/app-user-roles' data-tip='User Roles'><span class='pz-icon icon-list'></span></a></li> -->
				<li class='list-group-item pz-menu-group-item'><a href='/admin/manage-products' data-tip='Products'><span class='pz-icon icon-tree'></span></a></li>

				<li class='list-group-item pz-menu-group-item'><a href='/admin/new-job' data-tip='New Job'><span class='pz-icon icon-suitcase4'></span></a></li>
				<li class='list-group-item pz-menu-group-item'><a href='/admin/new-app-user'data-tip='New User'><span class='pz-icon icon-user-plus'></span></a></li>
				<li class='list-group-item pz-menu-group-item'><a href='/admin/new-role' data-tip='New User Role'><span class='pz-icon icon-archive'></a></li>
				<li class='list-group-item pz-menu-group-item'><a href='/admin/new-category' data-tip='New Category'><span class='pz-icon icon-add-to-list'></a></li>
				<li class='list-group-item pz-menu-group-item'><a href='/admin/new-category' data-tip='New Category'><span class='pz-icon icon-add-to-list'></a></li>
			</ul>
MN;
			return $menu;
		}

		protected function setPreviousPage(){
			$currentPage        = $_SERVER['REQUEST_URI'];
			$prevPages          = $this->session->get('prev_pages_admin', array(), 'poiz_magic_shop_pages_admin');
			$prevPagesLength    = count($prevPages);
			if($prevPagesLength > 10){
				array_splice($prevPages, 0, 10);
				$prevPages      = array_values($prevPages);
			}
			$prevPages[]        = $currentPage;
			$this->session->set('prev_pages_admin', $prevPages, 'poiz_magic_shop_pages_admin');
		}

		protected function getPreviousPage(){
			$prevPages          = $this->session->get('prev_pages_admin', array(), 'poiz_magic_shop_pages_admin');
			$prevPagesLength    = count($prevPages);
			$prevPage           = end($prevPages);
			if($prevPages && $prevPagesLength>1){
				$index          = $prevPagesLength - 2;
				$prevPage       = $prevPages[$index];
			}
			return $prevPage;
		}

		protected function getPreviousPages(){
			$prevPages          = $this->session->get('prev_pages_admin', array(), 'poiz_magic_shop_pages_admin');
			return $prevPages;
		}

	}