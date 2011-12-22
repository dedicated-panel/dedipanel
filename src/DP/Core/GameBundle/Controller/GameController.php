<?php

namespace DP\Core\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\Core\GameBundle\Entity\Game;
use DP\Core\GameBundle\Form\GameType;

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
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('DPGameBundle:Game')->findAll();

        return $this->render('DPGameBundle:Game:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Game entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPGameBundle:Game')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPGameBundle:Game:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Game entity.
     *
     */
    public function newAction()
    {
        $entity = new Game();
        $form   = $this->createForm(new GameType(), $entity);

        return $this->render('DPGameBundle:Game:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Game entity.
     *
     */
    public function createAction()
    {
        $entity  = new Game();
        $request = $this->getRequest();
        $form    = $this->createForm(new GameType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('game_show', array('id' => $entity->getId())));
            
        }

        return $this->render('DPGameBundle:Game:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Game entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPGameBundle:Game')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }

        $editForm = $this->createForm(new GameType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPGameBundle:Game:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Game entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPGameBundle:Game')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }

        $editForm   = $this->createForm(new GameType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('game_show', array('id' => $id())));
        }

        return $this->render('DPGameBundle:Game:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Game entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('DPGameBundle:Game')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Game entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('game'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
