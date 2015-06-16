<?php

namespace Oro\Bundle\IssueBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\UserBundle\Entity\User;


class IssueListener
{
    /** @var ClassMetadata[] */
    protected $metadataLocalCache = array();

    /** @var ServiceLink */
    protected $securityFacadeLink;

    /**
     * @param ServiceLink $securityFacadeLink
     */
    public function __construct(ServiceLink $securityFacadeLink)
    {
        $this->securityFacadeLink = $securityFacadeLink;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var Issue $entity */
        $entity = $args->getEntity();
        if (!$this->isIssueEntity($entity)) {
            return;
        }

        /** @var User $user */
        $user = $this->getUser($args->getEntityManager());

        //add reporter as collaborator
        $entity->setReporter($user);
        $entity->addCollaborator($user);

        //add assignee as collaborator
        $entity->addCollaborator($entity->getAssignee());
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager  = $args->getEntityManager();
        $unitOfWork     = $entityManager->getUnitOfWork();

        $entities = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            /** @var Issue $entity */
            if (!$this->isIssueEntity($entity)) {
                continue;
            }

            $entity->addCollaborator($entity->getAssignee());
            $meta = $this->getClassMetadata($entity, $entityManager);
            $unitOfWork->computeChangeSet($meta, $entity);
        }
    }

    /**
     * @param mixed $entity
     *
     * @return bool
     */
    protected function isIssueEntity($entity)
    {
        return $entity instanceof Issue;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return User|null
     */
    protected function getUser(EntityManager $entityManager)
    {
        /** @var User $user */
        $user = $this->securityFacadeLink->getService()->getLoggedUser();
        if ($user && $entityManager->getUnitOfWork()->getEntityState($user) == UnitOfWork::STATE_DETACHED) {
            $user = $entityManager->find('OroUserBundle:User', $user->getId());
        }

        return $user;
    }

    /**
     * @param object        $entity
     * @param EntityManager $entityManager
     *
     * @return ClassMetadata
     */
    protected function getClassMetadata($entity, EntityManager $entityManager)
    {
        $className = ClassUtils::getClass($entity);
        if (!isset($this->metadataLocalCache[$className])) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->metadataLocalCache[$className] = $entityManager->getClassMetadata($className);
        }

        return $this->metadataLocalCache[$className];
    }
}
