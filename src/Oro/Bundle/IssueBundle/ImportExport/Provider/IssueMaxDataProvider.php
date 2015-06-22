<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class IssueMaxDataProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilderPrototype;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Set QueryBuilder that will be used in calculations
     *
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $prototypeQueryBuilder = clone $queryBuilder;
        $prototypeQueryBuilder->resetDQLParts(array('groupBy', 'having', 'orderBy'));

        $this->queryBuilderPrototype = $prototypeQueryBuilder;
    }

    /**
     * Clone prototype of QueryBuilder and return it
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        if (!$this->queryBuilderPrototype) {
            /** @var EntityRepository $repository */
            $repository = $this->managerRegistry->getRepository('OroIssueBundle:Issue');
            $this->queryBuilderPrototype = $repository->createQueryBuilder('issue');
        }

        return clone $this->queryBuilderPrototype;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     * @throws \LogicException
     */
    protected function getRootAlias(QueryBuilder $queryBuilder)
    {
        $issueAliases = $queryBuilder->getRootAliases();
        if (empty($issueAliases)) {
            throw new \LogicException('Max data query builder must have root alias');
        }

        return current($issueAliases);
    }

    /**
     * Generate DQL to calculate maximum number of specified entities
     *
     * @param string $entityName
     * @param string $entityIdentifier
     * @return int
     */
    protected function getMaxEntitiesCount($entityName, $entityIdentifier = 'id')
    {
        $queryBuilder = $this->getQueryBuilder();
        $issueAlias = $this->getRootAlias($queryBuilder);

        $queryBuilder
            ->select("COUNT(joinedEntity.$entityIdentifier) as maxCount")
            ->join("$issueAlias.$entityName", 'joinedEntity')
            ->groupBy("$issueAlias.id")
            ->orderBy('maxCount', 'DESC')
            ->setMaxResults(1);

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return !empty($result['maxCount']) ? (int)$result['maxCount'] : 0;
    }
}
