<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Form\Handler\IssueHandler;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Tests\Unit\Fixtures\Taggable;


class IssueHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagManager;

    /**
     * @var IssueHandler
     */
    protected $handler;

    /**
     * @var Issue
     */
    protected $entity;

    protected function setUp()
    {
        $this->tagManager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = new Request();
        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new Issue();
        $this->handler = new IssueHandler($this->form, $this->request, $this->manager);
    }

    public function testSetTagManager()
    {
        $this->tagManager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->tagManager);
    }

    public function testProcessUnsupportedRequest()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     * @param string $method
     */
    public function testProcessSupportedRequest($method)
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }

}
