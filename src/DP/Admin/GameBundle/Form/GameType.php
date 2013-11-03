<?php

namespace DP\Admin\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('installName')
            ->add('steamCmd')
            ->add('launchName')
            ->add('bin')
            ->add('appId')
            ->add('appMod')
            ->add('orangebox')
            ->add('source')
            ->add('map')
            ->add('available')
            ->add('binDir')
            ->add('sourceImagesMaps')
            ->add('type')
            ->add('configTemplate')
            ->add('plugins')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\Core\GameBundle\Entity\Game'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dp_core_gamebundle_game';
    }
}
