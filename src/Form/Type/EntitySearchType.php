<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Selector de entidad con busqueda contra el servidor: guarda solo el id en un
 * input hidden y delega el buscador al controller Stimulus "combobox".
 * Evita volcar cientos de <option> en el HTML.
 */
class EntitySearchType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $class = $options['class'];

        $builder->addModelTransformer(new CallbackTransformer(
            fn(?object $entity) => $entity?->getId(),
            fn(?string $id) => $id !== null && $id !== '' ? $this->em->find($class, $id) : null,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $entity = $form->getData();

        $view->vars['selected_label'] = $entity ? ($options['choice_label'])($entity) : null;
        $view->vars['search_url']     = $options['search_url'];
        $view->vars['placeholder']    = $options['placeholder'];
        $view->vars['create_label']   = $options['create_label'];
        $view->vars['kind']           = $options['kind'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['class', 'choice_label', 'search_url'])
            ->setDefaults([
                'placeholder'  => 'Buscar...',
                'create_label' => null,
                'kind'         => null,
                'compound'     => false,
                'invalid_message' => 'La opcion seleccionada ya no existe.',
            ])
            ->setAllowedTypes('choice_label', 'callable');
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'entity_search';
    }
}
