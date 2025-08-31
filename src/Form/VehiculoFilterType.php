<?php

namespace App\Form;

use App\Entity\Marcas;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculoFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marca', EntityType::class, [
                'class' => Marcas::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Todas las Marcas',
            ])
            ->add('state', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'Todos los Estados',
                'choices' => [
                    'En Stock' => 'En Stock',
                    'Reservado' => 'Reservado',
                    'Vendido' => 'Vendido',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // No hay data_class
        ]);
    }
}