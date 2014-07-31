<?php

namespace DP\Core\UserBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoleFilterExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'dp_security_roles';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('roles'));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filteredChoices = array_intersect(
            array_keys($options['choices']), $options['roles']);

        foreach ($builder AS $key => $value) {
            $role = $builder->get($key)->getOption('value');

            if (!in_array($role, $filteredChoices)) {
                $builder->remove($key);
            }
        }
    }
}
