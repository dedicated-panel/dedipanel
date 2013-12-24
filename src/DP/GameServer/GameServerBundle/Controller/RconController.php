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

namespace DP\GameServer\GameServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class RconController extends Controller
{
    abstract public function getEntityRepository();
    abstract public function getRconFromServer(GameServer $server);
    abstract public function getBaseRoute();
    abstract protected function isGranted();

    public function consoleJsonAction($id)
    {
        if (!$this->isGranted()) {
            throw new AccessDeniedException;
        }
        
        $server = $this->getEntityRepository()->find($id);

        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }

        $response = new Response;
        $response->setCharset('utf-8');
        $response->headers->set('Content-type', 'application/json');

        $jsonResp = array();
        $trans = $this->get('translator');

        if ($server->getQuery()->isOnline() && !$server->getQuery()->isBanned()) {
            $form = $this->createRconForm($this->getFormDefaultValues($server))->getForm();
            $request = $this->get('request');

            if ($request->getMethod() == 'POST') {
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    // Enregistrement du mdp rcon
                    $server = $this->saveServerData($server, $data);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($server);
                    $em->flush();

                    // Exécution de la commande
                    $ret = $server
                                ->setRcon($this->getRconFromServer($server))
                                ->sendCmd($data['cmd']);

                    $jsonResp['cmd'] = $data['cmd'];
                    $jsonResp['ret'] = $ret;
                }
            }

        }
        elseif ($server->query->isBanned()) {
            $jsonResp['error'] = $trans->trans('game.banned');
        }
        else {
            $jsonResp['error'] = $trans->trans('game.offline');
        }

        $response->setContent(json_encode($jsonResp));

        return $response;
    }

    public function consoleAction($id)
    {
        if (!$this->isGranted()) {
            throw new AccessDeniedException;
        }
        
        $server = $this->getEntityRepository()->find($id);

        if (!$server) {
            throw $this->createNotFoundException('Unable to find GameServer entity.');
        }

        $log = '';
        $form = $this->createRconForm()->getForm();

        if ($server->getQuery()->isOnline() && !$server->getQuery()->isBanned()) {
            $request = $this->get('request');

            if ($request->getMethod() == 'POST') {
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    // Exécution de la commande
                    $ret = $server
                        ->setRcon($this->getRconFromServer($server))
                        ->sendCmd($data['cmd']);

                    $log = '> ' . $data['cmd'] . "\n" . $ret . "\n";
                }
            }
        }

        return $this->render('DPGameServerBundle:Rcon:console.html.twig', array(
            'log' => $log,
            'form' => $form->createView(),
            'baseRoute' => $this->getBaseRoute(),
            'sid' => $server->getId(),
            'online' => $server->getQuery()->isOnline(),
            'banned' => $server->getQuery()->isBanned(),
        ));
    }

    public function createRconForm(array $default = array())
    {
        $form = $this->createFormBuilder($default)->add('cmd', 'text', array('label' => 'game.rcon.command'));

        return $form;
    }
}
