<?php

namespace App\Form;

use App\Entity\Clientes;
use App\Entity\Reservas;
use App\Form\Type\EntitySearchType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservaType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cliente', EntitySearchType::class, [
                'class' => Clientes::class,
                'label' => 'Cliente',
                'choice_label' => fn(Clientes $c) => "{$c->getFirstName()} {$c->getLastName()} (DNI {$c->getDocumentNumber()})",
                'search_url' => $this->urlGenerator->generate('app_catalogo_buscar_clientes'),
                'placeholder' => 'Buscar por nombre o DNI...',
                'create_label' => 'Cargar cliente nuevo',
                'kind' => 'cliente',
            ])
            ->add('reservationDate', DateType::class, [
                'label' => 'Fecha de Reserva',
                'widget' => 'single_text',
                'data' => new \DateTimeImmutable('now')
            ])
            ->add('reservationCurrency', ChoiceType::class, [
                'label' => 'Moneda de la Seña',
                'choices' => [
                    'Pesos (ARS)' => 'ARS',
                    'Dólares (USD)' => 'USD',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('reservationAmount', MoneyType::class, [
                'label' => 'Monto de la Seña',
                'currency' => false, // La moneda se elige arriba
                'attr' => ['placeholder' => 'Ej: 500']
            ])
            ->add('expirationDate', DateType::class, [
                'label' => 'Fecha de Vencimiento',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observaciones',
                'required' => false,
                'attr' => ['rows' => 3]
            ]);

        if ($options['is_edit']) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => ['Activa' => 'Activa', 'Vencida' => 'Vencida', 'Cancelada' => 'Cancelada'],
                'attr' => ['class' => 'form-select']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservas::class,
            'is_edit'    => false,
        ]);
    }
}