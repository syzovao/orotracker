<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class IssueControllerTest extends WebTestCase
{
    const ISSUE_CODE = 'TEST_ISSUE_1';
    const ISSUE_DESCRIPTION = 'Test Issue 1 Description';
    const ISSUE_SUMMARY = 'Test Summary 1';
    const USER_NAME = 'admin';

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
     * @depends testCreate
     * @return string
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => 'TEST_ISSUE_1')
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_issue_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_issue_form_issue[code]'] = 'TEST_ISSUE_1_Updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Issue has been saved", $crawler->html());

        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => 'TEST_ISSUE_1_Updated')
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

    public function testGridData()
    {
        $response = $this->client->requestGrid(
            'issues-grid',
            array('issues-grid[_filter][code][value]' => 'TEST_ISSUE_1_Updated')
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
}
