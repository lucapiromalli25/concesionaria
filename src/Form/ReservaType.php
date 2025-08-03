<?php

namespace App\Form;

use App\Entity\Clientes;
use App\Entity\Reservas;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cliente', EntityType::class, [
                'class' => Clientes::class,
                'label' => 'Cliente',
                'choice_label' => fn(Clientes $c) => "{$c->getFirstName()} {$c->getLastName()} (DNI: {$c->getDocumentNumber()})",
                'placeholder' => 'Buscar cliente...',
                'attr' => ['class' => 'form-select']
            ])
            ->add('reservationDate', DateType::class, [
                'label' => 'Fecha de Reserva',
                'widget' => 'single_text',
                'data' => new \DateTimeImmutable('now')
            ])
            ->add('reservationAmount', MoneyType::class, [
                'label' => 'Monto de la Seña',
                'currency' => 'USD'
            ])
            ->add('expirationDate', DateType::class, [
                'label' => 'Fecha de Vencimiento',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => [
                    'Activa' => 'Activa',
                    'Vencida' => 'Vencida',
                    'Cancelada' => 'Cancelada',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observaciones',
                'required' => false,
                'attr' => ['rows' => 3]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservas::class,
        ]);
    }
}