<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parUsername', TextType::class, [
                'label' => 'Pseudo :',
                'trim' => true
            ])
            ->add('parFirstName', TextType::class, [
                'label' => 'Prénom :',
                'trim' => true
            ])
            ->add('parLastName', TextType::class, [
                'label' => 'Nom :',
                'trim' => true
            ])
            ->add('parPhone', TelType::class, [
                'label' => 'Téléphone :',
                'trim' => true
            ])
            ->add('parEmail', EmailType::class, [
                'label' => 'Email :',
                'trim' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                "label" => "Nouveau mot de passe :",
                "mapped" => false,
                "trim" => true,
                "required" => false,
                "constraints" => [
                    new Length([
                        "min" => 12,
                        "minMessage" => 'Le mot de passe doit comporter au minimum {{ limit }} caractères.',
                        "max" => 4096,
                    ])
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                "label" => "Confirmation nouveau mot de passe :",
                "mapped" => false,
                "trim" => true,
                "required" => false
            ])
            ->add('currentPassword', PasswordType::class, [
                "label" => "Mot de passe actuel :",
                "mapped" => false,
                "trim" => true,
                "required" => false
            ])
            ->add('parSite', EntityType::class, [
                'label' => "Ville de rattachement :",
                'class' => Site::class,
                'choice_label' => 'sitName',
            ])
            ->add('parPicture', FileType::class, [
                'label' => 'Ma photo :',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Choisissez une image valide (jpeg/png).',
                    ])
                ]
            ]);

        // adds required to currentPassword if a new password is entered
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (!empty($data['plainPassword'])) {
                $form->add('currentPassword', PasswordType::class, [
                    "label" => "Mot de passe actuel :",
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(['message' => 'Le mot de passe actuel est requis.'])
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
