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

namespace DP\GameServer\MinecraftServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer;
use DP\GameServer\MinecraftServerBundle\Form\AddMinecraftServerType;
use DP\GameServer\MinecraftServerBundle\Form\EditMinecraftServerType;
use PHPSeclibWrapper\Exception\MissingPacketException;

/**
 * MinecraftServer controller.
 *
 */
class MinecraftServerController extends Controller
{
    /**
     * Lists all MinecraftServer entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->findAll();
        
        return $this->render('DPMinecraftServerBundle:MinecraftServer:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a MinecraftServer entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPMinecraftServerBundle:MinecraftServer:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'delete_all_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new MinecraftServer entity.
     *
     */
    public function newAction()
    {
        $entity = new MinecraftServer();
        $form   = $this->createForm(new AddMinecraftServerType(), $entity);

        return $this->render('DPMinecraftServerBundle:MinecraftServer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new MinecraftServer entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new MinecraftServer();
        $form = $this->createForm(new AddMinecraftServerType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $alreadyInstalled = $form->get('alreadyInstalled')->getData();
            
            try {
                // On lance l'installation si le serveur n'est pas déjà sur la machine, 
                // Sinon on upload les scripts nécessaires au panel
                if (!$alreadyInstalled) {
                    $entity->installServer();
                }
                else {
                    $entity->uploadShellScripts($this->get('twig'));
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
                
                return $this->redirect($this->generateUrl('minecraft_show', array('id' => $entity->getId())));
            }
            catch (MissingPacketException $e) {
                $trans = $this->get('translator')->trans('minecraft.javaMissing');
                $form->addError(new FormError($trans));
            }
        }

        return $this->render('DPMinecraftServerBundle:MinecraftServer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing MinecraftServer entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }

        $editForm = $this->createForm(new EditMinecraftServerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPMinecraftServerBundle:MinecraftServer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'delete_all_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing MinecraftServer entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new EditMinecraftServerType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('minecraft_edit', array('id' => $id)));
        }

        return $this->render('DPMinecraftServerBundle:MinecraftServer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'delete_all_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a MinecraftServer entity.
     *
     */
    public function deleteAction(Request $request, $id, $fromMachine)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
            }
            
            if ($fromMachine) {
                $entity->removeFromServer();
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('minecraft'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * Recover installation status
     * Upload HLDS Scripts
     */
    public function installAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }
        
        $status = $entity->getInstallationStatus();
        
        // On upload le script du panel si l'archive jar est téléchargé
        if ($status >= 100) {
            $entity->uploadShellScripts($this->get('twig'));
        }  
        // On récupère le statut de l'installation que si celui-ci
        // N'est pas déjà indiqué comme terminé
        elseif ($status < 100) {
            $newStatus = $entity->getInstallationProgress($this->get('twig'));
            $entity->setInstallationStatus($newStatus);
            
            // On upload les scripts si le dl est terminé
            if ($newStatus == 100) {
                $entity->uploadShellScripts($this->get('twig'));
                $entity->uploadDefaultServerPropertiesFile($this->get('twig'));
            }
            // Si celui-ci n'est pas lancé on le lance
            elseif ($newStatus === null) {
                $entity->installServer();
            }
            
        } 
        // On vérifie que l'installation n'est pas bloqué (ou non démarré)
        elseif ($status === null) {
            $entity->installServer($this->get('twig'));
        }

        $em->persist($entity);
        $em->flush();
        
        return $this->redirect($this->generateUrl('minecraft'));
    }
    
    public function changeStateAction($id, $state)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('DPMinecraftServerBundle:MinecraftServer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MinecraftServer entity.');
        }
        
        $entity->changeStateServer($state);
        
        return $this->redirect($this->generateUrl('minecraft'));
    }
}
