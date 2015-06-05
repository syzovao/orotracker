<?php

namespace Oro\Bundle\UserBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IssueBundle\Entity\IssueResolution;

class LoadIssueResolutionData extends AbstractFixture implements FixtureInterface
{
    /**
     * @var array
     */
    protected $data = array(
        IssueResolution::RESOLUTION_UNRESOLVED => 'Unresolved',
        IssueResolution::RESOLUTION_DUPLICATE  => 'Duplicate',
        IssueResolution::RESOLUTION_WONTFIX    => 'Won\'t fix',
        IssueResolution::RESOLUTION_FIXED      => 'Fixed',
        IssueResolution::RESOLUTION_DONE       => 'Done'
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $i = 10;
        foreach ($this->data as $key => $value) {
            $issueResolution = new IssueResolution($key);
            $issueResolution
                ->setLabel($value)
                ->setPriority($i);
            $manager->persist($issueResolution);
            $i += 10;
        }
        $manager->flush();
    }

    /**
     * The order in which fixtures will be loaded
     * {@inheritDoc}
     *
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }
}
