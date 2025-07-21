<?php

namespace App\Form;

use App\Entity\Marcas;
use App\Entity\Modelos;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('marca', EntityType::class, [
                'class' => Marcas::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una marca',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Modelos::class,
        ]);
    }
}
