<?php
	/**
	 * Created by PhpStorm.
	 * User: poiz
	 * Date: 20/03/18
	 * Time: 07:22
	 */
	
	namespace App\Form;
	
	
	use App\Entity\Role;
	use App\Entity\User;
	use Symfony\Bridge\Doctrine\Form\Type\EntityType;
	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\Extension\Core\Type\EmailType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;
	
	class UserRegistrationForm extends AbstractType {
		
		public function buildForm(FormBuilderInterface $builder, array $options)
		{
			$builder
				->add('email', EmailType::class)
				->add('plainPassword', RepeatedType::class, [
					'type'  => PasswordType::class
				])
				->add('roles', EntityType::class, [
					'class'  => Role::class,
					'multiple'=>true,
				])
				
			;
		}
		
		public function configureOptions(OptionsResolver $resolver)
		{
			$resolver->setDefaults([
				'data_class' => User::class,
			]);
		}
		
	}