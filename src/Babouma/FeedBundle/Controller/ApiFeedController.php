<?php

namespace Babouma\FeedBundle\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class FeedController
 *
 * @package Babouma\FeedBundle\Controller
 */
class ApiFeedController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Feed list
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('BaboumaFeedBundle:Feed');
        $entities   = $repository->findBy([], ['id' => 'DESC']);

        return $this->view($entities, 200);
    }
}
