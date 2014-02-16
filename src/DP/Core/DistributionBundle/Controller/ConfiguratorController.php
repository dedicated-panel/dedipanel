<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace DP\Core\DistributionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;

class ConfiguratorController extends Controller
{
    public function indexAction()
    {
        $request = $this->get('request');
        $form = $this->createFormBuilder()
             ->add('type', 'choice', array(
                'choices' => array(
                    'i' => 'configurator.install',
                    // 'u' => 'configurator.update'
                ),
                'label' => 'configurator.chooseType')
            )->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // Installation
                if ($data['type'] == 'i') {
                    $url = $this->container->get('router')->generate('installer_check');
                }
                // Mise à jour
                elseif ($data['type'] == 'u') {
                    $url = $this->container->get('router')->generate('installer_step', array('index' => 0, 'type' => 'update'));
                }

                return $this->redirect($url);
            }
        }

        return $this->render('DPDistributionBundle:Configurator:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function checkAction()
    {
        $configurator = $this->container->get('dp.webinstaller');
        $config = $configurator->getRequirements();

        return $this->render('DPDistributionBundle:Configurator:check.html.twig', array(
            'requirements' => $config['requirements'],
            'hasError' => $config['error']
        ));
    }

    /*
     * @param $type     Configuration type (install, update)
     * @param $index    Step index
     */
    public function stepAction($type, $index)
    {
        $configurator = $this->container->get('dp.webinstaller');

        if ($type == 'install') {
            $step = $configurator->getInstallStep($index);
            $stepCount = $configurator->getInstallStepCount();
        }
        elseif ($type == 'update') {
            $step = $configurator->getUpdateStep($index);
            $stepCount = $configurator->getUpdateStepCount();
        }

        $form = $this->container->get('form.factory')->create($step->getFormType(), $step);
        $request = $this->container->get('request');

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $errors = $step->run($form->getData(), $type);

                if (count($errors) == 0) {
                    ++$index;

                    // Redirection vers la page finale s'il n'y a plus d'étapes
                    if ($index == $stepCount) {
                        return $this->redirect($this->container->get('router')->generate('installer_final', array('type' => $type)));
                    }

                    // Redirection vers la prochaine étape
                    return $this->redirect($this->container->get('router')->generate('installer_step', array('type' => $type, 'index' => $index)));
                }
                else {
                    foreach ($errors AS $error) {
                        $form->addError(new FormError($error));
                    }
                }
            }
        }

        return $this->render($step->getTemplate(), array(
            'form'          => $form->createView(),
            'configType'    => $type,
            'index'         => $index,
            'count'         => $stepCount,
        ));
    }

    public function finalAction($type)
    {
        return $this->render('DPDistributionBundle:Configurator:final.html.twig');
    }

    public function rewriteFrontScriptAction()
    {
        $rootDir = $this->get('kernel')->getRootDir();
        $filepath = $rootDir . '/../web/.htaccess';
        $configurator = $this->get('dp.webinstaller');
        
        if (is_writable($filepath)) {
            $content = file_get_contents($filepath);
            $content = str_replace('app_installer.php', 'app.php', $content);

            file_put_contents($filepath, $content);
        }
        
        // Suppression "hard" du cache de prod (si présent) pour s'assurer qu'il contient bien les derniers paramètres
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

        return $this->redirect($this->generateUrl('_welcome'));
    }
}
