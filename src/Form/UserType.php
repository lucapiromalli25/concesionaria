<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('complete_name', TextType::class, [
                'label' => 'Nombre Completo'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('dni', TextType::class, [
                'label' => 'DNI'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => [
                    'Usuario' => 'ROLE_USER',
                    'Vendedor' => 'ROLE_SALESPERSON',
                    'Gerente' => 'ROLE_MANAGER',
                    'Administrador' => 'ROLE_ADMIN',
                ],
                'multiple' => true, // Permite seleccionar varios roles
                'expanded' => true, // Muestra como checkboxes
                'required' => true,
            ])
            // Campo de contraseña "virtual", no está ligado directamente a la entidad
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nueva Contraseña (dejar en blanco para no cambiar)',
                'mapped' => false, // No intenta leer/escribir la propiedad 'plainPassword' en la entidad User
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}