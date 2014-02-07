<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\UserFeed;
use Feederate\FeederateBundle\Entity\UserEntry;
use Feederate\FeederateBundle\Model\Feed as FeedModel;
use Feederate\FeederateBundle\Form\FeedType;
use Feederate\FeederateBundle\Form\FeedReadType;
use Feederate\FeederateBundle\Parser\FeedParser;

/**
 * Class FeedController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class FeedController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Feed list
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $feeds = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed')
            ->findByUser($this->getUser(), [], ['title' => 'ASC']);

        $feeds = $this->getFeedResources($feeds);

        $unreadCount = 0;
        foreach ($feeds as $feed) {
            $unreadCount += $feed->getUnreadCount();
        }

        $starredEntries = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:UserEntry')
            ->findBy(['user' => $this->getUser(), 'isStarred' => true]);

        $starred = new FeedModel();
        $starred
            ->setId('starred')
            ->setTitle('Starred')
            ->setUnreadCount(count($starredEntries));

        array_unshift($feeds, $starred);

        $unread = new FeedModel();
        $unread
            ->setId('unread')
            ->setTitle('Unread')
            ->setUnreadCount($unreadCount);

        array_unshift($feeds, $unread);

        return $this->view($feeds, 200);
    }

    /**
     * Feed list
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        $entity = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed')
            ->findByUser($this->getUser(), ['id' => $id]);

        if (!$entity) {
            return $this->view(sprintf('Feed with id %s not found', $id), 404);
        }

        return $this->view($this->getFeedResources($entity), 200);
    }

    /**
     * Feed post
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction(Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $entity  = new Feed();
        $form    = $this->container->get('form.factory')->createNamed('', new FeedType(), $entity);

        $form->submit($request);

        if ($form->isValid()) {
            $manager->persist($entity);

            $userFeed = new UserFeed();
            $userFeed
                ->setFeed($entity)
                ->setUser($this->getUser());

            $manager->persist($userFeed);
            $manager->flush();

            return $this->view($this->getFeedResources($entity), 201, array(
                'Location' => $this->generateUrl('get_feed', ['id' => $entity->getId()], true),
            ));
        }

        return $this->view($form, 422);
    }

    /**
     * Delete feed
     *
     * @param integer $id Feed id
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(requirements={"id"="\d+"})
     *
     */
    public function deleteAction($id)
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $feed    = $manager
            ->getRepository('FeederateFeederateBundle:Feed')
            ->findByUser($this->getUser(), ['id' => $id]);

        if (!$feed) {
            return $this->view(sprintf('Feed with id %s not found', $id), 404);
        }

        $userFeed = $manager
            ->getRepository('FeederateFeederateBundle:UserFeed')
            ->findOneBy(['user' => $this->getUser(), 'feed' => $feed]);

        $manager->remove($userFeed);

        $userFeed = $manager
            ->getRepository('FeederateFeederateBundle:UserFeed')
            ->find(['feed' => $feed]);

        if (count($userFeed) === 0) {
            $feed->setUnused(true);
        }

        $manager->flush();

        return $this->view(null, 204);
    }

    /**
     * Mark feed as read
     *
     * @param integer $id      Feed id
     * @param Request $request Request
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/feeds/{id}/read",
     *     requirements={"id"="(\d+|starred|unread)"}
     * )
     *
     */
    public function postReadAction($id, Request $request)
    {
        $manager     = $this->get('doctrine.orm.entity_manager');
        $classicFeed = $id !== 'unread' && $id !== 'starred';

        if ($classicFeed) { 
            $feed = $manager
                ->getRepository('FeederateFeederateBundle:Feed')
                ->findByUser($this->getUser(), ['id' => $id]);

            if (!$feed) {
                return $this->view(sprintf('Feed with id %s not found', $id), 404);
            }
        }

        $form = $this->container->get('form.factory')->createNamed('', new FeedReadType());
        $form->submit($request);

        if ($form->isValid()) {
            $isRead = $form->getData()['is_read'];

            // Update userEntries
            if ($classicFeed) {
                $entries = $manager
                    ->getRepository('FeederateFeederateBundle:Entry')
                    ->findBy(['feed' => $feed]);
            } else {
                $entries = $manager
                    ->getRepository('FeederateFeederateBundle:Entry')
                    ->findByUserAndType($this->getUser(), $id);
            }

            foreach ($entries as $entry) {
                $userEntry = $manager
                    ->getRepository('FeederateFeederateBundle:UserEntry')
                    ->findOneBy(['user' => $this->getUser(), 'entry' => $entry]);

                if ($isRead && !$userEntry) {
                    $userEntry = new UserEntry();
                    $userEntry
                        ->setEntry($entry)
                        ->setUser($this->getUser());
                }

                if ($userEntry) {
                    $userEntry->setIsRead($isRead);
                    $manager->persist($userEntry);
                }
            }

            // Update userFeed
            if ($classicFeed) {
                $userFeed = $manager
                    ->getRepository('FeederateFeederateBundle:UserFeed')
                    ->findOneBy(['user' => $this->getUser(), 'feed' => $feed]);

                $userFeed->setUnreadCount(!$isRead ? count($entries) : 0);
                $manager->persist($userFeed);
            }

            $manager->flush();

            return $this->view('', 204);
        }

        return $this->view($form, 422);
    }

    /**
     * Parse feed
     *
     * @param integer $feedId Feed id
     * 
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/feeds/{feedId}/parse",
     *     requirements={"feedId"="\d+"}
     * )
     */
    public function getParseAction($feedId)
    {
        $manager = $this->get('doctrine.orm.entity_manager');

        $feed = $manager
            ->getRepository('FeederateFeederateBundle:Feed')
            ->findByUser($this->getUser(), ['id' => $feedId]);

        if (!$feed) {
            return $this->view(sprintf('Feed with id %s not found', $feedId), 400);
        }

        $feedParser = new FeedParser($feed, $manager);
        $feedParser
            ->setLimitEntries(20)
            ->parse();

        return $this->view("OK", 200);
    }

    /**
     * Get Feed resource
     *
     * @param mixed $feeds Feeds or one feed
     *
     * @return mixed
     */
    protected function getFeedResources($feeds) {
        if (is_array($feeds)) {
            $resources = [];
            foreach ($feeds as $feed) {
                $resources[] = $this->getFeedResources($feed);
            }

            return $resources;
        } else {
            $userFeed = $this->get('doctrine.orm.entity_manager')
                ->getRepository('FeederateFeederateBundle:UserFeed')
                ->findOneBy(['feed' => $feeds, 'user' => $this->getUser()]);

            $resource = new FeedModel();
            $resource->load($feeds, $userFeed);

            return $resource;
        }
    }
}
