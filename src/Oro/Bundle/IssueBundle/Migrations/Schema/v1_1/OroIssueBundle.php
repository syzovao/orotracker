<?php

namespace Oro\Bundle\IssueBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroIssueBundle implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_issue');
        $table->addColumn('workflowItem_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflowStep_id', 'integer', ['notnull' => false]);
        $table->addIndex(['workflowItem_id']);
        $table->addIndex(['workflowStep_id']);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflowStep_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflowItem_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
