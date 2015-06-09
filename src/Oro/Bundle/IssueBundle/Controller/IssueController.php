<?php

namespace Oro\Bundle\IssueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Entity\IssuePriority;
use Oro\Bundle\IssueBundle\Entity\IssueResolution;
use Oro\Bundle\IssueBundle\Form\Type\IssueType;


class IssueController extends Controller
{
    /**
     * @Route(name="oro_issue_index")
     * @Acl(
     *      id="oro_issue_view",
     *      type="entity",
     *      class="OroIssueBundle:Issue",
     *      permission="VIEW"
     * )
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'entity_class' => $this->container->getParameter('oro_issue.issue.entity.class')
        );
    }

    /**
     * Create business_unit form
     *
     * @Route("/create", name="oro_issue_create")
     * @Template("OroIssueBundle:Issue:update.html.twig")
     * @Acl(
     *      id="oro_issue_create",
     *      type="entity",
     *      class="OroIssueBundle:Issue",
     *      permission="CREATE"
     * )
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function createAction()
    {
        $userId = $this->getRequest()->get('userId');

        /** @var Issue $entity */
        $entity = new Issue();

        if ($userId) {
            $user = $this->getDoctrine()->getRepository('OroUserBundle:User')->find($userId);
            if (!$user) {
                throw new NotFoundHttpException(sprintf('User with ID %s is not found', $user));
            }
            $entity
                ->setAssignee($user)
                ->setReporter($user);
        } elseif ($reporter = $this->getUser()) {
            $entity->setReporter($reporter);
        }

        $issuePriority = $this->getDoctrine()
            ->getRepository('OroIssueBundle:IssuePriority')
            ->findOneByCode(IssuePriority::PRIORITY_MAJOR);
        $entity->getPriority($issuePriority);
        $issueResolution = $this->getDoctrine()
            ->getRepository('OroIssueBundle:IssueResolution')
            ->findOneByCode(IssueResolution::RESOLUTION_UNRESOLVED);
        $entity->getResolution($issueResolution);
        $entity->setIssueType(Issue::TYPE_TASK);

        return $this->update($entity);
    }

    /**
     * @Route("/view/{id}", name="oro_issue_view", requirements={"id"="\d+"})
     * @AclAncestor("oro_issue_view")
     * @Template
     *
     * @param Issue $entity
     * @return array
     */
    public function viewAction(Issue $entity)
    {
        return array('entity' => $entity);
    }

    /**
     * Update action
     *
     * @Route("/update/{id}", name="oro_issue_update", requirements={"id"="\d+"})
     * @Template()
     * @Acl(
     *      id="oro_issue_update",
     *      type="entity",
     *      class="OroIssueBundle:Issue",
     *      permission="EDIT"
     * )
     *
     * @param Issue $entity
     *
     * @return array
     */
    public function updateAction(Issue $entity)
    {
        return $this->update($entity);
    }

    /**
     * Update issue entity
     *
     * @param Issue $entity
     *
     * @return array
     */
    protected function update(Issue $entity)
    {
        $saved = false;
        $form = $this->createForm($this->getFormType(), $entity);
        if ($this->get('oro_issue.form.handler.issue')->process($entity)) {
            $saved = true;
            if (!$this->getRequest()->request->has('_widgetContainer')) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.issue.controller.issue.saved.message')
                );

                return $this->get('oro_ui.router')->redirectAfterSave(
                    ['route' => 'oro_issue_update', 'parameters' => ['id' => $entity->getId()]],
                    ['route' => 'oro_issue_index'],
                    $entity
                );
            }
        }

        return array(
            'saved'  => $saved,
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * @return IssueType
     */
    protected function getFormType()
    {
        return $this->get('oro_issue.form.type.issue');
    }
}
