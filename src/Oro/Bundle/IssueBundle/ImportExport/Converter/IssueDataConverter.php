<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Converter;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;
use Oro\Bundle\ImportExportBundle\Converter\QueryBuilderAwareInterface;
use Oro\Bundle\IssueBundle\ImportExport\Provider\IssueHeaderProvider;

class IssueDataConverter extends AbstractTableDataConverter implements QueryBuilderAwareInterface
{
    /**
     * @var IssueHeaderProvider
     */
    protected $headerProvider;

    /**
     * @var array
     */
    protected $headerConversionRules = array(
        // plain fields
        'ID'                => 'id',
        'Code'              => 'code',
        'Summary'           => 'summary',
        'Type'              => 'issueType',
        'Description'       => 'description',
        'Priority'          => 'priority',
        'Resolution'        => 'resolution',
        'Step'              => 'step',
        'Organization'      => 'organization',
        'Owner Username'    => 'owner:username',
        'Assignee Username' => 'assignee:username',
        'Reporter Username' => 'reporter:username',
    );

    /**
     * @param IssueHeaderProvider $headerProvider
     */
    public function __construct(IssueHeaderProvider $headerProvider)
    {
        $this->headerProvider = $headerProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->headerProvider->setQueryBuilder($queryBuilder);
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        $complexConversionRules = array();

        return array_merge($this->headerConversionRules, $complexConversionRules);
    }

    /**
     * {@inheritDoc}
     */
    protected function getBackendHeader()
    {
        return $this->headerProvider->getHeader();
    }
}
