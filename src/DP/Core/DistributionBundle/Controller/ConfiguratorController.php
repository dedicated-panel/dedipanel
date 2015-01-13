<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;

class ConfiguratorController extends Controller
{
    public function indexAction()
    {
        $request = $this->get('request');
        $form    = $this->getProcessTypeForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $route = $this->container->get('router')
                ->generate('dedipanel_installer_check', ['type' => $data['type']]);

            return $this->redirect($route);
        }

        return $this->render('DPDistributionBundle:Configurator:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    private function getProcessTypeForm()
    {
        return $this
            ->createFormBuilder()
            ->add('type', 'choice', array(
                'choices' => array(
                    'install' => 'configurator.install',
                    'update'  => 'configurator.update'
                ),
                'label' => 'configurator.choose_type'
            ))->getForm();
    }

    public function checkAction($type)
    {
        $config = $this->getConfigurator()->getRequirements();

        return $this->render('DPDistributionBundle:Configurator:check.html.twig', array(
            'requirements' => $config['requirements'],
            'hasError'     => $config['error'],
            'stepType'     => $type,
        ));
    }

    /*
     * @param string  $type     Configuration type (install, update)
     * @param integer $step     Step id
     */
    public function stepAction($type, $step)
    {
        $index = $step;
        $step  = $this->getConfigurator()->getInstallStep($index);
        $stepCount = $this->getConfigurator()->getInstallStepCount();

        if ($type == 'update') {
            $step = $this->getConfigurator()->getUpdateStep($index);
            $stepCount = $this->getConfigurator()->getUpdateStepCount();
        }

        $form = $this->createForm($step->getFormType(), $step);
        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $errors = $step->run($form->getData(), $type);

                if (count($errors) == 0) {
                    // Redirection vers la page finale s'il n'y a plus d'étapes
                    if (++$index == $stepCount) {
                        return $this->redirect($this->container->get('router')->generate('dedipanel_installer_final_step', array('type' => $type)));
                    }

                    // Redirection vers la prochaine étape
                    return $this->redirect($this->container->get('router')->generate('dedipanel_installer_step', array('type' => $type, 'step' => $index)));
                }

                foreach ($errors AS $error) {
                    $form->addError(new FormError($error));
                }
            }
        }

        return $this->render($step->getTemplate(), array(
            'form'          => $form->createView(),
            'configType'    => $type,
            'step'          => $index,
            'count'         => $stepCount,
        ));
    }

    public function finalAction()
    {
        return $this->render('DPDistributionBundle:Configurator:final.html.twig');
    }

        public function rewriteFrontScriptAction()
    {
        $rootDir = $this->get('kernel')->getRootDir();
        $filepath = $rootDir . '/../web/.htaccess';
        
        if (is_writable($filepath)) {
            $content = file_get_contents($filepath);
            $content = str_replace('app_installer.php', 'app.php', $content);

            file_put_contents($filepath, $content);
        }

        // Suppression "hard" du cache de prod (si présent)
        // pour s'assurer qu'il contient bien les derniers paramètres
        $this->deleteCache();

        // Supprime le contenu du fichier d'ip whitelist de l'installer
        $this->resetInstallerWhitelist();

        return $this->redirect($this->generateUrl('_welcome'));
    }

    private function deleteCache()
    {
        $cacheDir = $this->container->getParameter('kernel.root_dir') . '/cache/prod';
        if (is_dir($cacheDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files AS $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                }
                else {
                    unlink($fileinfo->getRealPath());
                }
            }

            rmdir($cacheDir);
        }
    }

    private function resetInstallerWhitelist()
    {
        if (is_writable($this->getConfigurator()->getWhitelistFilepath())) {
            return file_put_contents($this->getConfigurator()->getWhitelistFilepath(), "127.0.0.1\n");
        }

        return false;
    }

    /**
     * @return DP\Core\DistributionBundle\Configurator\Configurator
     */
    private function getConfigurator()
    {
        return $this->container->get('dp.webinstaller');
    }
}
