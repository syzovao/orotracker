<?php

namespace Oro\Bundle\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Issue
 *
 * @ORM\Table(name="oro_issue")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Oro\Bundle\IssueBundle\Entity\Repository\IssueRepository")
 *
 */
class Issue
{
    const TYPE_BUG     = 'bug';
    const TYPE_TASK    = 'task';
    const TYPE_STORY   = 'story';
    const TYPE_SUBTASK = 'subtask';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=255)
     */
    protected $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="issue_type", type="string", length=255)
     */
    protected $issueType;

    /**
     * @var IssuePriority
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IssueBundle\Entity\IssuePriority")
     * @ORM\JoinColumn(name="issue_priority_code", referencedColumnName="code", onDelete="SET NULL")
     */
    protected $issuePriority;

    /**
     * @var IssueResolution
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IssueBundle\Entity\IssueResolution")
     * @ORM\JoinColumn(name="issue_resolution_code", referencedColumnName="code", onDelete="SET NULL")
     */
    protected $issueResolution;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reporter_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reporter;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assignee_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assignee;

    /**
     * @var ArrayCollection User[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(name="oro_issue_collaborators",
     *      joinColumns={@ORM\JoinColumn(name="issue_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     **/
    protected $collaborators;

    /**
     * @var ArrayCollection Issue[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\IssueBundle\Entity\Issue")
     * @ORM\JoinTable(name="oro_issue_related",
     *      joinColumns={@ORM\JoinColumn(name="issue_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="related_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $relatedIssues;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Issue", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $parent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="parent")
     **/
    protected $children;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children      = new ArrayCollection();
        $this->collaborators = new ArrayCollection();
        $this->relatedIssues = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Issue
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
     * Set summary
     *
     * @param string $summary
     *
     * @return Issue
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Issue
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set reporter
     *
     * @param string $reporter
     *
     * @return Issue
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * Get reporter
     *
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Set assignee
     *
     * @param User $assignee
     * @return Issue
     */
    public function setAssignee($assignee)
    {
        $this->assignee = $assignee;

        return $this;
    }

    /**
     * Get assignee
     *
     * @return User
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * Set collaborators
     *
     * @param string $collaborators
     *
     * @return Issue
     */
    public function setCollaborators($collaborators)
    {
        $this->collaborators = $collaborators;

        return $this;
    }

    /**
     * Get collaborators
     *
     * @return ArrayCollection
     */
    public function getCollaborators()
    {
        return $this->collaborators;
    }

    /**
     * Add collaborators
     *
     * @param User $user
     *
     * @return Issue
     */
    public function addCollaborator(User $user)
    {
        if (!$this->getCollaborators()->contains($user)) {
            $this->getCollaborators()->add($user);
        }
        return $this;
    }

    /**
     * Remove collaborators
     *
     * @param User $user
     */
    public function removeCollaborator(User $user)
    {
        $this->collaborators->removeElement($user);
    }

    /**
     * @ORM\PrePersist
     */
    public function beforePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->addCollaborator($this->getReporter());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function beforeSave()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set parent
     *
     * @param Issue $parent
     *
     * @return Issue
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Issue
     */
    public function getParent()
    {
        return $this->parent;
    }

    function getFullParent()
    {
        return sprintf('%s - %s', $this->code, $this->issueType);
    }

    /**
     * Set children
     *
     * @param integer $children
     *
     * @return Issue
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get children
     *
     * @return integer 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Issue
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Issue
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set issueType
     *
     * @param string $issueType
     *
     * @return Issue
     */
    public function setIssueType($issueType)
    {
        $this->issueType = $issueType;

        return $this;
    }

    /**
     * Get IssueType
     *
     * @return string
     */
    public function getIssueType()
    {
        return $this->issueType;
    }

    /**
     * Set IssuePriority
     *
     * @param IssuePriority $issuePriority
     *
     * @return Issue
     */
    public function setIssuePriority($issuePriority)
    {
        $this->issuePriority = $issuePriority;

        return $this;
    }

    /**
     * Get IssuePriority
     *
     * @return IssuePriority
     */
    public function getIssuePriority()
    {
        return $this->issuePriority;
    }

    /**
     * Set IssueResolution
     *
     * @param IssueResolution $issueResolution
     *
     * @return IssueResolution
     */
    public function setIssueResolution($issueResolution)
    {
        $this->issueResolution = $issueResolution;

        return $this;
    }

    /**
     * Get IssueResolution
     *
     * @return IssueResolution
     */
    public function getIssueResolution()
    {
        return $this->issueResolution;
    }

    /**
     * Get if issue has children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->children->count() == 0 ? false : true;
    }

    /**
     * Add children
     *
     * @param Issue $children
     *
     * @return Issue
     */
    public function addChild(Issue $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Issue $children
     */
    public function removeChild(Issue $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get string value
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->code;
    }
}
