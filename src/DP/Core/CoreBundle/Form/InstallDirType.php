<?php

namespace DP\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class InstallDirType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['disabled'] = !empty($form->getParent()->getData()->getId());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'label' => 'game.dir',
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'dedipanel_install_dir';
    }
}
