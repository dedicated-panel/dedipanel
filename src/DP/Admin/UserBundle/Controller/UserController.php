<?php

namespace DP\Admin\UserBundle\Controller;

use DP\Admin\AdminBundle\Controller\CRUDController;
use DP\Admin\AdminBundle\Processor\CRUDProcessor;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends CRUDController
{
    public function getDescriptor()
    {
        return $this->get('dp.descriptor.user_admin');
    }
    
    public function changeStatusAction($id, $newStatus)
    {
        if ($newStatus != 'enabled' && $newStatus != 'disabled') {
            throw new BadRequestHttpException("Argument $newStatus non supporté.");
        }
        
        $descriptor = $this->getDescriptor();
        $entity = $descriptor->getRepository()->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }
        
        if ($newStatus == 'enabled') {
            $entity->setEnabled(true);
        }
        elseif ($newStatus == 'disabled') {
            $entity->setEnabled(false);
        }
        
        $this->getDescriptor()->getProcessor()->updateProcess($entity);
        
        $this->get('session')->getFlashBag()->add('dp_flash_info', $descriptor->getName() . '.' . $newStatus . '_succeed');
        
        return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
    }
}
