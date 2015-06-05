<?php

namespace Oro\Bundle\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssuePriority
 *
 * @ORM\Table(name="oro_issue_priority")
 * @ORM\Entity
 */
class IssuePriority
{
    const PRIORITY_TRIVIAL  = 'trivial';
    const PRIORITY_MINOR    = 'minor';
    const PRIORITY_MAJOR    = 'major';
    const PRIORITY_CRITICAL = 'critical';
    const PRIORITY_BLOCKER  = 'blocker';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=false)
     * @ORM\Id
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    protected $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     */
    protected $priority;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return IssuePriority
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set Label
     *
     * @param string $label
     *
     * @return IssuePriority
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get Label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return IssuePriority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get string value of Priority label
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->label;
    }
}
