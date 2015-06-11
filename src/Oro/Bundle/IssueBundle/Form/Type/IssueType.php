<?php

namespace Oro\Bundle\IssueBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Oro\Bundle\IssueBundle\Entity\Issue;


class IssueType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', 'text',
                array(
                    'required' => true,
                    'label' => 'oro.issue.code.label',
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'attr' => array('class'=>'taggable-field')
                )
            )
            ->add('summary', 'text',
                array(
                    'required' => true,
                    'label' => 'oro.issue.summary.label',
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'attr' => array('class'=>'taggable-field')
                )
            )
            ->add('description', 'textarea',
                array(
                    'required' => false,
                    'label' => 'oro.issue.description.label',
                    'attr' => array('class'=>'taggable-field')
                )
            )
            ->add('issueType', 'choice',
                array(
                    'label' => 'oro.issue.issue_type.label',
                    'choices' => array(
                        Issue::TYPE_TASK    => 'oro.issue.issue_type.task',
                        Issue::TYPE_STORY   => 'oro.issue.issue_type.story',
                        Issue::TYPE_SUBTASK => 'oro.issue.issue_type.subtask',
                        Issue::TYPE_BUG     => 'oro.issue.issue_type.bug'
                    ),
                    'required' => true
                )
            )
            ->add('priority', 'entity',
                array(
                    'label'    => 'oro.issue.priority.label',
                    'class' => 'Oro\Bundle\IssueBundle\Entity\IssuePriority',
                    'required' => true,
                    'multiple' => false,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('priority')->orderBy('priority.priority');
                    },
                    'property' => 'label',
                    'property_path' => 'priority',
                    'attr' => array('class'=>'form-control'),
                )
            )
            ->add('resolution', 'entity',
                array(
                    'label'    => 'oro.issue.resolution.label',
                    'class'    => 'Oro\Bundle\IssueBundle\Entity\IssueResolution',
                    'multiple' => false,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('resolution')->orderBy('resolution.priority');
                    },
                    'property' => 'label',
                    'property_path' => 'resolution',
                    'attr' => array('class'=>'form-control'),
                )
            )
            ->add('assignee', 'oro_user_select',
                array(
                    'label'    => 'oro.issue.assignee.label',
                    'attr' => array('class'=>'form-control'),
                )
            );
        $builder->add('tags', 'oro_tag_select',
            array(
                'label' => 'oro.tag.entity_plural_label',
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\IssueBundle\Entity\Issue'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oro_issue_form_issue';
    }
}
