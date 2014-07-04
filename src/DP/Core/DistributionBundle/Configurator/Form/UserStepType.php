<?php

/**
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Configurator\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array('label' => 'configurator.user_creation.username'))
            ->add('email', 'email', array('label' => 'configurator.user_creation.email'))
            ->add('password', 'repeated', array(
                    'type'          => 'password',
                    'first_name'    => 'password',
                    'second_name'   => 'password_again', 
                    'first_options' => array('label' => 'configurator.user_creation.password'),
                    'second_options' => array('label' => 'configurator.user_creation.password_again'),
            ));
    }
    
    public function getName()
    {
        return 'distributionbundle_user_step';
    }
}
