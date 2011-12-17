<?php

namespace DP\MachineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\MachineBundle\Entity\Machine;
use DP\MachineBundle\Form\MachineType;

use DP\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

/**
 * Machine controller.
 *
 */
class MachineController extends Controller
{
    /**
     * Lists all Machine entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('DPMachineBundle:Machine')->findAll();

        return $this->render('DPMachineBundle:Machine:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Machine entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPMachineBundle:Machine')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Machine entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPMachineBundle:Machine:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Machine entity.
     *
     */
    public function newAction()
    {
        $entity = new Machine();
        $form   = $this->createForm(new MachineType(), $entity);

        return $this->render('DPMachineBundle:Machine:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Machine entity.
     *
     */
    public function createAction()
    {
        $entity  = new Machine();
        $request = $this->getRequest();
        $form    = $this->createForm(new MachineType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $secure = PHPSeclibWrapper::getFromMachineEntity($entity);
            $secure->setPasswd($entity->getPasswd());
            $secure->connectionTest();
            
            $privkeyFilename = uniqid('', true);
            $secure->createKeyPair($privkeyFilename);
            $entity->setPrivateKeyFilename($privkeyFilename);
            $entity->setHome($secure->getHome());
            
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('machine_show', array('id' => $entity->getId())));
            
        }

        return $this->render('DPMachineBundle:Machine:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Machine entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPMachineBundle:Machine')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Machine entity.');
        }

        $editForm = $this->createForm(new MachineType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPMachineBundle:Machine:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Machine entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPMachineBundle:Machine')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Machine entity.');
        }

        $editForm   = $this->createForm(new MachineType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('machine_edit', array('id' => $id)));
        }

        return $this->render('DPMachineBundle:Machine:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Machine entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('DPMachineBundle:Machine')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Machine entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('machine'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    public function connectionTestAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPMachineBundle:Machine')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Machine entity.');
        }
        
        $secure = PHPSeclibWrapper::getFromMachineEntity($entity);
        $secure->setKeyfile($entity->getPrivateKeyFilename());
        $secure->connectionTest();
        
        return $this->render('DPMachineBundle:Machine:connectionTest.html.twig');
    }
}
