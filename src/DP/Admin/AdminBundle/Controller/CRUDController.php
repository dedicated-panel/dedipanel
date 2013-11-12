<?php

namespace DP\Admin\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DP\Core\UserBundle\Breadcrumb\Item\BreadcrumbItem;

abstract class CRUDController extends Controller
{
    abstract protected function getDescriptor();
    
    /**
     * Lists all entities.
     *
     */
    public function indexAction()
    {
        $descriptor = $this->getDescriptor();
        
        $this->createBreadcrumb();

        return $this->render($descriptor->getTemplate('index'), array(
            'entities' => $descriptor->getRepository()->findAll(),
            'csrf_token' => $this->getCsrfToken($descriptor->getName() . '.batch'),
            'descriptor' => $descriptor, 
        ));
    }

    /**
     * Displays a form to create a new entity.
     *
     */
    public function newAction()
    {
        $descriptor = $this->getDescriptor();
        $entity     = $descriptor->getFactory()->createEntity();
        
        $form   = $this->createCreateForm($entity);
        
        $this->createBreadcrumb(array(
            array('label' => $descriptor->getName() . '.add', 'route' => $descriptor->getRoute('new')), 
        ));

        return $this->render($descriptor->getTemplate('new'), array(
            'entity' => $entity,
            'form'   => $form->createView(), 
            'descriptor' => $descriptor, 
        ));
    }
    
    /**
     * Creates a new entity.
     *
     */
    public function createAction(Request $request)
    {
        $descriptor = $this->getDescriptor();
        $entity     = $descriptor->getFactory()->createEntity();
        
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDescriptor()->getProcessor()->createProcess($entity);
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $descriptor->getName() . '.creation_succeed');

            return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
        }
        
        $this->createBreadcrumb(array(
            array('label' => $descriptor->getName() . '.add', 'route' => $descriptor->getRoute('new')), 
        ));

        return $this->render($descriptor->getTemplate('new'), array(
            'entity' => $entity,
            'form'   => $form->createView(), 
            'descriptor' => $descriptor, 
        ));
    }

    /**
     * Displays a form to edit an existing entity.
     *
     */
    public function editAction($id)
    {
        $descriptor = $this->getDescriptor();
        $entity     = $descriptor->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $this->createBreadcrumb(array(
            array('label' => $entity, 'route' => $descriptor->getRoute('edit'), 'params' => array('id' => $entity->getId())), 
        ));

        return $this->render($descriptor->getTemplate('edit'), array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'descriptor' => $descriptor, 
        ));
    }
    
    /**
     * Edits an existing Game entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $descriptor = $this->getDescriptor();
        $entity     = $descriptor->getRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->getDescriptor()->getProcessor()->updateProcess($entity);
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $descriptor->getName() . '.update_succeed');

            return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
        }
        
        $this->createBreadcrumb(array(
            array('label' => $entity->getName(), 'route' => $descriptor->getRoute('edit'), 'params' => array('id' => $entity->getId())), 
        ));

        return $this->render($descriptor->getTemplate('edit'), array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'descriptor' => $descriptor, 
        ));
    }
    
    /**
     * Deletes a Game entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $descriptor = $this->getDescriptor();
            $entity     = $descriptor->getRepository()->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $this->getDescriptor()->getProcessor()->deleteProcess($entity);
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $descriptor->getName() . '.delete_succeed');
        }

        return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
    }
    
    public function batchDeleteAction(Request $request)
    {
        $descriptor = $this->getDescriptor();
        
        $this->validateCsrfToken($descriptor->getName() . '.batch');
        
        $confirmation = $request->get('confirmation', false) == 'ok';
        $elements = $request->get('idx');
        
        if (empty($elements)) {
            $this->get('session')->getFlashBag()->add('dp_flash_info', 'admin.batch.empty');
            
            return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
        }
        
        if ($confirmation) {
            $this->getDescriptor()->getProcessor()->batchDeleteProcess($elements);
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', 'admin.batch.delete.succeed');
            
            return $this->redirect($this->generateUrl($descriptor->getRoute('index')));
        }
        
        $this->createBreadcrumb(array(
            array('label' => 'admin.batch.title', 'route' => $descriptor->getName() . '_batch_delete'), 
        ));
    
        return $this->render($descriptor->getTemplate('batch_confirmation'), array(
            'elements' => $elements, 
            'csrf_token' => $this->getCsrfToken($descriptor->getName() . '.batch'), 
            'descriptor' => $descriptor, 
        ));
    }
    
    protected function createBreadcrumb(array $elements = array())
    {
        $descriptor = $this->getDescriptor();
        
        $items = array();
        $items[] = new BreadcrumbItem('&#8962;', '_welcome', array(), array('safe_label' => true));
        $items[] = new BreadcrumbItem('menu.admin.' . $descriptor->getName(), $descriptor->getRoute('index'));
        
        foreach ($elements AS $el) {
            if (isset($el['label']) && !empty($el['label'])) {
                $params = isset($el['params']) ? $el['params'] : array();
                
                $items[] = new BreadcrumbItem($el['label'], $el['route'], $params);
            }
        }
        
        $this->get('dp_breadcrumb.items_bag')->setItems($items);
    }

    /**
     * @param $intention
     *
     * @return string
     */
    public function getCsrfToken($intention)
    {
        if (!$this->container->has('form.csrf_provider')) {
            return false;
        }

        return $this->container->get('form.csrf_provider')->generateCsrfToken($intention);
    }

    /**
     * Validate CSRF token for action with out form
     *
     * @param string $intention
     *
     * @throws \RuntimeException
     */
    public function validateCsrfToken($intention)
    {
        if (!$this->container->has('form.csrf_provider')) {
            return;
        }

        if (!$this->container->get('form.csrf_provider')->isCsrfTokenValid($intention, $this->get('request')->request->get('_csrf_token', false))) {
            throw new HttpException(400, "The csrf token is not valid, CSRF attack ?");
        }
    }

    /**
    * Creates a form to create a Game entity.
    *
    * @param Game $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm($entity)
    {
        $descriptor = $this->getDescriptor();
        
        $form = $this->createForm($descriptor->getForm('add'), $entity, array(
            'action' => $this->generateUrl($descriptor->getRoute('create')),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'admin.create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
    * Creates a form to edit a entity.
    *
    * @param $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm($entity)
    {
        $descriptor = $this->getDescriptor();
        
        $form = $this->createForm($descriptor->getForm('edit'), $entity, array(
            'action' => $this->generateUrl($descriptor->getRoute('update'), array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'admin.update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Creates a form to delete a entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($this->getDescriptor()->getRoute('delete'), array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'admin.delete'))
            ->getForm()
        ;
    }
}
