<?php

namespace Oro\Bundle\IssueBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Validator\Constraints\IssueParent;


class IssueParentValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @param string|array $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        // @todo: change logic after fox validator
        if ($value != Issue::TYPE_SUBTASK) {
            /** @var IssueParent $constraint */
            $this->context->addViolation($constraint->message);
        }
    }
}
