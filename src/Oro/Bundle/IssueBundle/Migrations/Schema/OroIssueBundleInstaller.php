<?php

namespace Oro\Bundle\IssueBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroIssueBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroIssueTable($schema);
        $this->createOroIssueCollaboratorsTable($schema);
        $this->createOroIssuePriorityTable($schema);
        $this->createOroIssueRelatedTable($schema);
        $this->createOroIssueResolutionTable($schema);

        /** Foreign keys generation **/
        $this->addOroIssueForeignKeys($schema);
        $this->addOroIssueCollaboratorsForeignKeys($schema);
        $this->addOroIssueRelatedForeignKeys($schema);
    }

    /**
     * Create oro_issue table
     *
     * @param Schema $schema
     */
    protected function createOroIssueTable(Schema $schema)
    {
        $table = $schema->createTable('oro_issue');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('assignee_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('priority_code', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('resolution_code', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('reporter_id', 'integer', ['notnull' => false]);
        $table->addColumn('code', 'string', ['length' => 20]);
        $table->addColumn('summary', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', []);
        $table->addColumn('issue_type', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['priority_code']);
        $table->addIndex(['resolution_code']);
        $table->addIndex(['reporter_id']);
        $table->addIndex(['assignee_id']);
        $table->addIndex(['parent_id']);
        $table->addIndex(['organization_id']);
        $table->addIndex(['user_owner_id']);
    }

    /**
     * Create oro_issue_collaborators table
     *
     * @param Schema $schema
     */
    protected function createOroIssueCollaboratorsTable(Schema $schema)
    {
        $table = $schema->createTable('oro_issue_collaborators');
        $table->addColumn('issue_id', 'integer', []);
        $table->addColumn('user_id', 'integer', []);
        $table->setPrimaryKey(['issue_id', 'user_id']);
        $table->addIndex(['issue_id']);
        $table->addIndex(['user_id']);
    }

    /**
     * Create oro_issue_priority table
     *
     * @param Schema $schema
     */
    protected function createOroIssuePriorityTable(Schema $schema)
    {
        $table = $schema->createTable('oro_issue_priority');
        $table->addColumn('code', 'string', ['length' => 50]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('priority', 'integer', []);
        $table->setPrimaryKey(['code']);
    }

    /**
     * Create oro_issue_related table
     *
     * @param Schema $schema
     */
    protected function createOroIssueRelatedTable(Schema $schema)
    {
        $table = $schema->createTable('oro_issue_related');
        $table->addColumn('issue_id', 'integer', []);
        $table->addColumn('related_id', 'integer', []);
        $table->setPrimaryKey(['issue_id', 'related_id']);
        $table->addIndex(['issue_id']);
        $table->addIndex(['related_id']);
    }

    /**
     * Create oro_issue_resolution table
     *
     * @param Schema $schema
     */
    protected function createOroIssueResolutionTable(Schema $schema)
    {
        $table = $schema->createTable('oro_issue_resolution');
        $table->addColumn('code', 'string', ['length' => 50]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('priority', 'integer', []);
        $table->setPrimaryKey(['code']);
    }

    /**
     * Add oro_issue foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroIssueForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_issue');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assignee_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue_priority'),
            ['priority_code'],
            ['code'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue'),
            ['parent_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue_resolution'),
            ['resolution_code'],
            ['code'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['reporter_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_issue_collaborators foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroIssueCollaboratorsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_issue_collaborators');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue'),
            ['issue_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_issue_related foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroIssueRelatedForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_issue_related');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue'),
            ['related_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue'),
            ['issue_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
