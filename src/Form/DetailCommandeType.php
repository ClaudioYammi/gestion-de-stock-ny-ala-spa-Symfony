<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'id',
            ])
            ->add('idCommande', EntityType::class, [
                'class' => Commande::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetailCommande::class,
        ]);
    }
}
