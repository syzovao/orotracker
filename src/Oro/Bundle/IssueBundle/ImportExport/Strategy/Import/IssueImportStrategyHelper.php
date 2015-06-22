<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Strategy\Import;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\IssueBundle\Entity\IssuePriority;
use Oro\Bundle\IssueBundle\Entity\IssueResolution;
use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueImportStrategyHelper
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param SecurityContextInterface $securityContext
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(SecurityContextInterface $securityContext, ManagerRegistry $managerRegistry)
    {
        $this->securityContext = $securityContext;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        return $this->managerRegistry->getRepository($entityName);
    }

    /**
     * @param User $user
     * @return User|null
     */
    public function getUserOrNull(User $user)
    {
        $existingUser = null;
        $username = $user->getUsername();

        if ($username) {
            $existingUser = $this->getEntityRepository('OroUserBundle:User')->findOneBy(
                array('username' => $username)
            );
        }

        return $existingUser ? : null;
    }

    /**
     * @param Issue $issue
     * @return Issue|null
     */
    public function getIssueOrNull(Issue $issue)
    {
        $existingIssue = null;
        $issueId = $issue->getId();
        if ($issueId) {
            $existingIssue = $this->getEntityRepository('OroIssueBundle:Issue')->find($issueId);
        }

        return $existingIssue ? : null;
    }

    /**
     * @return User|null
     */
    public function getSecurityContextUserOrNull()
    {
        $token = $this->securityContext->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user) {
            return null;
        }

        return $this->getEntityRepository('OroUserBundle:User')->find($user->getId());
    }
}
