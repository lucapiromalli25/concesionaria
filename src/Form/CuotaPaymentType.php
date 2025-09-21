<?php

namespace App\Form;

use App\Entity\Cuotas;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CuotaPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paymentDate', DateTimeType::class, [
                'label' => 'Fecha y Hora de Pago',
                'widget' => 'single_text',
                'data' => new \DateTimeImmutable('now'),
                'required' => true,
            ])
            ->add('paidCurrency', ChoiceType::class, [
                'label' => 'Moneda del Pago',
                'choices' => [
                    'Pesos (ARS)' => 'ARS',
                    'Dólares (USD)' => 'USD',
                ],
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ])
            
            // --- CAMPO AÑADIDO ---
            ->add('paidAmount', MoneyType::class, [
                'label' => 'Monto Pagado',
                'currency' => false, // La moneda se elige arriba
                'required' => true,
            ])
            ->add('receiptFile', VichImageType::class, [
                'label' => 'Comprobante de Pago',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cuotas::class,
        ]);
    }
}