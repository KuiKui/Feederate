<?php

namespace Feederate\FeederateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use Zend\Feed\Reader\Reader;
use Zend\Feed\Reader\Feed\Rss as FeedRss;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;
use Feederate\FeederateBundle\Parser\FeedParser;

class ParseCommand extends ContainerAwareCommand
{
    const LOCKFILE_DIR = '/tmp/feederate/lock';

    protected function configure()
    {
        $this
            ->setName('parse')
            ->setDescription('Parse and saves all feeds')
            ->addOption('feed', 'f', InputOption::VALUE_REQUIRED, 'feed to parse')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'limit entries to be parsed')
            ->addOption('force', null, InputOption::VALUE_NONE, 'force execution (bypass and clean lock)');
    }

    protected function getManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function getLockFilename($feedId)
    {
        $filename = sprintf("%s/%s", self::LOCKFILE_DIR, $this->getName());

        if ($feedId) {
            $filename = sprintf("%s:%s", $filename, $feedId);
        }

        return $filename;
    }

    protected function lockCommand($lockFilename)
    {
        $fs = new Filesystem();

        $directory = dirname($lockFilename);

        try {
            if (!$fs->exists($directory)) {
                $fs->mkdir($directory);
            }

            $fs->touch($lockFilename);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function unlockCommand($lockFilename)
    {
        $fs = new Filesystem();

        try {
            $fs->remove($lockFilename);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $feedRepository = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Feed');

        $feedId = $input->getOption('feed');

        $lockFilename = $this->getLockFilename($feedId);

        if ($input->getOption('force')) {
            $this->unlockCommand($lockFilename);
        }

        if (file_exists($lockFilename)) {
            $output->writeln(sprintf("<info>This command is already running (file %s)</info>", $lockFilename));

            return 0;
        }

        if (!$this->lockCommand($lockFilename)) {
            $output->writeln(sprintf("<error>File %s can't be created)</info>", $lockFilename));

            return 1;
        }

        if (!$feedId) {
            $feeds = $feedRepository->findBy([]);
        } else {
            $feeds = $feedRepository->findBy(['id' => $feedId]);

            if (!count($feeds)) {
                $this->unlockCommand($lockFilename);

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

            sleep(2);
        }

        $this->unlockCommand($lockFilename);
    }
}
