<?php

namespace DP\Core\DistributionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfiguratorController extends Controller
{
    public function indexAction()
    {
        $request = $this->get('request');
        $form = $this->createFormBuilder()
                     ->add('type', 'choice', array(
                            'choices' => array(
                                'i' => 'configurator.install', 
                                'u' => 'configurator.update'
                            ), 
                            'label' => 'configurator.chooseType'))
                     ->getForm();
        
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            
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
                
                return new RedirectResponse($url);
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
            $step = $configurator->getUpdatetStep($index);
            $stepCount = $configurator->getUpdateStepCount();
        }
        
        $form = $this->container->get('form.factory')->create($step->getFormType(), $step);
        $request = $this->container->get('request');
        
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            
            if ($form->isValid()) {                
                if ($step->run($form->getData(), $type) !== false) {
                    
                    ++$index;                    
                    if ($index < $stepCount) {
                        return new RedirectResponse($this->container->get('router')->generate('installer_step', array('type' => $type, 'index' => $index)));
                    }
                    
                    return new RedirectResponse($this->container->get('router')->generate('installer_final', array('type' => $type)));
                }
            }
        }
        
        return $this->render($step->getTemplate(), array(
            'form'          => $form->createView(),
            'configType'    => $type, 
            'index'         => $index,
            'count'         => $stepCount
        ));
    }

    public function finalAction($type)
    {
        $configurator = $this->get('dp.webinstaller');
        $configurator->clean();
        
        return $this->render('DPDistributionBundle:Configurator:final.html.twig');
    }
}
