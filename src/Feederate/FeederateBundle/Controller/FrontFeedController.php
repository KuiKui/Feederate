<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FrontFeedController extends Controller
{
    /**
     * Index action
     *
     * @Template()
     * @Route("/", name="feedapp")
     *
     * @return array
     */
    public function indexAction()
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Feed');
        $entities   = $repository->findBy([], ['id' => 'DESC']);

        return array('feeds' => $entities);
    }
}
