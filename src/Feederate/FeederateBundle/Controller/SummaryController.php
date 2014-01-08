<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;
use Feederate\FeederateBundle\Entity\UserEntry;
use Feederate\FeederateBundle\Model\Summary;
use Feederate\FeederateBundle\Form\UserEntryType;

/**
 * Class SummaryController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class SummaryController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Summary list by feed
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/feeds/{feedId}/summaries",
     *     requirements={"feedId"="\d+"}
     * )
     */
    public function getFeedSummariesAction($feedId)
    {
        $feed = $this->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed')
            ->find($feedId);

        if (!$feed) {
            return $this->view(sprintf('Feed with id %s not found', $feedId), 400);
        }

        $entryRepository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Entry');
        $entries         = $entryRepository->findBy(['feed' => $feed], ['generatedAt' => 'DESC']);
        $user            = $this->get('security.context')->getToken()->getUser();

        $summaries = [];
        foreach ($entries as $entry) {
            $userEntryRepository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:UserEntry');
            $userEntry         = $userEntryRepository->findOneBy(['entry' => $entry, 'user' => $user]);

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
        $entry   = $manager
            ->getRepository('FeederateFeederateBundle:Entry')
            ->find($id);

        if (!$entry) {
            return $this->view(sprintf('Summary with id %s not found', $id), 404);
        }

        $user      = $this->get('security.context')->getToken()->getUser();
        $userEntry = $manager
            ->getRepository('FeederateFeederateBundle:UserEntry')
            ->findOneBy(['entry' => $entry, 'user' => $user]);

        if (!$userEntry) {
            $userEntry = new UserEntry();
            $userEntry
                ->setEntry($entry)
                ->setUser($user);
        }

        $form = $this->container->get('form.factory')->createNamed('', new UserEntryType(), $userEntry);

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
