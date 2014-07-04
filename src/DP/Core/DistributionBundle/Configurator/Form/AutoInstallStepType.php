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

class AutoInstallStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('createDB', 'checkbox', array(
                'label' => 'configurator.auto_install.install_db',
                'required' => false,
            ))
            ->add('loadFixtures', 'checkbox', array(
                'label' => 'configurator.auto_install.load_fixtures',
                'required' => false, 
            ))
            ->add('installAssets', 'checkbox', array(
                'label' => 'configurator.auto_install.install_assets',
                'required' => false,
            ))
        ;
    }

    public function getName()
    {
        return 'distributionbundle_autoinstall_step';
    }
}
