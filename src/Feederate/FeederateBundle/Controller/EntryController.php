<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;

/**
 * Class EntryController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class EntryController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Entry list
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Entry');
        $entities   = $repository->findBy([], ['generatedAt' => 'DESC']);

        return $this->view($entities, 200);
    }

    /**
     * Entry list
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Entry');
        $entity     = $repository->find($id);

        if (!$entity) {
            return $this->view(sprintf('Entry with id %s not found', $id), 404);
        }

        return $this->view($entity, 200);
    }
}
