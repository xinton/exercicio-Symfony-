<?php

namespace VoxSocioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VoxSocioBundle:Default:index.html.twig');
    }
}
