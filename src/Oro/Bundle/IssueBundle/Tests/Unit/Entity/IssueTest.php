<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Entity;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\UserBundle\Entity\User;


class IssueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $property
     * @param string $value
     * @param string $expected
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Issue();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $issuePriority = $this
            ->getMockBuilder('Oro\Bundle\IssueBundle\Entity\IssuePriority')
            ->disableOriginalConstructor()
            ->getMock();
        $issueResolution = $this
            ->getMockBuilder('Oro\Bundle\IssueBundle\Entity\IssueResolution')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            'code'        => array('code', 'TEST_ISSUE', 'TEST_ISSUE'),
            'summary'     => array('summary', 'Test Issue summary', 'Test Issue summary'),
            'description' => array('description', 'Test Issue description', 'Test Issue description'),
            'createdAt'   => array('createdAt', $now, $now),
            'updatedAt'   => array('updatedAt', $now, $now),
            'issueType'   => array('issueType', 'task', 'task'),
            'priority'    => array('priority', $issuePriority, $issuePriority),
            'resolution'  => array('resolution', $issueResolution, $issueResolution),
            'assignee'    => array('assignee', $user, $user),
            'owner'       => array('owner', $user, $user),
        );
    }

    public function testAddRemoveCollaborator()
    {
        $obj = new Issue();
        $user = new User();
        $obj->setReporter($user);
        $obj->addCollaborator($user);
        $this->assertTrue($obj->getCollaborators()->contains($user));
        $this->assertEquals($obj->getReporter(), $user);

        $obj->removeCollaborator($user);
        $this->assertFalse($obj->getCollaborators()->contains($user));
    }
}
