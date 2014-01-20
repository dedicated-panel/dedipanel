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

namespace DP\GameServer\SteamServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use DP\GameServer\SteamServerBundle\Form\AddSteamServerType;
use DP\GameServer\SteamServerBundle\Form\EditSteamServerType;
use Symfony\Component\Form\FormError;
use DP\GameServer\SteamServerBundle\Exception\InstallAlreadyStartedException;
use PHPSeclibWrapper\Exception\MissingPacketException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_SHOW')) {
            throw new AccessDeniedException;
        }
        
        $em = $this->getDoctrine()->getManager();

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
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_SHOW')) {
            throw new AccessDeniedException;
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPSteamServerBundle:SteamServer:show.html.twig', array(
            'entity'            => $entity,
            'delete_form'       => $deleteForm->createView(),
            'delete_all_form'   => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new SteamServer entity.
     *
     */
    public function newAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_ADD')) {
            throw new AccessDeniedException;
        }
        
        $entity = new SteamServer();
        $form   = $this->createForm(new AddSteamServerType(), $entity);

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
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_ADD')) {
            throw new AccessDeniedException;
        }
        
        $entity  = new SteamServer();
        $request = $this->getRequest();
        $form    = $this->createForm(new AddSteamServerType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $alreadyInstalled = $form->get('alreadyInstalled')->getData();
            $twig = $this->get('twig');

            // Affichage d'une erreur sur le formulaire si l'installation est déjà en cours.
            try {
                // On lance l'installation si le serveur n'est pas déjà sur la machine,
                // Sinon on upload les scripts nécessaires au panel
                if (!$alreadyInstalled) {
                    $entity->installServer($twig);
                }
                else {
                    $entity->uploadShellScripts($twig);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('steam_show', array('id' => $entity->getId())));
            }
            catch (InstallAlreadyStartedException $e) {
                $trans = $this->get('translator')->trans('game.installAlreadyStarted');
                $form->addError(new FormError($trans));
            }
            catch (MissingPacketException $e) {
                $trans = $this->get('translator')->trans('steam.missingCompatLib');
                $form->addError(new FormError($trans));
            }
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
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_EDIT')) {
            throw new AccessDeniedException;
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $editForm = $this->createForm(new EditSteamServerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DPSteamServerBundle:SteamServer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'delete_all_form'   => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing SteamServer entity.
     *
     */
    public function updateAction($id)
    {
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_EDIT')) {
            throw new AccessDeniedException;
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SteamServer entity.');
        }

        $editForm   = $this->createForm(new EditSteamServerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->bind($this->getRequest());

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('steam_show', array('id' => $id)));
        }

        return $this->render('DPSteamServerBundle:SteamServer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'delete_all_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SteamServer entity.
     *
     */
    public function deleteAction($id, $fromMachine)
    {
        if (!$this->get('security.context')->isGranted('ROLE_DP_STEAM_DELETE')) {
            throw new AccessDeniedException;
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DPSteamServerBundle:SteamServer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SteamServer entity.');
            }

            if ($fromMachine) {
                $entity->removeFromServer();
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
