<?php

namespace DP\Admin\GameBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\Core\GameBundle\Entity\Game;
use DP\Admin\GameBundle\Form\GameType;
use DP\Core\UserBundle\Breadcrumb\Item\BreadcrumbItem;

/**
 * Game controller.
 *
 */
class GameController extends Controller
{

    /**
     * Lists all Game entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DPGameBundle:Game')->findAll();
        
        $this->createBreadcrumb();

        return $this->render('DPAdminGameBundle:Game:index.html.twig', array(
            'entities' => $entities,
            'csrf_token' => $this->getCsrfToken('game_admin.batch'), 
        ));
    }
    /**
     * Creates a new Game entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Game();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('game_admin_show', array('id' => $entity->getId())));
        }

        return $this->render('DPAdminGameBundle:Game:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Game entity.
    *
    * @param Game $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Game $entity)
    {
        $form = $this->createForm(new GameType(), $entity, array(
            'action' => $this->generateUrl('game_admin_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Game entity.
     *
     */
    public function newAction()
    {
        $entity = new Game();
        $form   = $this->createCreateForm($entity);

        return $this->render('DPAdminGameBundle:Game:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Game entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPGameBundle:Game')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $this->createBreadcrumb(array(
            array('label' => $entity->getName(), 'route' => 'game_admin_edit', 'params' => array('id' => $entity->getId())), 
        ));

        return $this->render('DPAdminGameBundle:Game:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Game entity.
    *
    * @param Game $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Game $entity)
    {
        $form = $this->createForm(new GameType(), $entity, array(
            'action' => $this->generateUrl('game_admin_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'admin.update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }
    /**
     * Edits an existing Game entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPGameBundle:Game')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('game_admin'));
        }
        
        $this->createBreadcrumb(array(
            array('label' => $entity->getName(), 'route' => 'game_admin_edit', 'params' => array('id' => $entity->getId())), 
        ));

        return $this->render('DPAdminGameBundle:Game:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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
            $entity = $em->getRepository('DPGameBundle:Game')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Game entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('game_admin'));
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
            ->setAction($this->generateUrl('game_admin_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'admin.delete'))
            ->getForm()
        ;
    }
    
    private function createBreadcrumb(array $elements = array())
    {
        $items = array();
        $items[] = new BreadcrumbItem('&#8962;', '_welcome', array(), array('safe_label' => true));
        $items[] = new BreadcrumbItem('menu.admin.game', 'game_admin');
        
        foreach ($elements AS $el) {
            if (isset($el['label']) && !empty($el['label'])) {                
                $items[] = new BreadcrumbItem($el['label'], $el['route'], $el['params']);
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
}
