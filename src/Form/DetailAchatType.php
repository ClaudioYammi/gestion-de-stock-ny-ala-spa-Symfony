<?php

namespace App\Form;

use App\Entity\Achat;
use App\Entity\DetailAchat;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailAchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('prixunitaire')
            ->add('reference', EntityType::class, [
                'class' => Produit::class,
'choice_label' => 'id',
            ])
            ->add('idAchat', EntityType::class, [
                'class' => Achat::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetailAchat::class,
        ]);
    }
}
