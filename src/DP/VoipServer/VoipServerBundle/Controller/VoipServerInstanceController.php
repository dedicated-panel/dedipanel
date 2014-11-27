<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\VoipServerBundle\Controller;

use DP\Core\CoreBundle\Controller\Server\ServerController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class VoipServerInstanceController extends ServerController
{
    /** @var Request $request */
    private $request;


    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if ($container !== null) {
            $this->domainManager = new VoipServerInstanceDomainManager(
                $container->get($this->config->getServiceName('manager')),
                $container->get('event_dispatcher'),
                $this->flashHelper,
                $this->config,
                $container->get('twig')
            );
        }
    }

    public function indexAction(Request $request)
    {
        $this->isGrantedOr403('INDEX', $this->findServer($request));

        return parent::indexAction($request);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $this->isGrantedOr403('CREATE', $this->findServer($request));

        $resource = $this->createNewFromRequest($request);
        $form = $this->getForm($resource);

        if ($form->handleRequest($request)->isValid()) {
            $resource = $this->domainManager->create($resource);

            if (null !== $resource) {
                return $this->redirectHandler->redirectTo($resource);
            }
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('create.html'))
            ->setData(array(
                $this->config->getResourceName() => $resource,
                'form'                           => $form->createView()
            ))
        ;

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request)
    {
        $this->isGrantedOr403('UPDATE', $this->find($request));

        $resource = $this->findOr404($request);
        $form = $this->getForm($resource);
        $method = $request->getMethod();

        if (in_array($method, array('POST', 'PUT', 'PATCH')) &&
            $form->submit($request, !$request->isMethod('PATCH'))->isValid()) {
            if (null !== $this->domainManager->update($resource)) {
                return $this->redirectHandler->redirectTo($resource);
            }
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('update.html'))
            ->setData(array(
                $this->config->getResourceName() => $resource,
                'form'                           => $form->createView()
            ))
        ;

        return $this->handleView($view);
    }

    protected function findServer(Request $request)
    {
        return $this->resourceResolver->getResource(
            $this->get('dedipanel.repository.teamspeak'),
            'findOneBy',
            array(array('id' => intval($request->get('serverId'))))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createNewFromRequest(Request $request)
    {
        return $this->resourceResolver->createResource(
            $this->getRepository(),
            'createNewInstance',
            array($this->findServer($request))
        );
    }
}
