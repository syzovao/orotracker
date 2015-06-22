<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Provider;

use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;


class IssueHeaderProvider
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DataConverterInterface
     */
    protected $dataConverter;

    /**
     * @var IssueMaxDataProvider
     */
    protected $maxDataProvider;

    /**
     * @var array
     */
    protected $maxHeader;

    /**
     * @param SerializerInterface $serializer
     * @param DataConverterInterface $dataConverter
     * @param IssueMaxDataProvider $maxDataProvider
     */
    public function __construct(
        SerializerInterface $serializer,
        DataConverterInterface $dataConverter,
        IssueMaxDataProvider $maxDataProvider
    ) {
        $this->serializer      = $serializer;
        $this->dataConverter   = $dataConverter;
        $this->maxDataProvider = $maxDataProvider;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->maxDataProvider->setQueryBuilder($queryBuilder);
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        if (null === $this->maxHeader) {
            $issue = $this->getMaxIssueEntity();
            $complexIssueData = $this->serializer->serialize($issue, null);
            $plainIssueData = $this->dataConverter->convertToExportFormat($complexIssueData, false);
            $this->maxHeader = array_keys($plainIssueData);
        }

        return $this->maxHeader;
    }

    /**
     * @return Issue
     */
    protected function getMaxIssueEntity()
    {
        $issue = new Issue();
        $issue->setOwner(new User());
        $issue->setAssignee(new User());
        $issue->setReporter(new User());
        return $issue;
    }
}
