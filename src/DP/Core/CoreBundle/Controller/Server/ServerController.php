<?php

namespace DP\Core\CoreBundle\Controller\Server;

use DP\Core\CoreBundle\Controller\ResourceController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use DP\Core\CoreBundle\Model\ServerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServerController extends ResourceController
{
    /**
     * @var ServerDomainManager
     */
    protected $domainManager;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if ($container !== null) {
            $this->domainManager = new ServerDomainManager(
                $container->get($this->config->getServiceName('manager')),
                $container->get('event_dispatcher'),
                $this->flashHelper,
                $this->config,
                $container->get('twig')
            );
        }
    }

    /**
     * @param  Request          $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $this->isGrantedOr403('DELETE', $this->find($request));

        $resource = $this->findOr404($request);
        $delete = $this->domainManager->delete($resource, $request->query->get('fromMachine'));

        if ($delete === null) {
            return $this->redirectHandler->redirectToReferer();
        }

        return $this->redirectHandler->redirectToIndex();
    }

    public function changeStateAction(Request $request)
    {
        $this->isGrantedOr403('STATE', $this->find($request));

        $server = $this->findOr404($request);
        $state = $request->get('state');

        // Authorized "state" value
        $actions = [
            ServerInterface::ACTION_START,
            ServerInterface::ACTION_STOP,
            ServerInterface::ACTION_RESTART,
        ];

        if (!in_array($state, $actions)) {
            throw new BadRequestHttpException;
        }

        $this->domainManager->changeState($server, $state);

        return $this->redirectHandler->redirectToReferer();
    }
}
