<?php

namespace Feederate\FeederateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Zend\Feed\Reader\Reader;

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
        $repository = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Feed');
        $feeds   = $repository->findBy([], ['title' => 'ASC']);

        foreach ($feeds as $feed) {
            $reader = Reader::import($feed->getUrl());
            foreach ($reader as $entry) {
                var_dump($entry);die;
            }

        }
    }
}
