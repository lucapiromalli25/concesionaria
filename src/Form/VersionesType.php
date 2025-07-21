<?php

namespace App\Form;

use App\Entity\Modelos;
use App\Entity\Versiones;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('characteristics', TextareaType::class, [
                'label' => 'Características',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('modelo', EntityType::class, [
                'class' => Modelos::class,
                'choice_label' => function (Modelos $modelo) {
                    return $modelo->getMarca()->getName() . ' - ' . $modelo->getName();
                },
                'group_by' => function(Modelos $modelo) {
                    // Agrupa los modelos por marca
                    return $modelo->getMarca()->getName();
                },
                'placeholder' => 'Seleccione un modelo',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Versiones::class,
        ]);
    }
}