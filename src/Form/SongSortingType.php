<?php

	namespace App\Form;

	use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;

	class SongSortingType extends AbstractType
	{
		public function buildForm(FormBuilderInterface $builder, array $options)
		{
			$builder
				->add('order_by', ChoiceType::class, [
                    'label' => 'Sort By:',
                    'placeholder' => 'Select a Field To Filter by',
                    'choices' => ['Artist'=>'artistID', 'Song Title'=>'name',],
                ])
				->add('order_direction', ChoiceType::class, [
                    'label' => 'Sort Direction',
                    'placeholder' => 'Choose a Sort Direction',
                    'choices' => ['ASC'=>'ASC', 'DESC'=>'DESC',],
                ])

            ;
		}

		/*
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Sorting::class,
            ]);
        }
        */

	}
