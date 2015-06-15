<?php

namespace Oro\Bundle\IssueBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IssueParent extends Constraint
{
    public $message = 'issue.validators.parent_only_for_subtask';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_issue.issue_parent_validator';
    }
}
