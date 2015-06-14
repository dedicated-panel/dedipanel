<?php

namespace DP\Core\CoreBundle\Form\Extension;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DynamicFieldsTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param ManagerRegistry      $registry
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(ManagerRegistry $registry, FormFactoryInterface $formFactory)
    {
        $this->registry    = $registry;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        if (!$this->isEntity($data)) {
            return;
        }

        if (null === $data->getId()) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onCreate']);
        } else {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onUpdate']);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onCreate(FormEvent $event)
    {
        $form   = $event->getForm();
        $remove = $form->getConfig()->getOption('remove_on_create');

        foreach ($remove AS $fieldName) {
            $form->remove($fieldName);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onUpdate(FormEvent $event)
    {
        $form    = $event->getForm();
        $remove  = $form->getConfig()->getOption('remove_on_update');
        $disable = $form->getConfig()->getOption('disable_on_update');

        foreach ($remove AS $fieldName) {
            $form->remove($fieldName);
        }

        foreach ($disable AS $fieldName) {
            $field = $form->get($fieldName);

            $type    = $field->getConfig()->getType();
            $data    = $field->getData();
            $options = $field->getConfig()->getOptions();

            $options['auto_initialize'] = false;
            $options['disabled']        = true;

            $newField = $this
                ->formFactory
                ->createNamed($fieldName, $type, $data, $options)
            ;

            $form->add($newField);
        }
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    private function isEntity($object)
    {
        $isEntity = false;

        if (is_object($object)) {
            $class  = ($object instanceof Proxy) ? get_parent_class($object) : get_class($object);

            if (null !== $manager = $this->registry->getManagerForClass($class)) {
                $isEntity = !$this
                    ->registry
                    ->getManager()
                    ->getMetadataFactory()
                    ->isTransient($class);
            }
        }

        return $isEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'remove_on_create'  => [],
                'remove_on_update'  => [],
                'disable_on_update' => [],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
