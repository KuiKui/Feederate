<?php

namespace Feederate\FeederateBundle\Importer;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\UserFeed;
use Feederate\FeederateBundle\Parser\FeedParser;

class FeedbinImporter
{
    protected $securityContext;

    protected $mananger;

    protected $errors = array();

    protected $parsed = 0;

    /**
     * Constructor
     * 
     * @param EntityManager $manager
     */
    public function __construct($securityContext, $manager)
    {
        $this->securityContext = $securityContext;
        $this->manager         = $manager;
    }

    /**
     * Set securityContext
     * 
     * @return this
     */
    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;

        return $this;
    }

    /**
     * Get securityContext
     * 
     * @return SecurityContext
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * Set manager
     * 
     * @return this
     */
    public function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     * 
     * @return EntityManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Increment number of parsed feed
     * 
     * @return this
     */
    protected function incrementParsed()
    {
        $this->parsed++;

        return $this;
    }

    /**
     * Get number of parsed feed
     * 
     * @return integer
     */
    public function getParsed()
    {
        return $this->parsed;
    }

    /**
     * Add error
     * 
     * @param string $message
     * 
     * @return this
     */
    protected function addError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * Get errors
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Import file
     * 
     * @return void
     */
    public function import($file)
    {
        $feedsDataXml = simplexml_load_file($file);

        foreach ($feedsDataXml->body->outline as $feedXml) {
            $feed = $this->getManager()
                ->getRepository('FeederateFeederateBundle:Feed')
                ->findOneBy(['url' => $feedXml->attributes()->xmlUrl]);

            if (!$feed) {
                $feed = new Feed();
                $feed
                    ->setTitle($feedXml->attributes()->title ?: $feedXml->attributes()->htmlUrl)
                    ->setUrl($feedXml->attributes()->xmlUrl)
                    ->setTargetUrl($feedXml->attributes()->htmlUrl);
            }

            $userFeed = new UserFeed();
            $userFeed
                ->setFeed($feed)
                ->setUser($this->getSecurityContext()->getToken()->getUser());

            if (!$feed->getId()) {
                if ($this->parseFeed($feed)) {
                    $this->getManager()->persist($feed);
                    $this->getManager()->persist($userFeed);
                    $this->getManager()->flush();
                    $this->incrementParsed();
                } else {
                    $this->addError(sprintf("It's not possible to parse the feed %s", $feed->getTitle()));
                }
            }
        }
    }

    /**
     * Parse feed
     * 
     * @param Feed $feed
     * 
     * @return void
     */
    protected function parseFeed(Feed $feed)
    {
        try {
            $feedParser = new FeedParser($feed, $this->getManager());
            $feedParser->parse();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
