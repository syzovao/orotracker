<?php

namespace Oro\Bundle\IssueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IssueController extends Controller
{
    public function indexAction()
    {
        return $this->render('OroIssueBundle:Issue:index.html.twig');
    }
}
