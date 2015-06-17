<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Entity;

use Oro\Bundle\IssueBundle\Entity\IssuePriority;

class IssuePriorityTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;
    protected $code = 'TESTCODE';
    protected $label = 'TESTNAME';

    protected function setUp()
    {
        $this->obj = new IssuePriority($this->code);
    }

    public function testGetCode()
    {
        $this->assertTrue($this->obj->getCode() == $this->code);
    }

    public function testGetSetLabel()
    {
        $this->obj->setLabel($this->label);
        $this->assertTrue($this->obj->getLabel() == $this->label);
    }

    public function testGetSetPriority()
    {
        $priority = 10;
        $this->obj->setPriority($priority);
        $this->assertTrue($this->obj->getPriority() == $priority);
    }
}
