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
            ->add('debug', 'dictionary', array(
                'name' => 'yes_no',
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
