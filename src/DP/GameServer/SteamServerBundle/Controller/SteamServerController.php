<?php

namespace DP\GameServer\SteamServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use DP\GameServer\SteamServerBundle\Form\SteamServerType;

/**
 * SteamServer controller.
 *
 */
class SteamServerController extends Controller
{
    /**
     * Lists all SteamServer entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('DPSteamServerBundle:SteamServer')->findAll();

        return $this->render('DPSteamServerBundle:SteamServer:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a SteamServer entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPSteamServerBundle:SteamServer:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new SteamServer entity.
     *
     */
    public function newAction()
    {
        $entity = new SteamServer();
        $form   = $this->createForm(new SteamServerType(), $entity);

        return $this->render('DPSteamServerBundle:SteamServer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new SteamServer entity.
     *
     */
    public function createAction()
    {
        $entity  = new SteamServer();
        $request = $this->getRequest();
        $form    = $this->createForm(new SteamServerType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('steam_show', array('id' => $entity->getId())));
            
        }

        return $this->render('DPSteamServerBundle:SteamServer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing SteamServer entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $editForm = $this->createForm(new SteamServerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPSteamServerBundle:SteamServer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing SteamServer entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $editForm   = $this->createForm(new SteamServerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('steam_show', array('id' => $id)));
        }

        return $this->render('DPSteamServerBundle:SteamServer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SteamServer entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SteamServer entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('steam'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
