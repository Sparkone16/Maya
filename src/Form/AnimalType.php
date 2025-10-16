<?php

namespace App\Form;

use App\Entity\Animal;
use App\Entity\RaceAnimal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            // ->add('race')
            ->add('dateNaissance')
            ->add('raceAnimal', EntityType::class, [
                'class' => RaceAnimal::class,
                'choice_label' => 'intitule',
                'placeholder' => 'Choisissez une race',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}
