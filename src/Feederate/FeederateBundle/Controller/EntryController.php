<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Feederate\ControllerExtraBundle\Traits\Pagination;
use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;

/**
 * Class EntryController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class EntryController extends FOSRestController implements ClassResourceInterface
{
    use Pagination;

    /**
     * Entry list
     * 
     * @param ParamFetcher $paramFetcher Param Fetcher
     *
     * @return \FOS\RestBundle\View\View
     * 
     * @Rest\QueryParam(name="type", requirements="(starred|unread)", nullable=false, default="starred", strict=true, description="Summaries type")
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @Rest\QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $entries = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Entry')
            ->findByUserAndType($this->getUser(), $paramFetcher->get('type'), [], ['generatedAt' => 'DESC'], $limit, $start);

        return $this->view($entries, 200);
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
     * 
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @Rest\QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     */
    public function getFeedEntriesAction($feedId, ParamFetcher $paramFetcher)
    {
        $feed = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed')
            ->find($feedId);

        if (!$feed) {
            return $this->view(sprintf('Feed with id %s not found', $feedId), 400);
        }

        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $entities = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Entry')
            ->findBy(['feed' => $feed], ['generatedAt' => 'DESC'], $limit, $start);

        return $this->view($entities, 200);
    }
}
