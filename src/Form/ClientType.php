<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom.'])
                ],
                'attr' => [
                    'placeholder' => 'Entrez le nom du Client',
                ],
            ])
            ->add('adresse', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une adresse.'])
                ],
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse du Client',
                ],
            ])
            ->add('prenom', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un prénom.'])
                ],
                'attr' => [
                    'placeholder' => 'Entrez votre prenom',
                ],
            ])
            ->add('email', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une adresse e-mail.']),
                    new Email(['message' => 'Veuillez saisir une adresse e-mail valide.'])
                ],
                'attr' => [
                    'placeholder' => 'Entrez votre email',
                ],
            ])
            ->add('telephone', null, [
                'constraints' => [
                new NotBlank(['message' => 'Veuillez saisir un numéro de téléphone.']),
                new Length([
                        'min' => 10,
                        'max' => 15,
                        'minMessage' => 'Le numéro de téléphone doit contenir au moins {{ limit }} chiffres.',
                        'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} chiffres.'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Entrez votre numéro de téléphone ',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}