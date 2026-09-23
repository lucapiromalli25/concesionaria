<?php

namespace App\Form;

use App\Entity\AuditableInterface;
use App\Entity\Rol;
use App\Entity\User;
use App\Repository\RolRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
        $restricciones = [new Length([
            'min' => 6,
            'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres',
            'max' => 4096,
        ])];

        if ($options['es_nuevo']) {
            $restricciones[] = new NotBlank(['message' => 'Ponele una contraseña para que pueda entrar.']);
        }

        $builder
            ->add('complete_name', TextType::class, [
                'label' => 'Nombre completo',
            ])
            ->add('dni', TextType::class, [
                'label' => 'DNI',
                'help' => 'Con este numero entra al sistema.',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            // Los roles salen de la tabla `rol`. El campo no esta mapeado porque
            // la relacion pasa por la pivote UsuarioRol, que el controlador arma.
            ->add('rolesAsignados', EntityType::class, [
                'label'         => 'Roles',
                'class'         => Rol::class,
                'choice_label'  => fn (Rol $rol) => $rol->getNombre(),
                'choice_attr'   => fn (Rol $rol) => ['data-descripcion' => $rol->getDescripcion() ?? ''],
                'query_builder' => fn (RolRepository $repo) => $repo->createQueryBuilder('r')
                    ->andWhere('r.deletedAt IS NULL')
                    ->andWhere('r.status = :activo')->setParameter('activo', AuditableInterface::STATUS_ACTIVO)
                    ->orderBy('r.esSistema', 'DESC')
                    ->addOrderBy('r.nombre', 'ASC'),
                'multiple' => true,
                'expanded' => true,
                'mapped'   => false,
                'required' => false,
                'data'     => $options['data']?->getRolesAsignados() ?? [],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $options['es_nuevo'] ? 'Contraseña' : 'Nueva contraseña',
                'help' => $options['es_nuevo'] ? null : 'Dejala vacia para no cambiarla.',
                'mapped' => false,
                'required' => $options['es_nuevo'],
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => $restricciones,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'es_nuevo'   => false,
        ]);
    }
}
