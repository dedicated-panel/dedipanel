<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace DP\Core\MachineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\Core\MachineBundle\Entity\Machine;
use DP\Core\MachineBundle\Form\AddMachineType;
use DP\Core\MachineBundle\Form\EditMachineType;
use Symfony\Component\Form\FormError;
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
        $form->bind($request);

        if ($form->isValid()) {
            $sec = PHPSeclibWrapper::getFromMachineEntity($entity);
            $test = $sec->connectionTest();

            if ($test) {
                $this->generateKeyPair($entity);

                $this->getMachineInfos($sec, $entity);
                $is64Bit = $entity->getIs64Bit();

                if ($is64Bit) {
                    if (!$sec->hasCompatLib()) {
                        $this->get('session')->setFlash('compatLib', 'machine.compatLibNotInstalled');
                    }
                }

                if (!$sec->javaInstalled()) {
                    $this->get('session')->setFlash('compatLib', 'machine.javaNotInstalled');
                }

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('machine_show', array('id' => $entity->getId())));
            }
            else {
                $trans = $this->get('translator')->trans('machine.identNotGood');
                $form->addError(new FormError($trans));
            }
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

        $editForm->bind($request);

        if ($editForm->isValid()) {
            // Si l'utilisateur a précisé son mdp, on régénère une paire de clé
            $password = $entity->getPassword();
            if (!empty($password)) {
                $sec = PHPSeclibWrapper::getFromMachineEntity($entity);
                $test = $sec->connectionTest();

                // Si le test de connexion à réussi, on génère la paire de clé
                if ($test) {
                    $this->generateKeyPair($entity);

                    $this->getMachineInfos($sec, $entity);
                    $is64Bit = $entity->getIs64Bit();

                    if ($is64Bit) {
                        if (!$sec->hasCompatLib()) {
                            $this->get('session')->setFlash('compatLib', 'machine.compatLibNotInstalled');
                        }
                    }

                    if (!$sec->javaInstalled()) {
                        $this->get('session')->setFlash('compatLib', 'machine.javaNotInstalled');
                    }

                    $em->persist($entity);
                    $em->flush();

                    return $this->redirect($this->generateUrl('machine'));
                }
                // Sinon ajout d'un message d'erreur sur le formulaire
                else {
                    $trans = $this->get('translator')->trans('machine.identNotGood');
                    $form->addError(new FormError($trans));
                }
            }
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
        $secure->setPasswd($entity->getPassword());

        if ($delete) $secure->deleteKeyPair($entity->getPublicKey());

        $privkeyFilename = uniqid('', true);
        $pubKey = $secure->createKeyPair($privkeyFilename);

        $entity->setPrivateKeyFilename($privkeyFilename);
        $entity->setPublicKey($pubKey);

        $this->getMachineInfos($secure, $entity);

        return true;
    }

    protected function getMachineInfos(PHPSeclibWrapper $secure, Machine $entity)
    {
        $entity->setHome($secure->getHome());
        $entity->setNbCore($entity->retrieveNbCore());
        $entity->setIs64bit($secure->is64bitSystem());
    }

    /**
     * Deletes a Machine entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

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

        $is64Bit = false;
        $compatLib = false;

        try {
            $secure = PHPSeclibWrapper::getFromMachineEntity($entity);
            $test = $secure->connectionTest();

            $this->getMachineInfos($secure, $entity);
            $is64Bit = $entity->getIs64Bit();

            if ($is64Bit) {
                $compatLib = $secure->hasCompatLib();
            }

            $javaInstalled = $secure->javaInstalled();

            $em->persist($entity);
            $em->flush($entity);
        }
        catch (PHPSeclibWrapper\Exception\ConnectionErrorException $e) {
            $test = false;
        }

        return $this->render('DPMachineBundle:Machine:connectionTest.html.twig', array(
            'machine' => $entity,
            'result' => $test,
            'hasCompatLib' => $compatLib,
            'javaInstalled' => $javaInstalled,
        ));
    }
}
