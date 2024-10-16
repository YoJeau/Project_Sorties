<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locName', TextType::class, [
                'label' => 'Nom du lieu'
            ])
            ->add('locStreet', TextType::class, [
                'label' => 'Rue'
            ])
            ->add('locLatitude', NumberType::class, [
                'label' => 'Latitude',
            ])
            ->add('locLongitude', NumberType::class, [
                'label' => 'Longitude',
            ])
            ->add('locCity', EntityType::class, [
                'label' => "Ville",
                'class' => City::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
