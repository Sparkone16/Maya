<?php

namespace App\Form;

use App\Entity\Saisonnier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaisonnierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('mail')
            ->add('adresse')
            ->add('dateNaissance')
            ->add('dateDebut')
            ->add('salaireHoraire')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Saisonnier::class,
        ]);
    }
}
