<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\Song;
use App\Repository\ArtistRepository;
use App\Repository\GenreRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SongType extends AbstractType
{
    public function buildForm2(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('file')
            ->add('artistID')
            ->add('artist')
        ;
    }
    
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name')
			->add('artist', EntityType::class, [
				'placeholder' => 'Choose an Artist',
				'class' => Artist::class,
				'query_builder' => function(ArtistRepository $repo) {
					return $repo->createQueryBuilder('artist');
				}
			])
			->add('genre', EntityType::class, [
				'placeholder' => 'Select Genre',
				'class' => Genre::class,
				'query_builder' => function(GenreRepository $repo) {
					return $repo->createQueryBuilder('genre');
				}
			])
			->add('file', FileType::class, array('label' => 'File (MP3 File)'))
            #->add('cover_pix', FileType::class, array('label' => 'Cover Art (JPG or PNG)'))
            ->add('cover_pix', FileType::class, [
                #'multiple' => true,
                'label' => 'Cover Art (JPG or PNG)',
                'attr'     => [
                    'accept' => 'image/*',
                    #'multiple' => 'multiple'
                ]
            ])
		;
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
        ]);
    }
}
