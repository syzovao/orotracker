<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\IssueBundle\Entity\IssuePriority;
use Oro\Bundle\IssueBundle\Entity\IssueResolution;
use Oro\Bundle\IssueBundle\Entity\Issue;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class IssueControllerTest extends WebTestCase
{
    const ISSUE_CODE = 'TEST_ISSUE_1';
    const ISSUE_CODE_UPDATED = 'TEST_ISSUE_1_Updated';
    const ISSUE_DESCRIPTION = 'Test Issue 1 Description';
    const ISSUE_SUMMARY = 'Test Summary 1';
    const USER_NAME = 'admin';

    const ISSUE_CODE_STORY = 'TEST_STORY_1';
    const ISSUE_CODE_SUBTASK = 'TEST_SUBTASK_1';

    protected $userId;

    protected function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_issue_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertEquals('Manage Issues - Issues', $crawler->filter('#page-title')->html());
    }

    public function testCreate()
    {
        $userId = $this->getUserId();
        // Create a new client to browse the application
        $client = $this->client;
        $crawler = $client->request('GET', $this->getUrl('oro_issue_create'));

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form->setValues(array(
            'oro_issue_form_issue[code]' => self::ISSUE_CODE,
            'oro_issue_form_issue[summary]' => self::ISSUE_SUMMARY,
            'oro_issue_form_issue[description]' => self::ISSUE_DESCRIPTION,
            'oro_issue_form_issue[issueType]' => 'task',
            'oro_issue_form_issue[priority]' => 'trivial',
            'oro_issue_form_issue[resolution]' => 'unresolved',
        ));
        $form['oro_issue_form_issue[assignee]'] = $userId;
        $client->followRedirects(true);
        $crawler = $client->submit($form);

        $result = $client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Issue has been saved", $crawler->html());
    }

    /**
     * @return string
     */
    public function testCreateParent()
    {
        $userId = $this->getUserId();
        // Create a new client to browse the application
        $client = $this->client;
        $crawler = $client->request('GET', $this->getUrl('oro_issue_create'));

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form->setValues(array(
            'oro_issue_form_issue[code]' => self::ISSUE_CODE_STORY,
            'oro_issue_form_issue[summary]' => self::ISSUE_CODE_STORY,
            'oro_issue_form_issue[description]' => self::ISSUE_CODE_STORY,
            'oro_issue_form_issue[issueType]' => Issue::TYPE_STORY,
            'oro_issue_form_issue[priority]' => IssuePriority::PRIORITY_TRIVIAL,
            'oro_issue_form_issue[resolution]' => IssueResolution::RESOLUTION_UNRESOLVED,
        ));
        $form['oro_issue_form_issue[assignee]'] = $userId;
        $client->followRedirects(true);
        $crawler = $client->submit($form);

        $result = $client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Issue has been saved", $crawler->html());

        //Create Sub-task
        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => self::ISSUE_CODE_STORY)
        );
        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_issue_create', array('parent' => $result['id']))
        );
        $form = $crawler->selectButton('Save')->form();
        $client->followRedirects(true);
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form->setValues(array(
            'oro_issue_form_issue[code]' => self::ISSUE_CODE_SUBTASK,
            'oro_issue_form_issue[summary]' => self::ISSUE_CODE_SUBTASK,
            'oro_issue_form_issue[description]' => self::ISSUE_CODE_SUBTASK,
            'oro_issue_form_issue[issueType]' => Issue::TYPE_SUBTASK,
            'oro_issue_form_issue[priority]' => IssuePriority::PRIORITY_TRIVIAL,
            'oro_issue_form_issue[resolution]' => IssueResolution::RESOLUTION_UNRESOLVED,
        ));
        $form['oro_issue_form_issue[assignee]'] = $userId;

        $client->followRedirects(true);
        $crawler = $client->submit($form);

        $result = $client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Issue has been saved", $crawler->html());
        $this->assertContains(self::ISSUE_CODE_STORY, $crawler->html());
    }

    /**
     * @depends testCreate
     * @return string
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => self::ISSUE_CODE)
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_issue_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_issue_form_issue[code]'] = self::ISSUE_CODE_UPDATED;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Issue has been saved", $crawler->html());

        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => self::ISSUE_CODE_UPDATED)
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        return $result['id'];
    }

    /**
     * @depends testUpdate
     * @param string $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_issue_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("TEST_ISSUE_1_Updated - Manage Issues - Issues", $crawler->html());
    }

    /**
     * @return array
     */
    protected function getUserId()
    {
        if (!$this->userId) {
            $userId = $this->getContainer()->get('doctrine')
                ->getRepository('OroUserBundle:User')
                ->findOneBy(['username' => self::USER_NAME])->getId();
            $this->userId = $userId;
        }
        return $this->userId;
    }

    /**
     * @depends testUpdate
     */
    public function testGridData()
    {
        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => self::ISSUE_CODE_UPDATED)
        );

        $result = $this->getJsonResponseContent($response, 200);

        $response = $this->client->requestGrid(
            'issues-grid',
            array(
                'issues-grid[_filter][summary][value]' => self::ISSUE_SUMMARY
            )
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->assertEquals(self::ISSUE_SUMMARY, $result['summary']);
    }

    /**
     * @depends testUpdate
     */
    public function testSearchData()
    {
        $searchStr = self::ISSUE_CODE;
        $this->client->request('GET',
            $this->getUrl('oro_search_results',
                array(
                    'from' => 'oro_issue',
                    'search' => $searchStr
                )
            )
        );
        $result = $this->client->getResponse();

        $this->assertContains($searchStr, $result->getContent());
        $this->assertContains('Search results', $result->getContent());
    }

    /**
     * @depends testUpdate
     */
    public function testUserProfileIssueData()
    {
        $this->client->request('GET', $this->getUrl('oro_user_profile_view'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Create Issue", $result->getContent());

        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => self::ISSUE_CODE_UPDATED)
        );
        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $this->assertEquals(self::ISSUE_CODE_UPDATED, $result['code']);
    }

    /**
     * @depends testView
     */
    public function testDelete()
    {
        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => self::ISSUE_CODE_SUBTASK)
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $id = $result['id'];

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_issue', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_issue_update', array('id' => $id)));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }
}
