<?php

namespace DP\Core\MachineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\Core\MachineBundle\Entity\Machine;
use DP\Core\MachineBundle\Form\AddMachineType;
use DP\Core\MachineBundle\Form\EditMachineType;

use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

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
        $form   = $this->createForm(new AddMachineType(), $entity);

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
        $form    = $this->createForm(new AddMachineType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $this->generateKeyPair($entity);
            
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

        $editForm = $this->createForm(new EditMachineType(), $entity);
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

        $editForm   = $this->createForm(new EditMachineType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            // Si l'utilisateur a précisé son mdp, on régénère une paire de clé
            if (!empty($entity->passwd)) {
                $this->generateKeyPair($entity, true);
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('machine'));
        }

        return $this->render('DPMachineBundle:Machine:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    private function generateKeyPair(Machine $entity, $delete = false)
    {
        $secure = PHPSeclibWrapper::getFromMachineEntity($entity, false);
        $secure->setPasswd($entity->getPasswd());

        if ($delete) $secure->deleteKeyPair($entity->getPublicKey());
        
        $privkeyFilename = uniqid('', true);
        $pubKey = $secure->createKeyPair($privkeyFilename);
        
        $entity->setPrivateKeyFilename($privkeyFilename);
        $entity->setHome($secure->getHome());
        $nbCore = $entity->retrieveNbCore();
        $entity->setNbCore($nbCore);
        $entity->setPublicKey($pubKey);

        return true;
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
            
            try {
                $secure = PHPSeclibWrapper::getFromMachineEntity($entity);
                $secure->deleteKeyPair($entity->getPublicKey());
            }
            catch (\Exception $e) {}
            
            foreach ($entity->getGameServers() AS $srv) {
                $entity->getGameServers()->removeElement($srv);
                $em->remove($srv);
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
    
    public function connectionTestAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('DPMachineBundle:Machine')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Machine entity.');
        }
        
        try {    
            $secure = PHPSeclibWrapper::getFromMachineEntity($entity);
            $secure->setKeyfile($entity->getPrivateKeyFilename());
            $test = $secure->connectionTest();
        }
        catch (PHPSeclibWrapper\Exception\ConnectionErrorException $e) {
            $test = false;
        }
        
        return $this->render('DPMachineBundle:Machine:connectionTest.html.twig', array(
            'mid' => $id, 
            'result' => $test, 
        ));
    }
}
