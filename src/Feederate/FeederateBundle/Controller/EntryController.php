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
        $entities = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Entry')
            ->findBy([], ['generatedAt' => 'DESC']);

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
        $entity = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Entry')
            ->find($id);

        if (!$entity) {
            return $this->view(sprintf('Entry with id %s not found', $id), 404);
        }

        return $this->view($entity, 200);
    }

    /**
     * Entry list by feed
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/feeds/{feedId}/entries",
     *     requirements={"feedId"="\d+"}
     * )
     */
    public function getFeedEntriesAction($feedId)
    {
        $feed = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed')
            ->find($feedId);

        if (!$feed) {
            return $this->view(sprintf('Feed with id %s not found', $feedId), 400);
        }

        $entities = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Entry')
            ->findBy(['feed' => $feed], ['generatedAt' => 'DESC']);

        return $this->view($entities, 200);
    }
}
