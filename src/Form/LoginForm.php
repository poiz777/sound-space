<?php

	namespace App\Form;

	use Nelmio\Alice\User;
	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;

	class LoginForm extends AbstractType
	{
		public function buildForm(FormBuilderInterface $builder, array $options)
		{
			$builder
				#->add('_username')
				#->add('_password', PasswordType::class);
				->add('email')
				->add('password', PasswordType::class);
		}

		public function getBlockPrefix()
		{
			return '';
		}

		public function configureOptions( OptionsResolver $resolver )
		{
			// parent::configureOptions( $resolver );
			return $resolver->setDefaults([
				// 'data_class'        => User::class,

				// enable/disable CSRF protection for this form
				'csrf_protection'   => true,

				// the name of the hidden HTML field that stores the token
				'csrf_field_name' => '_csrf_token',

				// an arbitrary string used to generate the value of the token
				// using a different string for each form improves its security
				'csrf_token_id'   => 'authenticate',

			]);
		}


	}

	/**
	 *
	dump($this);
	die;
	 */