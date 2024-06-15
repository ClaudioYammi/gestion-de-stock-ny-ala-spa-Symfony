<?php

namespace App\Form;

use App\Entity\Achat;
use App\Entity\Fournisseur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
class AchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateachat', DateTimeType::class, [
                'label' => 'Date d\'achat',
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

            ->add('idFournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'nom',
                'label' => 'Fournisseur',
                'placeholder' => 'Sélectionnez un Fournisseur',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un fournisseur.']),
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('Tva')
            ->add('Remise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Achat::class,
        ]);
    }
}
