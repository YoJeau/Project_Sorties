<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\State;
use App\Entity\Trip;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('triName', TextType::class, [
                'label' => 'Nom de la sortie',
                'label_attr' => ['class' => 'w-50']
            ])
            ->add('triStartingDate', DateType::class, [
                'label' => 'Date et heure de la sortie',
                'label_attr' => ['class' => 'w-50'],
                'widget' => 'single_text',
            ])
            ->add('triClosingDate', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'label_attr' => ['class' => 'w-50'],
                'widget' => 'single_text',
            ])
            ->add('triMaxInscriptionNumber', NumberType::class, [
                'label' => 'Nombre de places',
                'label_attr' => ['class' => 'w-50'],
            ])
            ->add('triDuration', NumberType::class, [
                'label' => 'Durée',
                'label_attr' => ['class' => 'w-50'],
            ])
            ->add('triDescription', TextareaType::class, [
                'label' => 'Description et infos',
                'label_attr' => ['class' => 'w-50'],
            ])
            ->add('triSite', EntityType::class, [
                'label' => "Site",
                'label_attr' => ['class' => 'w-50'],
                'class' => Site::class,
                'choice_label' => 'sitName',
            ])
            ->add('triLocation', EntityType::class, [
                'label' => "Lieu",
                'label_attr' => ['class' => 'w-50'],
                'class' => Location::class,
                'choice_label' => 'locName',
            ])
            ->add('triCancellationReason', TextareaType::class, [
                'label' => 'Motif',
                'label_attr' => ['class' => 'w-100'],
            ])
            ->add('triState', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'staLabel',
            ])

            ->add('triOrganiser', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'id',
            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
