<?php

namespace Feederate\FeederateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Zend\Feed\Reader\Reader;

use Feederate\FeederateBundle\Entity\Entry;

class ParserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('parser')
            ->setDescription('Parse and saves all feeds');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager    = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('FeederateFeederateBundle:Feed');
        $feeds      = $repository->findBy([], ['title' => 'ASC']);

        foreach ($feeds as $feed) {
            try {
                $reader = Reader::import($feed->getUrl());
                foreach ($reader as $entryReader) {
                    $entry = $manager->getRepository('FeederateFeederateBundle:Entry')
                        ->findOneBy(['generatedId' => $entryReader->getId(), 'feed' => $feed]);

                    if (!$entry) {
                        $entry = new Entry();
                        $entry
                            ->setGeneratedId($entryReader->getId())
                            ->setFeed($feed);
                    }

                    $entry
                        ->setGeneratedAt($entryReader->getDateCreated())
                        ->setTitle($entryReader->getTitle())
                        ->setDescription($entryReader->getDescription())
                        ->setTargetUrl($entryReader->getLink())
                        ->setContent($entryReader->getContent());

                    $manager->persist($entry);
                    $manager->flush();
                }
            } catch (\Exception $e) {
                var_dump($feed->getTitle(), $e->getMessage());
            }
        }
    }
}
