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
use App\Entity\Proveedores;
use App\Form\Type\EntitySearchType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Enum\VehicleStatus;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VehiculosType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('version', EntitySearchType::class, [
                'label' => 'Marca, modelo y versión',
                'class' => Versiones::class,
                'choice_label' => fn(Versiones $v) => trim(sprintf(
                    '%s %s %s',
                    $v->getModelo()->getMarca()->getName(),
                    $v->getModelo()->getName(),
                    $v->getName() ?? ''
                )),
                'search_url' => $this->urlGenerator->generate('app_catalogo_buscar_versiones'),
                'placeholder' => 'Buscar en el catálogo...',
                'create_label' => 'Cargar un modelo que no está en el catálogo',
                'kind' => 'version',
            ])
            ->add('supplier', EntitySearchType::class, [
                'class' => Proveedores::class,
                'label' => 'Comprado a',
                'choice_label' => fn(Proveedores $p) => $p->getName(),
                'search_url' => $this->urlGenerator->generate('app_catalogo_buscar_proveedores'),
                'placeholder' => 'Buscar proveedor...',
                'create_label' => 'Cargar proveedor nuevo',
                'kind' => 'proveedor',
                'required' => false,
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
                'choices' => VehicleStatus::choices(),
                'placeholder' => 'Seleccione un estado',
            ])
            ->add('entry_date', DateType::class, [
                'label' => 'Fecha de Ingreso',
                'widget' => 'single_text',
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
            ])
            ->add('purchaseDocumentFile', VichFileType::class, [
                'label' => 'Boleto de Compra (PDF)',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Eliminar documento actual',
                'download_uri' => false,
            ])
            ->add('purchasePriceUsd', MoneyType::class, [
                'label' => 'Compra en dólares',
                'currency' => 'USD',
                'required' => false,
            ])
            ->add('purchase_price', MoneyType::class, [
                'label' => 'Compra en pesos',
                'currency' => 'ARS',
                'required' => false,
            ])
            ->add('suggestedRetailPriceUsd', MoneyType::class, [
                'label' => 'Venta sugerida en dólares',
                'currency' => 'USD',
                'required' => false,
            ])
            ->add('suggested_retail_price', MoneyType::class, [
                'label' => 'Venta sugerida en pesos',
                'currency' => 'ARS',
                'required' => false,
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehiculos::class,
        ]);
    }
}