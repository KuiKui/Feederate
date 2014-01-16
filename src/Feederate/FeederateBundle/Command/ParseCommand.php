<?php

namespace Feederate\FeederateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Zend\Feed\Reader\Reader;
use Zend\Feed\Reader\Feed\Rss as FeedRss;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;
use Feederate\FeederateBundle\Parser\FeedParser;

class ParseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('parse')
            ->setDescription('Parse and saves all feeds')
            ->addOption('feed', 'f', InputOption::VALUE_REQUIRED, 'feed to parse')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'limit entries to be parsed');
    }

    protected function getManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $feedRepository = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed');

        $feedId = $input->getOption('feed');

        if (!$feedId) {
            $feeds = $feedRepository->findBy([]);
        } else {
            $feeds = $feedRepository->findBy(['id' => $feedId]);

            if (!count($feeds)) {
                throw new \Exception(sprintf("Feed with id %d does not exists", $feedId));
            }
        }

        $limitEntries = (int) $input->getOption('limit');

        foreach ($feeds as $feed) {
            try {
                $feedParser = new FeedParser($feed, $this->getManager());
                $feedParser->setOutput($output);

                if ($limitEntries) {
                    $feedParser->setLimitEntries($limitEntries);
                }

                $feedParser->parse();

            } catch (\Exception $e) {
                $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
            }
        }
    }
}
