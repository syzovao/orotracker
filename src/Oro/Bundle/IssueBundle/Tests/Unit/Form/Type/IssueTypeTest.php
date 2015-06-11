<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Form\Type;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Form\Type\IssueType;


class IssueTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new IssueType();
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'data_class' => 'Oro\Bundle\IssueBundle\Entity\Issue',
                )
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_issue_form_issue', $this->type->getName());
    }

    public function testBuildForm()
    {
        $expectedFields = array(
            'code'        => 'text',
            'summary'     => 'text',
            'description' => 'textarea',
            'issueType'   => 'choice',
            'priority'    => 'entity',
            'resolution'  => 'entity',
            'assignee'    => 'oro_user_select',
            'tags'        => 'oro_tag_select'
        );

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $counter = 0;
        foreach ($expectedFields as $fieldName => $formType) {
            $builder->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

        $this->type->buildForm($builder, array());
    }
}
