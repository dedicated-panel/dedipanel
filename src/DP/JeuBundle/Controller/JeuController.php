<?php

namespace DP\JeuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\JeuBundle\Entity\Jeu;
use DP\JeuBundle\Form\JeuType;

/**
 * Jeu controller.
 *
 */
class JeuController extends Controller
{
    /**
     * Lists all Jeu entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('DPJeuBundle:Jeu')->findAll();

        return $this->render('DPJeuBundle:Jeu:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Jeu entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPJeuBundle:Jeu')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Jeu entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPJeuBundle:Jeu:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Jeu entity.
     *
     */
    public function newAction()
    {
        $entity = new Jeu();
        $form   = $this->createForm(new JeuType(), $entity);

        return $this->render('DPJeuBundle:Jeu:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Jeu entity.
     *
     */
    public function createAction()
    {
        $entity  = new Jeu();
        $request = $this->getRequest();
        $form    = $this->createForm(new JeuType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('jeu_show', array('id' => $entity->getId())));
            
        }

        return $this->render('DPJeuBundle:Jeu:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Jeu entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPJeuBundle:Jeu')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Jeu entity.');
        }

        $editForm = $this->createForm(new JeuType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPJeuBundle:Jeu:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Jeu entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPJeuBundle:Jeu')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Jeu entity.');
        }

        $editForm   = $this->createForm(new JeuType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('jeu_show', array('id' => $id())));
        }

        return $this->render('DPJeuBundle:Jeu:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Jeu entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('DPJeuBundle:Jeu')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Jeu entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('jeu'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
