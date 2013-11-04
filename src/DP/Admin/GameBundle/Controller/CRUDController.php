<?php

namespace DP\Admin\GameBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DP\Core\UserBundle\Breadcrumb\Item\BreadcrumbItem;

abstract class CRUDController extends Controller
{
    abstract protected function createEntity();
    abstract protected function getRepository();
    abstract protected function getBaseRoute();
    abstract protected function getTplDir();
    abstract protected function getFormType();
    
    
    /**
     * Lists all Game entities.
     *
     */
    public function indexAction()
    {
        $entities = $this->getRepository()->findAll();
        
        $this->createBreadcrumb();

        return $this->render('DPAdminGameBundle:' . $this->getTplDir() . ':index.html.twig', array(
            'entities' => $entities,
            'csrf_token' => $this->getCsrfToken($this->getBaseRoute() . '_admin.batch'), 
            'baseRoute' => $this->getBaseRoute(), 
        ));
    }

    /**
     * Displays a form to create a new Game entity.
     *
     */
    public function newAction()
    {
        $entity    = $this->createEntity();
        $baseRoute = $this->getBaseRoute();
        
        $form   = $this->createCreateForm($entity);
        
        $this->createBreadcrumb(array(
            array('label' => $baseRoute . '_admin.add', 'route' => $baseRoute . '_admin_new'), 
        ));

        return $this->render('DPAdminGameBundle:' . $this->getTplDir() . ':new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'baseRoute' => $this->getBaseRoute(), 
        ));
    }
    
    /**
     * Creates a new Game entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity    = $this->createEntity();
        $baseRoute = $this->getBaseRoute();
        
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $baseRoute . '_admin.creation_succeed');

            return $this->redirect($this->generateUrl($baseRoute . '_admin'));
        }
        
        $this->createBreadcrumb(array(
            array('label' => $baseRoute . '_admin.add', 'route' => $baseRoute . '_admin_new'), 
        ));

        return $this->render('DPAdminGameBundle:' . $this->getTplDir() . ':new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'baseRoute' => $this->getBaseRoute(), 
        ));
    }

    /**
     * Displays a form to edit an existing Game entity.
     *
     */
    public function editAction($id)
    {
        $entity    = $this->getRepository()->find($id);
        $baseRoute = $this->getBaseRoute(); 

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $this->createBreadcrumb(array(
            array('label' => $entity->getName(), 'route' => $baseRoute . '_admin_edit', 'params' => array('id' => $entity->getId())), 
        ));

        return $this->render('DPAdminGameBundle:' . $this->getTplDir() . ':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'baseRoute' => $this->getBaseRoute(), 
        ));
    }
    
    /**
     * Edits an existing Game entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getRepository()->find($id);
        $baseRoute = $this->getBaseRoute();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $baseRoute . '_admin.update_succeed');

            return $this->redirect($this->generateUrl($baseRoute . '_admin'));
        }
        
        $this->createBreadcrumb(array(
            array('label' => $entity->getName(), 'route' => $baseRoute . '_admin_edit', 'params' => array('id' => $entity->getId())), 
        ));

        return $this->render('DPAdminGameBundle:' . $this->getTplDir() . ':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'baseRoute' => $this->getBaseRoute(), 
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
        
        $baseRoute = $this->getBaseRoute();

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $this->getRepository()->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $em->remove($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', $baseRoute . '_admin.delete_succeed');
        }

        return $this->redirect($this->generateUrl($baseRoute . '_admin'));
    }
    
    public function batchDeleteAction(Request $request)
    {
        $baseRoute = $this->getBaseRoute();
        
        $this->validateCsrfToken($baseRoute . '_admin.batch');
        
        $confirmation = $request->get('confirmation', false) == 'ok';
        $elements = $request->get('idx');
        
        if (empty($elements)) {
            $this->get('session')->getFlashBag()->add('dp_flash_info', 'admin.batch.empty');
            
            return $this->redirect($this->generateUrl($baseRoute . '_admin'));
        }
        
        if ($confirmation) {
            $em   = $this->getDoctrine()->getManager();
            $repo = $this->getRepository();
            $i = 0;
            
            foreach ($elements AS $el) {
                $entity = $repo->find($el);
                $em->remove($entity);
                
                ++$i;
                
                // Vide le cache de l'ORM afin de ne pas consommer trop de mémoire
                if (($i % 50) == 0) {
                    $em->flush();
                    $em->clear();
                }
            }
            
            $em->flush();
            $em->clear();
            
            $this->get('session')->getFlashBag()->add('dp_flash_info', 'admin.batch.delete_succeed');
            
            return $this->redirect($this->generateUrl($baseRoute . '_admin'));
        }
        else {
            $this->createBreadcrumb(array(
                array('label' => 'admin.batch.title', 'route' => $baseRoute . '_admin_batch_delete'), 
            ));
        
            return $this->render('DPAdminGameBundle:' . $this->getTplDir() . ':batch_confirmation.html.twig', array(
                'elements' => $elements, 
                'csrf_token' => $this->getCsrfToken('game_admin.batch'), 
                'baseRoute' => $this->getBaseRoute(), 
            ));
        }
    }
    
    
    
    
    protected function createBreadcrumb(array $elements = array())
    {
        $baseRoute = $this->getBaseRoute();
        
        $items = array();
        $items[] = new BreadcrumbItem('&#8962;', '_welcome', array(), array('safe_label' => true));
        $items[] = new BreadcrumbItem('menu.admin.' . $baseRoute, $baseRoute . '_admin');
        
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
        $form = $this->createForm($this->getFormType(), $entity, array(
            'action' => $this->generateUrl($this->getBaseRoute() . '_admin_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'admin.create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
    * Creates a form to edit a Game entity.
    *
    * @param $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm($entity)
    {
        $form = $this->createForm($this->getFormType(), $entity, array(
            'action' => $this->generateUrl($this->getBaseRoute() . '_admin_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'admin.update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Creates a form to delete a Game entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($this->getBaseRoute() . '_admin_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'admin.delete'))
            ->getForm()
        ;
    }
}
