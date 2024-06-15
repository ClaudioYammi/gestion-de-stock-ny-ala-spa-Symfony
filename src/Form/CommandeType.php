<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datecommande', null, [
                'widget' => 'single_text',
            ])
            ->add('etatcommande')
            ->add('idVille', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => function ($ville) {
                    return $ville->getNom();
                },
                'placeholder' => 'Sélectionnez une Ville', // Optionnel : affiche un libellé vide pour le choix initial
            ])
            ->add('idClient', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function ($client) {
                    return $client->getNom();
                },
                'placeholder' => 'Sélectionnez un Client', // Optionnel : affiche un libellé vide pour le choix initial
            ])
            ->add('Tva')
            ->add('Remise')
            ->add('numfacture', TextType::class, [
                'label' => false,
                'attr' => [
                    'readonly' => true,
                    'hidden' => true // Ajoutez cette option pour masquer le champ
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
