<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Заглавие',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Съдържание',
                'attr' => ['rows' => 8],
            ])
            ->add('referenceList', TextareaType::class, [
                'label' => 'Референции',
                'required' => false,
                'attr' => ['rows' => 6],
                'help' => 'По една референция на ред.',
            ])
            ->add('coAuthors', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'by_reference' => false,
                'label' => 'Съавтори',
                'disabled' => !$options['can_manage_coauthors'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'can_manage_coauthors' => true,
        ]);

        $resolver->setAllowedTypes('can_manage_coauthors', 'bool');
    }
}
