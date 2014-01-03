<?php

namespace Babouma\FeedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BaboumaFeedBundle:Default:index.html.twig', array('name' => $name));
    }
}
