<?php

namespace App\Form;

use App\Entity\Rol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

class RolType extends AbstractType
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $slugger = $this->slugger;

        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'attr'  => ['placeholder' => 'Cajero, Administrativo, Recepcion...'],
            ])
            ->add('descripcion', TextareaType::class, [
                'label'    => 'Descripcion',
                'required' => false,
                'help'     => 'Que hace esta persona en el dia a dia. Se ve al asignar el rol.',
                'attr'     => ['rows' => 2],
            ]);

        // El codigo identifica al rol en el sistema y arma su ROLE_<CODIGO>. Se
        // elige al crear y despues no se toca: cambiarlo dejaria sin acceso a
        // todos los que ya lo tienen.
        if ($options['es_nuevo']) {
            $builder->add('codigo', TextType::class, [
                'label' => 'Codigo',
                'help'  => 'Sin espacios ni acentos. No se puede cambiar despues.',
                'attr'  => ['placeholder' => 'cajero'],
            ]);

            // Se normaliza antes de validar: si no, "Cajero de turno" chocaria
            // contra el formato en vez de convertirse en cajero_de_turno.
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $evento) use ($slugger) {
                $datos = $evento->getData();

                if (!empty($datos['codigo'])) {
                    $datos['codigo'] = strtolower((string) $slugger->slug($datos['codigo'], '_'));
                    $evento->setData($datos);
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rol::class,
            'es_nuevo'   => false,
        ]);
    }
}
