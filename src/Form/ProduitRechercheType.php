<?php

namespace App\Form;

use App\Entity\ProduitRecherche;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class ProduitRechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Categorie::class,
                'choice_label' => 'libelle',
                'multiple' => false,
                'expanded' => false,
                'mapped' => false
            ])
            ->add('prixMini', MoneyType::class, [
                'label' => 'Prix minimum',
                'required' => false,
                'invalid_message' => 'Nombre attendu'
            ])
            ->add('prixMaxi', MoneyType::class, [
                'label' => 'Prix maximum',
                'required' => false,
                'invalid_message' => 'Nombre attendu'
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProduitRecherche::class,
        ]);
    }
}
