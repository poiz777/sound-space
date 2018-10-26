<?php

	namespace App\Form;

	use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;

	class SongSearchType extends AbstractType
	{
		public function buildForm(FormBuilderInterface $builder, array $options)
		{
			$builder
				->add('search_table', ChoiceType::class, [
                    'label' => 'Search In:',
                    'placeholder' => 'Select a Field To Filter by',
                    'choices' => ['Artists'=>'App\Entity\Artist', 'Song Titles'=>'App\Entity\Song', 'Song Genre'=>'App\Entity\Genre', 'Everywhere'=>'Global',],
                ])
				->add('search_term', TextType::class, [
                    'label' => 'Search For:',
                    'attr' => ['placeholder'=>'Michael Bolton',],
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
