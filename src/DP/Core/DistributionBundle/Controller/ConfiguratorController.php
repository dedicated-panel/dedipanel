<?php

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
                    'u' => 'configurator.update'
                ), 
                'label' => 'configurator.chooseType')
            )->getForm();
        
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
            $form->bindRequest($request);
            
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
        $configurator = $this->get('dp.webinstaller');
        $configurator->clean();
        
        return $this->render('DPDistributionBundle:Configurator:final.html.twig');
    }
}
