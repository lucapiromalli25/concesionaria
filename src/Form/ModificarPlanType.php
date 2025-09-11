<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ModificarPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numberOfInstallments', NumberType::class, [
                'label' => 'Nueva Cantidad de Cuotas',
                'mapped' => false, // No está directamente en la entidad que pasamos
                'constraints' => [
                    new NotBlank(['message' => 'Este campo es obligatorio.']),
                    new Positive(['message' => 'El número de cuotas debe ser mayor a cero.']),
                ],
                'attr' => ['placeholder' => 'Ej: 12']
            ]);
    }
}