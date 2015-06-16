<?php

namespace Oro\Bundle\IssueBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\NoteBundle\Entity\Note;


class NoteListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->isNoteEntity($entity)) {
            return;
        }

        /** @var Note $entity */
        $target = $entity->getTarget();
        if (!$this->isIssueEntity($target)) {
            return;
        }
        /** @var Issue $target */
        $newUpdatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $target->setUpdatedAt($newUpdatedAt);
    }

    /**
     * @param mixed $entity
     *
     * @return bool
     */
    protected function isNoteEntity($entity)
    {
        return $entity instanceof Note;
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
}
