<?php

namespace DP\GameServer\MinecraftServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer;
use DP\GameServer\MinecraftServerBundle\Form\MinecraftServerType;

/**
 * MinecraftServer controller.
 *
 * @Route("/")
 */
class MinecraftServerController extends Controller
{
    /**
     * Lists all MinecraftServer entities.
     *
     * @Route("/", name="")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a MinecraftServer entity.
     *
     * @Route("/{id}/show", name="_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new MinecraftServer entity.
     *
     * @Route("/new", name="_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new MinecraftServer();
        $form   = $this->createForm(new MinecraftServerType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new MinecraftServer entity.
     *
     * @Route("/create", name="_create")
     * @Method("POST")
     * @Template("DPMinecraftServerBundle:MinecraftServer:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new MinecraftServer();
        $form = $this->createForm(new MinecraftServerType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('minecraft_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing MinecraftServer entity.
     *
     * @Route("/{id}/edit", name="_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }

        $editForm = $this->createForm(new MinecraftServerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing MinecraftServer entity.
     *
     * @Route("/{id}/update", name="_update")
     * @Method("POST")
     * @Template("DPMinecraftServerBundle:MinecraftServer:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new MinecraftServerType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('minecraft_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a MinecraftServer entity.
     *
     * @Route("/{id}/delete", name="_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl(''));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
