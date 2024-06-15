<?php

namespace App\Form;

use App\Entity\Inventaire;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('update_at', null, [
                'widget' => 'single_text',
            ])
            ->add('note')
            ->add('stockinventaire')
            ->add('stockutiliser')
            ->add('reference', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventaire::class,
        ]);
    }
}
