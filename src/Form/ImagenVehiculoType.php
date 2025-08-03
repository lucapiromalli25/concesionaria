<?php

namespace App\Form;

use App\Entity\ImagenesVehiculos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType; // Importante usar este tipo

class ImagenVehiculoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Este es el único campo que el usuario verá.
            // Se conecta con la propiedad "virtual" $imageFile de tu entidad.
            ->add('imageFile', VichImageType::class, [
                'label' => 'Archivo de Imagen',
                'required' => false, // No es requerido al editar
                'allow_delete' => true, // Muestra el checkbox "Eliminar"
                'download_uri' => false, // No muestra el enlace de descarga
                'image_uri' => true, // Muestra la imagen actual como vista previa
                'asset_helper' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImagenesVehiculos::class,
        ]);
    }
}