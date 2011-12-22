<?php

namespace DP\Core\UserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller {
	public function menuAction() {
		return $this->render('DPUserBundle:User:menu.html.twig');
	}
}
?>