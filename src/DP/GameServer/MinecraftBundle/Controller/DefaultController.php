<?php

namespace DP\GameServer\MinecraftBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DPMinecraftBundle:Default:index.html.twig', array('name' => $name));
    }
}
