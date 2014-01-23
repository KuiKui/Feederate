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
use Feederate\FeederateBundle\Entity\UserEntry;
use Feederate\FeederateBundle\Model\Summary;
use Feederate\FeederateBundle\Form\UserEntryReadType;
use Feederate\FeederateBundle\Form\UserEntryStarredType;

/**
 * Class SummaryController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class SummaryController extends FOSRestController implements ClassResourceInterface
{
    use Pagination;

    /**
     * Summary list by feed
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/feeds/{feedId}/summaries",
     *     requirements={"feedId"="\d+"}
     * )
     * 
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @Rest\QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     */
    public function getFeedSummariesAction($feedId, ParamFetcher $paramFetcher)
    {
        $feed = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed')
            ->find($feedId);

        if (!$feed) {
            return $this->view(sprintf('Feed with id %s not found', $feedId), 400);
        }

        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $entries = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Entry')
            ->findBy(['feed' => $feed], ['generatedAt' => 'DESC'], $limit, $start);

        $summaries = [];

        foreach ($entries as $entry) {
            $userEntry = $this
                ->get('doctrine.orm.entity_manager')
                ->getRepository('FeederateFeederateBundle:UserEntry')
                ->findOneBy(['entry' => $entry, 'user' => $this->getUser()]);

            $summary = new Summary();
            $summary->load($entry, $userEntry);

            $summaries[] = $summary;
        }

        return $this->view($summaries, 200);
    }

    /**
     * Summary list by feed
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

        $summaries = [];

        foreach ($entries as $entry) {
            $userEntry = $this
                ->get('doctrine.orm.entity_manager')
                ->getRepository('FeederateFeederateBundle:UserEntry')
                ->findOneBy(['entry' => $entry, 'user' => $this->getUser()]);

            $summary = new Summary();
            $summary->load($entry, $userEntry);

            $summaries[] = $summary;
        }

        return $this->view($summaries, 200);
    }

    /**
     * Mark summary as read
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/summaries/{id}/read",
     *     requirements={"id"="\d+"}
     * )
     */
    public function postSummariesReadAction($id, Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');

        $entry = $manager
            ->getRepository('FeederateFeederateBundle:Entry')
            ->find($id);

        if (!$entry) {
            return $this->view(sprintf('Summary with id %s not found', $id), 404);
        }

        $userEntry = $manager
            ->getRepository('FeederateFeederateBundle:UserEntry')
            ->findOneBy(['entry' => $entry, 'user' => $this->getUser()]);

        if (!$userEntry) {
            $userEntry = new UserEntry();
            $userEntry
                ->setEntry($entry)
                ->setUser($this->getUser());
        }

        $form = $this->container->get('form.factory')->createNamed('', new UserEntryReadType(), $userEntry);

        $form->submit($request);

        if ($form->isValid()) {
            $userFeed = $manager
                ->getRepository('FeederateFeederateBundle:UserFeed')
                ->findOneBy(['feed' => $entry->getFeed(), 'user' => $this->getUser()]);
            $userFeed->decrUnreadCount();

            $manager->persist($userEntry);
            $manager->persist($userFeed);
            $manager->flush();

            $summary = new Summary();
            $summary->load($entry, $userEntry);

            return $this->view($summary, 201);
        }

        return $this->view($form, 422);
    }

    /**
     * Mark summary as starred
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/summaries/{id}/star",
     *     requirements={"id"="\d+"}
     * )
     */
    public function postSummariesStarAction($id, Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');

        $entry = $manager
            ->getRepository('FeederateFeederateBundle:Entry')
            ->find($id);

        if (!$entry) {
            return $this->view(sprintf('Summary with id %s not found', $id), 404);
        }

        $userEntry = $manager
            ->getRepository('FeederateFeederateBundle:UserEntry')
            ->findOneBy(['entry' => $entry, 'user' => $this->getUser()]);

        if (!$userEntry) {
            $userEntry = new UserEntry();
            $userEntry
                ->setEntry($entry)
                ->setUser($this->getUser());
        }

        $form = $this->container->get('form.factory')->createNamed('', new UserEntryStarredType(), $userEntry);

        $form->submit($request);

        if ($form->isValid()) {
            $manager->persist($userEntry);
            $manager->flush();

            $summary = new Summary();
            $summary->load($entry, $userEntry);

            return $this->view($summary, 201);
        }

        return $this->view($form, 422);
    }
}
