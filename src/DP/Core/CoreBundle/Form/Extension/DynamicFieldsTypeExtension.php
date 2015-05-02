<?php

namespace DP\Core\CoreBundle\Form\Extension;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DynamicFieldsTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param EntityManager        $em
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManager $em, FormFactoryInterface $formFactory)
    {
        $this->em          = $em;
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
        if (is_object($object)) {
            $class  = ($object instanceof Proxy) ? get_parent_class($object) : get_class($object);

            return !$this->em->getMetadataFactory()->isTransient($class);
        }

        return false;
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
