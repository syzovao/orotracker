<?php

namespace Oro\Bundle\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueResolution
 *
 * @ORM\Table(name="oro_issue_resolution")
 * @ORM\Entity
 */
class IssueResolution
{
    const RESOLUTION_UNRESOLVED = 'unresolved';
    const RESOLUTION_DUPLICATE  = 'duplicate';
    const RESOLUTION_WONTFIX    = 'wontfix';
    const RESOLUTION_FIXED      = 'fixed';
    const RESOLUTION_DONE       = 'done';

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
     * @return IssueResolution
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
     * @return IssueResolution
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
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
     * @return IssueResolution
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
     * Get string value of resolution label
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->label;
    }
}
