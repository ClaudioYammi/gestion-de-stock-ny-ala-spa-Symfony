<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Vente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datevente', DateTimeType::class, [
                'label' => 'Date de vente',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('numfacture', TextType::class, [
                'label' => false,
                'attr' => [
                    'readonly' => true,
                    'hidden' => true // Ajoutez cette option pour masquer le champ
                ],
            ])
            ->add('idClient', EntityType::class, [
                'class' => Client::class,
                'choice_label' =>"nom",
                    'label' => 'Client',
                    'placeholder' => 'Sélectionnez un Client',
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez sélectionner un Client.']),
                    ],
                    'attr' => ['class' => 'form-select'], // Optionnel : affiche un libellé vide pour le choix initial
            ])
            ->add('Tva')
            ->add('Remise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
        ]);
    }
}
