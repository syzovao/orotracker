<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Strategy\Import;

use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\ImportExport\Strategy\Import\IssueImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class AddOrReplaceStrategy implements StrategyInterface, ContextAwareInterface
{
    /**
     * @var ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @var IssueImportStrategyHelper
     */
    protected $issueStrategyHelper;

    /**
     * @var ContextInterface
     */
    protected $importExportContext;

    /**
     * @param ImportStrategyHelper $strategyHelper
     * @param IssueImportStrategyHelper $issueStrategyHelper
     */
    public function __construct(
        ImportStrategyHelper $strategyHelper,
        IssueImportStrategyHelper $issueStrategyHelper
    ) {
        $this->strategyHelper = $strategyHelper;
        $this->issueStrategyHelper = $issueStrategyHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity)
    {
        if (!$this->importExportContext) {
            throw new LogicException('Strategy must have import/export context');
        }

        if (!$entity instanceof Issue) {
            throw new InvalidArgumentException('Imported entity must be instance of Issue');
        }

        // try to find existing issue by ID, if it exists - replace all data with data from imported issue
        $entity = $this->findAndReplaceIssue($entity);

        // update all related entities
        $this
            ->updateOwner($entity)
            ->updateAssignee($entity)
            ->updateReporter($entity);

        $this->updateOrganization($entity);

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function setImportExportContext(ContextInterface $importExportContext)
    {
        $this->importExportContext = $importExportContext;
    }

    /**
     * @param Issue $issue
     * @return null|Issue
     */
    protected function validateAndUpdateContext(Issue $issue)
    {
        // validate issue
        $validationErrors = $this->strategyHelper->validateEntity($issue);
        if ($validationErrors) {
            $this->importExportContext->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->importExportContext);
            return null;
        }

        // increment context counter
        if ($issue->getId()) {
            $this->importExportContext->incrementReplaceCount();
        } else {
            $this->importExportContext->incrementAddCount();
        }

        return $issue;
    }

    /**
     * @param Issue $issue
     * @return AddOrReplaceStrategy
     */
    protected function updateOrganization(Issue $issue)
    {
        $organization = $issue->getOrganization();
        if (!$organization) {
            $currentUser = $this->issueStrategyHelper->getSecurityContextUserOrNull();
            /** @var Organization $organization */
            $organization = $currentUser->getOrganization();
            $issue->setOrganization($organization);
        }

        return $this;
    }

    /**
     * @param Issue $issue
     * @return AddOrReplaceStrategy
     */
    protected function updateReporter(Issue $issue)
    {
        $reporter = $issue->getReporter();
        if ($reporter) {
            $existingReporter = $this->issueStrategyHelper->getUserOrNull($reporter);
            $issue->setReporter($existingReporter);
        } else {
            $issue->setReporter(null);
        }

        return $this;
    }

    /**
     * @param Issue $issue
     * @return AddOrReplaceStrategy
     */
    protected function updateOwner(Issue $issue)
    {
        $owner = $issue->getOwner();
        if ($owner) {
            $existingOwner = $this->issueStrategyHelper->getUserOrNull($owner);
            $issue->setOwner($existingOwner);
        } else {
            $issue->setOwner(null);
        }

        return $this;
    }

    /**
     * @param Issue $issue
     * @return AddOrReplaceStrategy
     */
    protected function updateAssignee(Issue $issue)
    {
        $assignee = $issue->getAssignee();
        if ($assignee) {
            $existingAssignee = $this->issueStrategyHelper->getUserOrNull($assignee);
            $issue->setAssignee($existingAssignee);
        } else {
            $issue->setAssignee(null);
        }

        return $this;
    }

    /**
     * @param Issue $issue
     * @return Issue
     */
    protected function findAndReplaceIssue(Issue $issue)
    {
        $existingIssue = $this->issueStrategyHelper->getIssueOrNull($issue);
        if ($existingIssue) {
            $this->strategyHelper->importEntity($existingIssue, $issue);
            $this->updateCreatedAndUpdatedFields($existingIssue);
            $issue = $existingIssue;
        } else {
            $issue->setId(null);
        }

        return $issue;
    }

    /**
     * @param Issue $issue
     * @return AddOrReplaceStrategy
     */
    protected function updateCreatedAndUpdatedFields(Issue $issue)
    {
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $issue
            ->setCreatedAt($currentDate)
            ->setUpdatedAt($currentDate);

        return $this;
    }
}
