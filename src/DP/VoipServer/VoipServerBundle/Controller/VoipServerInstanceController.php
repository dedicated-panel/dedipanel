<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\VoipServerBundle\Controller;

use DP\Core\CoreBundle\Controller\Server\ServerController;
use Symfony\Component\HttpFoundation\Request;

class VoipServerInstanceController extends ServerController
{
    /** @var Request $request */
    private $request;


    public function indexAction(Request $request)
    {
        $this->isGrantedOr403('INDEX', $this->findServer($request));

        return parent::indexAction($request);
    }

    public function createAction(Request $request)
    {
        $this->isGrantedOr403('CREATE', $this->findServer($request));

        $this->request = $request;

        return parent::createAction($request);
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
    public function createNew()
    {
        return $this->resourceResolver->createResource(
            $this->getRepository(),
            'createNewInstance',
            array($this->findServer($this->request))
        );
    }
}
