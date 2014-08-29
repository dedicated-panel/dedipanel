<?php

namespace DP\Core\CoreBundle\Form;

use Symfony\COmponent\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class FormErrorExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['errors'] = $form->getErrors(true, true);
    }

    public function getExtendedType()
    {
        return 'form';
    }
}