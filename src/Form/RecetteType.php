<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Recette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('tempsPreparation')
            ->add('tempsCuisson')
            ->add('ingredient')
            ->add('description')
            ->add('estSucree', CheckboxType::class, [
                'label' => 'Est sucrée',  // <--- C'est ici qu'on définit le nom
                'required' => false,
            ])
            ->add('estTraditionnelle')
            ->add('imageFichier', VichImageType::class, [
                'required' => false,
            ])
            ->add('produits', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'libelle',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
