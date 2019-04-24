<?php

namespace Canddi\Kommander\Helper\Config;
use Canddi\Kommander\TestCase;
use GuzzleHttp\Client as GuzzleClient;

class GithubTest
    extends TestCase
{
    public function testConstructInstance()
    {
        $modelHelperConfig = \Canddi_Helper_Github::getInstance();

        $this->assertTrue($modelHelperConfig instanceof \Canddi_Helper_Github);
    }

    public function testConstructSingleton()
    {
        $modelHelperConfig1 = \Canddi_Helper_Github::getInstance();
        $modelHelperConfig2 = \Canddi_Helper_Github::getInstance();

        $this->assertTrue($modelHelperConfig1 instanceof $modelHelperConfig2);
    }

    public function testConstructSetters()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubUsername = 'Unit-Test-USER';
        $strGithubPAT = 'Unit-Test-PAT';
        $strGithubOrganisation = 'Unit-Test-ORG';
        $arrStaticFiles = [
            'CODEOWNERS' => '* @Deep-Web-Technologies/canmergetodev',
        ];

        $this->assertTrue(
            $modelHelperGithub->getConfig() instanceOf \Canddi_Helper_Config
        );
        $this->assertEquals(
            $strGithubUsername,
            $modelHelperGithub->getUsername()
        );
        $this->assertEquals(
            $strGithubPAT,
            $modelHelperGithub->getAccessToken()
        );
        $this->assertEquals(
            $strGithubOrganisation,
            $modelHelperGithub->getOrganisation()
        );
        $this->assertEquals(
            $arrStaticFiles,
            $modelHelperGithub->getStaticFiles()
        );
        $this->assertTrue(
            $modelHelperGithub->getGuzzleConnection() instanceOf GuzzleClient
        );
    }

    public function testAddTeamRepo()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strTeamName = 'testTeam';
        $intTeamId = 1;
        $intSuccessStatus = 204;

        $mockGetGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([
                'id' => $intTeamId,
            ]))
            ->mock();
        $mockPutGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(null)
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn($intSuccessStatus)
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/orgs/$strOrganisation/teams/$strTeamName",
                [
                    'json' => [],
                ]
            )
            ->andReturn($mockGetGuzzleResponse)
            ->shouldReceive('request')
            ->once()
            ->with(
                'PUT',
                "$strGithubRoot/teams/$intTeamId/repos/$strOrganisation/$strRepository",
                [
                    'json' => [
                        'permission' => 'admin',
                    ],
                ]
            )
            ->andReturn($mockPutGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolAddTeamRepoResponse = $this->_invokeProtMethod($modelHelperGithub, 'addTeamRepo', $strRepository, $strTeamName);

        $this->assertTrue($boolAddTeamRepoResponse);
    }
}