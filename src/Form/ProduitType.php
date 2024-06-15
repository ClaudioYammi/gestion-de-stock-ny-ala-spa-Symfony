<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Fournisseur;
use App\Entity\Emplacement;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = new \DateTime();
        $minimumDate = $today->modify('+5 days');

        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
                'imagine_pattern' => 'squared_thumbnail_small',
                
            ])

            ->add('designation', null, [
                'label' => 'Désignation',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une désignation.']),
                ],
                'attr' => [
                    'placeholder' => 'Entrez le nom du Produit',
                ],
            ])

            ->add('description', null, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une désignation.']),
                ],
                'attr' => [
                    'placeholder' => 'Entrez le description du produit',
                ],
            ])

            ->add('prixunitaire', NumberType::class, [
                'label' => 'Prix unitaire',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un prix unitaire.']),
                    new Positive(['message' => 'Le prix unitaire doit être un nombre positif.']),
                ],
                'attr' => [
                    'placeholder' => 'ex: 90 000',
                ],
            ])

            ->add('prixunitairevente', NumberType::class, [
                'label' => 'Prix unitaire',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un prix unitaire.']),
                    new Positive(['message' => 'Le prix unitaire doit être un nombre positif.']),
                ],
                'attr' => [
                    'placeholder' => 'ex: 90 000',
                ],
            ])
            
            ->add('dateexp', DateTimeType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => $minimumDate,
                        'message' => 'La date d\'expiration doit être supérieure à 5 jours à partir de la date d\'aujourd\'hui.',
                    ]),
                ],
            ])

            ->add('qttemin', NumberType::class, [
                'label' => 'Quantité minimale',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une quantité minimale.']),
                    new Positive(['message' => 'La quantité minimale doit être un nombre positif.']),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'La quantité minimale ne doit contenir que des chiffres.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Entrez la quantité minimale',
                ],
                'data' => 5,
            ])

            ->add('qttemax', NumberType::class, [
                'label' => 'Quantité maximale',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une quantité maximale.']),
                    new Positive(['message' => 'La quantité maximale doit être un nombre positif.']),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'La quantité maximale ne doit contenir que des chiffres.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Entrez la quantité maximale',
                ],
                'data' => 100,
            ])

            

            ->add('idCategorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionnez une Catégorie',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une catégorie.']),
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('emplacement', EntityType::class, [
                'class' => Emplacement::class,
                'choice_label' => 'nom',
                'label' => 'Emplacement',
                'placeholder' => 'Sélectionnez un Emplacement',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une Emplacement.']),
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('capaciter', NumberType::class, [
                'attr' => [
                    'placeholder' => 'ex: 100',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'La capaciter ne doit contenir que des chiffres.',
                    ]),
                ]
            ])
            
            ->add('unite', ChoiceType::class, [
                'label' => 'Mesure',
                'choices' => [
                    'Kilogrammes' => 'kg',
                    'Grammes' => 'g',
                    'Litres' => 'l',
                    'Millilitres' => 'ml',
                ],
                'attr' => ['class' => 'form-select'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}