<?php

namespace DP\Core\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\Core\GameBundle\Entity\Plugin;
use DP\Core\GameBundle\Form\PluginType;

/**
 * Plugin controller.
 *
 */
class PluginController extends Controller
{
    /**
     * Lists all Plugin entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('DPGameBundle:Plugin')->findAll();

        return $this->render('DPGameBundle:Plugin:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Plugin entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPGameBundle:Plugin')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Plugin entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPGameBundle:Plugin:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Plugin entity.
     *
     */
    public function newAction()
    {
        $entity = new Plugin();
        $form   = $this->createForm(new PluginType(), $entity);

        return $this->render('DPGameBundle:Plugin:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Plugin entity.
     *
     */
    public function createAction()
    {
        $entity  = new Plugin();
        $request = $this->getRequest();
        $form    = $this->createForm(new PluginType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('plugin_show', array('id' => $entity->getId())));
            
        }

        return $this->render('DPGameBundle:Plugin:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Plugin entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPGameBundle:Plugin')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Plugin entity.');
        }

        $editForm = $this->createForm(new PluginType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPGameBundle:Plugin:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Plugin entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPGameBundle:Plugin')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Plugin entity.');
        }

        $editForm   = $this->createForm(new PluginType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('plugin_edit', array('id' => $id)));
        }

        return $this->render('DPGameBundle:Plugin:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Plugin entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('DPGameBundle:Plugin')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Plugin entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('plugin'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
