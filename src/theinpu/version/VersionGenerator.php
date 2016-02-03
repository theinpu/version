<?php

namespace theinpu\version;

use Gitonomy\Git\Commit;
use Gitonomy\Git\Repository;

/**
 * Class VersionGenerator
 * Generate version number based on time, passed from repository starts
 * @package theinpu\version
 */
class VersionGenerator {

    /**
     * @var Repository
     */
    private $repo;

    /**
     * VersionGenerator constructor.
     *
     * @param string $path path to git repository
     */
    public function __construct($path) {
        $this->repo = new Repository($path);
    }

    /**
     * Generate version string
     *
     * @param int $forthNumber optional, for x.x.x.x version
     * @param string $tag      tag for version, x.x.x.x-rc1 etc.
     *
     * @return string
     */
    public function version($forthNumber = null, $tag = "") {
        $commits = $this->repo->getLog()->getCommits();
        $count = count($commits);
        /** @var Commit $firstCommit */
        $firstCommit = $commits[$count - 1];
        /** @var \DateTime $startDate */
        $startDate = $firstCommit->getAuthorDate();
        $fromStart = $startDate->diff(new \DateTime('now'), true);

        $version = [0, 0, 0];
        $version[0] = $fromStart->y;
        $version[1] = $fromStart->m;
        $version[2] = $fromStart->d;
        if(!is_null($forthNumber)) {
            $version[] = $forthNumber;
        }

        $versionString = implode('.', $version);

        if(!empty($tag)) {
            $versionString .= '-'.$tag;
        }

        return $versionString;
    }

    /**
     * Mark commit by version tag
     *
     * @param string $hash     commit id
     * @param int $forthNumber @see VersionGenerator::version
     * @param string $tag
     */
    public function markCommit($hash, $forthNumber = null, $tag = "") {
        $version = 'v'.$this->version($forthNumber, $tag);
        $this->repo->getReferences()->createTag($version, $hash);
        $this->repo->run('push', array('origin', $version));
    }
}