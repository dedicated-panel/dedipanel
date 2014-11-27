<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Configurator\Form;

use DP\Core\DistributionBundle\Configurator\Step\DoctrineStep;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Doctrine Form Type.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', 'text', array('label' => 'configurator.db.host'))
            ->add('port', 'text', array('required' => false, 'label' => 'configurator.db.port'))
            ->add('user', 'text', array('label' => 'configurator.db.user'))
            ->add('password', 'repeated', array(
                'required'        => false,
                'type'            => 'password',
                'first_name'      => 'password',
                'second_name'     => 'password_again',
                'first_options'   => array('label' => 'configurator.db.password'), 
                'second_options'  => array('label' => 'configurator.db.password_again'), 
                'invalid_message' => 'The password fields must match.',
            ))
            ->add('name', 'text', array('label' => 'configurator.db.name'))
        ;
    }

    public function getName()
    {
        return 'distributionbundle_doctrine_step';
    }
}
