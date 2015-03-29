<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Controller;

use DP\Core\CoreBundle\Settings\Settings;
use DP\Core\CoreBundle\Settings\SettingsReader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DP\Core\UserBundle\Entity\User;

class ConfigController extends Controller
{
    public function configAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted(User::ROLE_SUPER_ADMIN)) {
            throw new AccessDeniedException();
        }

        /** @var Settings $settings */
        $settings = $this->get('dedipanel.core_settings.settings');
        $usable   = $this->verifyConfigFile();

        $form = $this->createForm('core_settings', $settings, ['disabled' => !$usable]);

        if ($form->handleRequest($request) && $form->isValid() && $usable) {
            $settings = $form->getData();

            $flashMsg  = 'update_succeeded';
            $flashType = 'success';

            if (!$this->get('dedipanel.core_settings.writer')->write($settings)) {
                $flashMsg  = 'update_failed';
                $flashType = 'error';
            }

            $this->addFlash($flashType, sprintf('dedipanel.core.config.%s', $flashMsg));

            return $this->redirectToRoute('dedipanel_core_config');
        }

        $this->verifyUpdate();

        return $this->render('DPCoreBundle:Config:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Verify if the config file (dedipanel.yml) is accessible and writable.
     *
     * @return bool
     */
    private function verifyConfigFile()
    {
        /** @var SettingsReader $reader */
        $error = '';

        if (!$this->get('dedipanel.core_settings.reader')->fileExists()) {
            $error = 'file_not_found';
        } elseif (!$this->get('dedipanel.core_settings.writer')->isWritable()) {
            $error = 'file_not_writable';
        }

        if (!empty($error)) {
            $this->addFlash('error', sprintf('dedipanel.core.config.%s', $error));
        }

        return empty($error);
    }

    /**
     * @param string $type
     * @param string $message
     * @param array $params
     */
    protected function addFlash($type, $message, array $params = array())
    {
        $message = $this->get('translator')->trans($message, $params, 'flashes');

        /** @var FlashBag $flashBag */
        $flashBag = $this->get('session')->getBag('flashes');
        $flashBag->add($type, $message);
    }

    /**
     * Will check if a new update is available, and will display a flash message if it is the case
     */
    private function verifyUpdate()
    {
        if ('prod' != $this->container->getParameter('kernel.environment')) {
            return;
        }

        /** @var \DP\Core\CoreBundle\Service\UpdateWatcherService $watcher */
        $watcher = $this->get('dp_core.update_watcher.service');

        if ($watcher->isUpdateAvailable()) {
            $this->addFlash('warning', 'dedipanel.core.update_available', array(
                '%version%' => 'v' . $watcher->getAvailableVersion(),
                '%url%' => 'http://www.dedicated-panel.net',
            ));
        }
    }
}
