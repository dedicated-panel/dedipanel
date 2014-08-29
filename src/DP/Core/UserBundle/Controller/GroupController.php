<?php

namespace DP\Core\UserBundle\Controller;

use DP\Core\CoreBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends ResourceController
{
    /**
     * @param  Request          $request
     * @return RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $this->isGrantedOr403('DELETE', $this->find($request));

        $resource  = $this->findOr404($request);
        $hierarchy = $this
            ->get('dedipanel.repository.group')
            ->getAccessibleGroups(array($resource))
        ;

        foreach ($hierarchy AS $child) {
            if ($child->getUsers()->count() > 0) {
                $this
                    ->flashHelper
                    ->setFlash('error', 'dedipanel.group.users_yet_associated')
                ;

                return $this->redirectHandler->redirectToReferer();
            }
        }

        $this->domainManager->delete($resource);

        return $this->redirectHandler->redirectToIndex();
    }
}
