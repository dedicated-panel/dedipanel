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
    
    /**
     * Displays a form to edit an existing entity.
     *
     */
    public function editRolesAction($id)
    {
        $descriptor = $this->getDescriptor();
        $entity     = $descriptor->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditRolesForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $this->createBreadcrumb(array(
            array(
                'label' => $entity, 
                'route' => $descriptor->getRoute('editRoles'), 
                'params' => array('id' => $entity->getId())
            ), 
        ));

        return $this->render($descriptor->getTemplate('editRoles'), array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(), 
            'descriptor' => $descriptor, 
        ));
    }
    
    public function updateRolesAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $descriptor = $this->getDescriptor();
        $entity     = $descriptor->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }
        
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditRolesForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->getDescriptor()->getProcessor()->updateProcess($entity);
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $descriptor->getName() . '.update_succeed');

            return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
        }
        
        $this->createBreadcrumb(array(
            array(
                'label' => $entity, 
                'route' => $descriptor->getRoute('editRoles'), 
                'params' => array('id' => $entity->getId())
            ), 
        ));

        return $this->render($descriptor->getTemplate('editRoles'), array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(), 
            'descriptor'  => $descriptor, 
        ));
    }
    
    private function createEditRolesForm($entity)
    {
        $descriptor = $this->getDescriptor();
        
        $form = $this->createForm($descriptor->getForm('editRoles'), $entity, array(
            'action' => $this->generateUrl($descriptor->getRoute('update'), array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'admin.update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }
}
