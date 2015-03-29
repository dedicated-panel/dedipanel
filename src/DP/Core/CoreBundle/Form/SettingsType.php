<?php

namespace DP\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debug', 'choice', array(
                'choices' => array('Non', 'Oui'),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefault('data_class', 'DP\Core\CoreBundle\Settings\Settings')
        ;
    }

    public function getName()
    {
        return 'core_settings';
    }
}
