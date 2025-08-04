<?php

namespace App\Form;

use App\Entity\Vehiculos;
use App\Entity\Versiones;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class VehiculosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('version', EntityType::class, [
                'label' => 'Versión del Vehículo',
                'class' => Versiones::class,
                'choice_label' => fn(Versiones $v) => "{$v->getModelo()->getMarca()->getName()} - {$v->getModelo()->getName()} - {$v->getName()}",
                'group_by' => fn(Versiones $v) => $v->getModelo()->getMarca()->getName() . ' / ' . $v->getModelo()->getName(),
                'placeholder' => 'Seleccione una versión',
            ])
            ->add('anio', NumberType::class, [
                'label' => 'Año',
            ])
            ->add('chassis_number', TextType::class, [
                'label' => 'Número de Chasis (VIN)',
            ])
            ->add('engine_number', TextType::class, [
                'label' => 'Número de Motor',
            ])
            ->add('plateNumber', TextType::class, [
                'label' => 'Patente',
                'required' => false,
            ])
            ->add('color', TextType::class)
            ->add('kilometers', NumberType::class, [
                'label' => 'Kilometraje',
                'required' => false,
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => [
                    'En Stock' => 'En Stock',
                    'Reservado' => 'Reservado',
                    'Vendido' => 'Vendido',
                    'En Mantenimiento' => 'En Mantenimiento',
                ],
                'placeholder' => 'Seleccione un estado',
            ])
            ->add('entry_date', DateType::class, [
                'label' => 'Fecha de Ingreso',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('purchase_price', MoneyType::class, [
                'label' => 'Precio de Compra',
                'currency' => 'USD',
                'required' => false,
            ])
            ->add('suggested_retail_price', MoneyType::class, [
                'label' => 'Precio Venta (Sugerido)',
                'currency' => 'USD',
                'required' => false,
            ])
            ->add('internal_observations', TextareaType::class, [
                'label' => 'Observaciones Internas',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('imagenesVehiculos', CollectionType::class, [
                'entry_type' => ImagenVehiculoType::class, // Le decimos que cada elemento de la colección es un formulario de imagen
                'entry_options' => ['label' => false],
                'allow_add' => true,      // Permite que se añadan nuevos formularios de imagen con JavaScript
                'allow_delete' => true,   // Permite que se eliminen
                'by_reference' => false,  // Muy importante para que Symfony llame a los métodos add/remove de la entidad Vehiculos
                'label' => 'Imágenes del Vehículo',
                'label_attr' => ['class' => 'fw-bold'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehiculos::class,
        ]);
    }
}