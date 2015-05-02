<?php

namespace DP\Core\MachineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CoreAssignmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['machine'])
            ->setDefaults([
                'label'        => 'game.core',
                'multiple'     => true,
                'required'     => false,
                'expanded'     => true,
                'choice_list'  => function (Options $options) {
                    $choices = [];

                    if (null !== $machine = $options['machine']) {
                        $choices = array_combine(
                            range(1, $machine->getNbCore()),
                            range(0, $machine->getNbCore()-1)
                        );
                    }

                    return new ChoiceList(array_values($choices), array_keys($choices));
                }
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dedipanel_core_assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }
}
