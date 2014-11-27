<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DP\Core\UserBundle\Entity\User;

class ConfigController extends Controller
{
    private $configFile;

    public function __construct()
    {
        $this->configFile = __DIR__ . '/../../../../../app/config/dedipanel.yml';
    }

    public function configAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted(User::ROLE_SUPER_ADMIN)) {
            throw new AccessDeniedException();
        }

        $debugMode = $this->container->getParameter('dedipanel.debug');
        $usable = $this->verifyConfigFile();

        $form = $this->createConfigForm(array(
            'debug_mode' => $debugMode,
        ), !$usable);

        if ($request->isMethod('POST') && $form->submit($request)->isValid()
        && $usable) {
            $data = $form->getData();
            $debugMode = (bool) $data['debug_mode'];

            if ($this->updateConfigFile($debugMode)) {
                $this->addFlash('success', 'dedipanel.core.config.update_succeeded');
            }
            else {
                $this->addFlash('error', 'dedipanel.core.config.update_failed');
            }

            return $this->redirect($this->generateUrl('dedipanel_core_config'));
        }

        if ($this->container->getParameter('kernel.environment') == 'prod') {
            $this->verifyUpdate();
        }

        return $this->render('DPCoreBundle:Config:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    private function createConfigForm(array $default = array(), $disabled = false)
    {
        $form = $this
            ->createFormBuilder($default)
            ->add('debug_mode', 'choice', array(
                'choices' => array('Non', 'Oui'),
                'disabled' => $disabled,
            ))
        ;

        return $form->getForm();
    }

    /**
     * Verify if the config file (dedipanel.yml) is accessible and writable.
     *
     * @return bool
     */
    private function verifyConfigFile()
    {
        if (!file_exists($this->configFile)) {
            $this->addFlash('error', 'dedipanel.core.config.file_not_found');

            return false;
        }

        if (!is_writable($this->configFile)) {
            $this->addFlash('error', 'dedipanel.core.config.file_not_writable');

            return false;
        }

        return true;
    }

    /**
     * @param boolean $debugMode
     */
    private function updateConfigFile($debugMode)
    {
        $config = array(
            'dp_core' => array(
                'version' => $this->container->getParameter('dedipanel.version'),
                'debug' => $debugMode,
            ),
        );

        $yaml = Yaml::dump($config, 2);

        return (bool) file_put_contents($this->configFile, $yaml);
    }

    /**
     * @param string $type
     * @param string $message
     */
    private function addFlash($type, $message, $params = array())
    {
        $message = $this->get('translator')->trans($message, $params, 'flashes');

        /** @var FlashBag $flashBag */
        $flashBag = $this->get('session')->getBag('flashes');
        $flashBag->add($type, $message);
    }

    private function verifyUpdate()
    {
        /** @var DP\Core\CoreBundle\Service\UpdateWatcherService $watcher */
        $watcher = $this->get('dp_core.update_watcher.service');

        if ($watcher->isUpdateAvailable()) {
            $this->addFlash('warning', 'dedipanel.core.update_available', array(
                '%version%' => 'v' . $watcher->getAvailableVersion(),
                '%url%' => 'http://www.dedicated-panel.net',
            ));
        }
    }
}
