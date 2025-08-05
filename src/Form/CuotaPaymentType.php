<?php

namespace App\Form;

use App\Entity\Cuotas;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

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