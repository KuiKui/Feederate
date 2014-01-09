<?php

namespace Feederate\FeederateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Zend\Feed\Reader\Reader;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;

class ParserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('parser')
            ->setDescription('Parse and saves all feeds')
            ->addOption('feed', null, InputOption::VALUE_REQUIRED, 'feed to parse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $feedRepository = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed');

        if ($feedId = $input->getOption('feed')) {
            $feed = $feedRepository->find($feedId);

            if (!$feed) {
                throw new \Exception(sprintf("Feed with id %s does not exist", $feedId));
            }

            $this->parseByFeed($feed);
        } else {
            $feeds = $feedRepository->findBy([], ['title' => 'ASC']);
            foreach ($feeds as $feed) {
                $this->parseByFeed($feed);
            }
        }

    }

    protected function parseByFeed(Feed $feed)
    {
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');

        try {
            $newEntriesCount = 0;
            $reader          = Reader::import($feed->getUrl());

            $feed->setTitle($reader->getTitle());

            $manager->persist($feed);
            $manager->flush();

            foreach ($reader as $entryReader) {
                $entry = $manager->getRepository('FeederateFeederateBundle:Entry')
                    ->findOneBy(['generatedId' => $entryReader->getId(), 'feed' => $feed]);

                if (!$entry) {
                    $entry = new Entry();
                    $entry
                        ->setGeneratedId($entryReader->getId())
                        ->setFeed($feed);
                    $newEntriesCount++;
                }

                $entry
                    ->setGeneratedAt($entryReader->getDateCreated())
                    ->setTitle($entryReader->getTitle())
                    ->setTargetUrl($entryReader->getLink())
                    ->setDescription(strip_tags($entryReader->getDescription()))
                    ->setContent($entryReader->getContent());

                $manager->persist($entry);
                $manager->flush();
            }

            $userFeeds = $manager
                ->getRepository('FeederateFeederateBundle:UserFeed')
                ->findBy(['feed' => $feed]);

            foreach ($userFeeds as $userFeed) {
                $userFeed->incrUnreadCount($newEntriesCount);
                $manager->persist($userFeed);
                $manager->flush();
            }
        } catch (\Exception $e) {
            var_dump($feed->getTitle(), $e->getMessage());
        }
    }
}
