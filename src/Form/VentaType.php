<?php

namespace App\Form;

use App\Entity\Clientes;
use App\Entity\Ventas;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class VentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cliente', EntityType::class, [
                'class' => Clientes::class,
                // Muestra un texto útil en el desplegable en lugar de solo el ID
                'choice_label' => fn(Clientes $c) => "{$c->getFirstName()} {$c->getLastName()} (DNI: {$c->getDocumentNumber()})",
                'placeholder' => 'Buscar cliente...',
                'attr' => ['class' => 'form-select'] // Para que Select2 lo tome
            ])
            ->add('sale_date', DateType::class, [
                'label' => 'Fecha de Venta',
                'widget' => 'single_text',
                'data' => new \DateTime('now') // Pone la fecha actual por defecto
            ])
            ->add('saleCurrency', ChoiceType::class, [
                'label' => 'Moneda de la Venta',
                'choices' => [
                    'Pesos (ARS)' => 'ARS',
                    'Dólares (USD)' => 'USD',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('final_sale_price', MoneyType::class, [
                'label' => 'Precio Final de Venta',
                'currency' => false, // La moneda se define en el campo de arriba
                'attr' => ['placeholder' => 'Ej: 25000']
            ])
            ->add('payment_method', ChoiceType::class, [
                'label' => 'Método de Pago',
                'choices' => [
                    'Efectivo' => 'Efectivo',
                    'Transferencia Bancaria' => 'Transferencia Bancaria',
                    'Financiado' => 'Financiado',
                    'Otro' => 'Otro',
                ],
                'placeholder' => 'Seleccione un método',
                'attr' => ['class' => 'form-select']
            ])
            ->add('numberOfInstallments', NumberType::class, [
                'label' => 'Cantidad de Cuotas',
                'required' => false,
                'attr' => ['placeholder' => 'Ej: 12'],
                // Le damos un ID al contenedor para encontrarlo con JS
                'row_attr' => ['id' => 'field-installments', 'class' => 'd-none'] 
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observaciones',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('saleDocumentFile', VichFileType::class, [
                'label' => 'Boleto de Venta (PDF)',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Eliminar boleto actual',
                'download_uri' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ventas::class,
        ]);
    }
}