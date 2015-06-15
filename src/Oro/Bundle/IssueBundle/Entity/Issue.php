<?php

namespace Oro\Bundle\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContextInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\IssueBundle\Model\ExtendIssue;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;


/**
 * Issue
 *
 * @ORM\Table(name="oro_issue")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Oro\Bundle\IssueBundle\Entity\Repository\IssueRepository")
 * @Config(
 *      routeName="oro_issue_index",
 *      routeView="oro_issue_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-list-alt"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "workflow"={
 *              "active_workflow"="oro_issue_workflow"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          }
 *      }
 * )
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 *
 */
class Issue extends ExtendIssue implements Taggable
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
     * @var IssuePriority
     *
     * @ORM\ManyToOne(targetEntity="IssuePriority")
     * @ORM\JoinColumn(name="priority_code", referencedColumnName="code", onDelete="SET NULL")
     */
    protected $priority;

    /**
     * @var IssueResolution
     *
     * @ORM\ManyToOne(targetEntity="IssueResolution")
     * @ORM\JoinColumn(name="resolution_code", referencedColumnName="code", onDelete="SET NULL")
     */
    protected $resolution;

    /**
     * @var string
     *
     * @ORM\Column(name="issue_type", type="string", length=255)
     */
    protected $issueType;

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
     */
    protected $parent;

    /**
     * @var ArrayCollection Issue[]
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="parent")
     */
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var ArrayCollection $tags
     */
    protected $tags;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflowItem_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowIssueItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflowStep_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowIssueStep;

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
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return (string) $this->code;
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
     * @param IssuePriority $priority
     *
     * @return Issue
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get IssuePriority
     *
     * @return IssuePriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set IssueResolution
     *
     * @param IssueResolution $resolution
     *
     * @return IssueResolution
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get IssueResolution
     *
     * @return IssueResolution
     */
    public function getResolution()
    {
        return $this->resolution;
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
     * Get string value
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->code;
    }

    /**
     * Get relatedIssues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedIssues()
    {
        return $this->relatedIssues;
    }

    /**
     * Set owner
     *
     * @param User $owner
     *
     * @return Issue
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
     * @return Issue
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get TaggableId
     *
     * @return int
     */
    public function getTaggableId()
    {
        return $this->getId();
    }


    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        $this->tags = $this->tags ? : new ArrayCollection();

        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     *
     * @return Issue $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param WorkflowItem $workflowItem
     * @return Issue
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowIssueItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowIssueItem;
    }

    /**
     * @param WorkflowItem $workflowStep
     * @return Issue
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowIssueStep = $workflowStep;

        return $this;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowIssueStep;
    }
}
