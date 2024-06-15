<?php

namespace App\Form;

use App\Entity\DetailVente;
use App\Entity\Produit;
use App\Entity\Vente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('reference', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'id',
            ])
            ->add('idVente', EntityType::class, [
                'class' => Vente::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetailVente::class,
        ]);
    }
}
