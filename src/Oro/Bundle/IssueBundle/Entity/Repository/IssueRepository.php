<?php
namespace Oro\Bundle\IssueBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueRepository extends EntityRepository
{
    /**
     * Find parent story issue
     *
     * @param int $id
     *
     * @return Issue
     */
    public function findParentStory($id)
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->select('i')
            ->where('i.issueType = "story"')
            ->where('i.id = :id')
            ->setParameter('id', $id);
        return $queryBuilder->getQuery()->getSingleResult();
    }

}
