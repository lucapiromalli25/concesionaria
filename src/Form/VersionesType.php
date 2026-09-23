<?php

namespace App\Form;

use App\Entity\Modelos;
use App\Entity\Versiones;
use App\Form\Type\EntitySearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VersionesType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modelo', EntitySearchType::class, [
                'class' => Modelos::class,
                'label' => 'Marca y modelo',
                'choice_label' => fn(Modelos $modelo) => $modelo->getMarca()->getName() . ' ' . $modelo->getName(),
                'search_url' => $this->urlGenerator->generate('app_catalogo_buscar_modelos'),
                'placeholder' => 'Buscar modelo...',
            ])
            ->add('name', TextType::class, [
                'label' => 'Version',
                'required' => false,
                'help' => 'Como figura en el titulo del vehiculo. Ej: Highline 3.0 V6 4x4.',
            ])
            ->add('characteristics', TextareaType::class, [
                'label' => 'Caracteristicas',
                'required' => false,
                'attr' => ['rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Versiones::class,
        ]);
    }
}
