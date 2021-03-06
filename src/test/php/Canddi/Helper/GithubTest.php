<?php

namespace Canddi\Kommander\Helper;
use Canddi\Kommander\TestCase;
use Canddi\Kommander\Exception\Fatal\ResponseException;
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

    public function testGettersSetters()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strUsername = 'Unit-Test-Username';
        $strOrganisation = 'Unit-Test-ORG';
        $strAccessToken = 'Unit-Test-PAT';
        $arrConfig = ['rule' => 'test'];
        $guzzleConnection = \Mockery::mock('\GuzzleHttp\Client');
        $arrStaticFiles = ['CODEOWNERS' => 'example content'];

        $modelHelperGithub->setAccessToken($strAccessToken);
        $modelHelperGithub->setConfig($arrConfig);
        $modelHelperGithub->setGuzzleConnection($guzzleConnection);
        $modelHelperGithub->setOrganisation($strOrganisation);
        $modelHelperGithub->setStaticFiles($arrStaticFiles);
        $modelHelperGithub->setUsername($strUsername);

        $this->assertEquals(
            $strUsername,
            $modelHelperGithub->getUsername($strUsername)
        );
        $this->assertEquals(
            $strOrganisation,
            $modelHelperGithub->getOrganisation($strOrganisation)
        );
        $this->assertEquals(
            $strAccessToken,
            $modelHelperGithub->getAccessToken($strAccessToken)
        );
        $this->assertEquals(
            $arrConfig,
            $modelHelperGithub->getConfig($arrConfig)
        );
        $this->assertEquals(
            $arrStaticFiles,
            $modelHelperGithub->getStaticFiles($arrStaticFiles)
        );

        $this->assertEquals(
            $guzzleConnection,
            $modelHelperGithub->getGuzzleConnection($guzzleConnection)
        );
        $this->assertTrue(
            $modelHelperGithub->getGuzzleConnection() instanceOf GuzzleClient
        );
    }

    public function testCreateBranch_exists()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strBranchName = 'testBranch';
        $intTeamId = 1;
        $intSuccessStatus = 204;

        $mockGetGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/branches/$strBranchName",
                [
                    'json' => [],
                ]
            )
            ->andReturn($mockGetGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolCreateBranchResponse = $this->_invokeProtMethod($modelHelperGithub, 'createBranch', $strRepository, $strBranchName);

        $this->assertTrue($boolCreateBranchResponse);
    }

    public function testCreateBranch_new()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strBranchName = 'testBranch';
        $strSha = 'exampleshastring';
        $intTeamId = 1;
        $intSuccessStatus = 204;

        $mockGetGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([
                [
                    "object" => [
                        "sha" => $strSha,
                    ],
                ],
            ]))
            ->mock();

        $mockPostGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();


        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/branches/$strBranchName",
                [
                    'json' => [],
                ]
            )
            ->andThrow(new ResponseException(500, 'example exception', []))
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/git/refs/heads",
                [
                    'json' => [],
                ]
            )
            ->andReturn($mockGetGuzzleResponse)
            ->shouldReceive('request')
            ->once()
            ->with(
                'POST',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/git/refs",
                [
                    'json' => [
                        "ref" => "refs/heads/$strBranchName",
                        "sha" => $strSha
                    ],
                ]
            )
            ->andReturn($mockPostGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolCreateBranchResponse = $this->_invokeProtMethod($modelHelperGithub, 'createBranch', $strRepository, $strBranchName);

        $this->assertTrue($boolCreateBranchResponse);
    }

    public function testCreateBranchProtection()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strBranchName1 = 'testBranch1';
        $strBranchName2 = 'testBranch2';
        $arrBranchRules = [
            'example_rule' => true,
        ];
        $arrRules = [
            $strBranchName1 => $arrBranchRules,
            $strBranchName2 => $arrBranchRules,
        ];
        $strSha = 'exampleshastring';
        $intTeamId = 1;
        $intSuccessStatus = 204;

        $mockPutGuzzleResponse1 = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();
        $mockPutGuzzleResponse2 = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'PUT',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/branches/$strBranchName1/protection",
                [
                    'json' => $arrBranchRules,
                ]
            )
            ->andReturn($mockPutGuzzleResponse1)
            ->shouldReceive('request')
            ->once()
            ->with(
                'PUT',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/branches/$strBranchName2/protection",
                [
                    'json' => $arrBranchRules,
                ]
            )
            ->andReturn($mockPutGuzzleResponse2)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolCreateBranchProtectionResponse = $this->_invokeProtMethod($modelHelperGithub, 'createBranchProtection', $strRepository, $arrRules);

        $this->assertTrue($boolCreateBranchProtectionResponse);
    }

    public function testDisableBranchProtection()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strBranchName1 = 'testBranch1';
        $arrRules = [
            $strBranchName1 => [
                'example_rule' => true,
            ]
        ];
        $strSha = 'exampleshastring';
        $intTeamId = 1;
        $intSuccessStatus = 204;

        $mockPutGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'DELETE',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/branches/$strBranchName1/protection",
                [
                    'json' => [],
                ]
            )
            ->andReturn($mockPutGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolCreateBranchProtectionResponse = $this->_invokeProtMethod($modelHelperGithub, 'disableBranchProtection', $strRepository, $arrRules);

        $this->assertTrue($boolCreateBranchProtectionResponse);
    }

    public function testDisableBranchProtection_missing()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strBranchName = 'testBranch';
        $arrRules = [
            $strBranchName => [
                'example_rule' => true,
            ]
        ];


        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'DELETE',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/branches/$strBranchName/protection",
                [
                    'json' => [],
                ]
            )
            ->andThrow(new ResponseException(404, 'exception message', []))
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolCreateBranchProtectionResponse = $this->_invokeProtMethod($modelHelperGithub, 'disableBranchProtection', $strRepository, $arrRules);

        $this->assertTrue($boolCreateBranchProtectionResponse);
    }

    public function testCreateDefaultBranch()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strBranchName = 'testBranch';
        $intTeamId = 1;
        $intSuccessStatus = 204;

        $mockPatchGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'PATCH',
                "$strGithubRoot/repos/$strOrganisation/$strRepository",
                [
                    'json' => [
                        'name' => $strRepository,
                        'default_branch' => $strBranchName,
                    ],
                ]
            )
            ->andReturn($mockPatchGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolCreateDefaultBranchResponse = $this->_invokeProtMethod($modelHelperGithub, 'createDefaultBranch', $strRepository, $strBranchName);

        $this->assertTrue($boolCreateDefaultBranchResponse);
    }

    public function testPushStaticDirectory_exists()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strSha = 'exampleshastring';
        $strBranchName = 'testBranch';
        $strFilename = 'CODEOWNERS';
        $strGithubCommitMsg = "Add %s";
        $strFileContents = '* @Deep-Web-Technologies/canmergetodev';
        $arrStaticFiles = [
            $strFilename => $strFileContents,
        ];
        $modelHelperGithub->setStaticFiles($arrStaticFiles);

        $mockGetGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([
                'sha' => $strSha
            ]))
            ->mock();
        $mockPutGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/contents/.github/$strFilename",
                [
                    'json' => [],
                ]
            )
            ->andReturn($mockGetGuzzleResponse)
            ->shouldReceive('request')
            ->once()
            ->with(
                'PUT',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/contents/.github/$strFilename",
                [
                    'json' => [
                        "message" => sprintf($strGithubCommitMsg, $strFilename),
                        "content" => base64_encode($strFileContents),
                        "sha" => $strSha,
                    ],
                ]
            )
            ->andReturn($mockPutGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolPushStaticDirectoryResponse = $this->_invokeProtMethod($modelHelperGithub, 'pushStaticDirectory', $strRepository, $strBranchName);

        $this->assertTrue($boolPushStaticDirectoryResponse);
    }

    public function testPushStaticDirectory_new()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $strSha = 'exampleshastring';
        $strBranchName = 'testBranch';
        $strFilename = 'CODEOWNERS';
        $strGithubCommitMsg = "Add %s";
        $strFileContents = '* @Deep-Web-Technologies/canmergetodev';
        $arrStaticFiles = [
            $strFilename => $strFileContents,
        ];
        $modelHelperGithub->setStaticFiles($arrStaticFiles);

        $mockGetGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([
                'sha' => $strSha
            ]))
            ->mock();
        $mockPutGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/contents/.github/$strFilename",
                [
                    'json' => [],
                ]
            )
            ->andThrow(new ResponseException(500, 'File does not exist', []))
            ->shouldReceive('request')
            ->once()
            ->with(
                'PUT',
                "$strGithubRoot/repos/$strOrganisation/$strRepository/contents/.github/$strFilename",
                [
                    'json' => [
                        "message" => sprintf($strGithubCommitMsg, $strFilename),
                        "content" => base64_encode($strFileContents)
                    ],
                ]
            )
            ->andReturn($mockPutGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $boolPushStaticDirectoryResponse = $this->_invokeProtMethod($modelHelperGithub, 'pushStaticDirectory', $strRepository, $strBranchName);

        $this->assertTrue($boolPushStaticDirectoryResponse);
    }

    public function testCreateNewRepository()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository = 'testRepository';
        $intRepositoryId = 1;
        $boolRepositoryPrivate = true;

        $mockPostGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode([
                'id' => $intRepositoryId,
                'name' => $strRepository,
                'private' => $boolRepositoryPrivate,
            ]))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'POST',
                "$strGithubRoot/orgs/$strOrganisation/repos",
                [
                    'json' => [
                        'name' => $strRepository,
                        'private' => $boolRepositoryPrivate,
                    ],
                ]
            )
            ->andReturn($mockPostGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $arrExpectedResult = [
            'id' => $intRepositoryId,
            'name' => $strRepository,
            'private' => $boolRepositoryPrivate,
        ];

        $arrPushStaticDirectoryResponse = $this->_invokeProtMethod($modelHelperGithub, 'createNewRepository', $strRepository);

        $this->assertEquals($arrExpectedResult, $arrPushStaticDirectoryResponse);
    }

    public function testListRepositories()
    {
        $modelHelperGithub = \Canddi_Helper_Github::getInstance();

        $strGithubRoot = 'https://api.github.com';
        $strOrganisation = 'Unit-Test-ORG';
        $strRepository1 = 'testRepository';
        $strRepository2 = 'testRepository2';
        $arrRepositories = [
            [
                'name' => $strRepository1,
            ],
            [
                'name' => $strRepository2,
            ],
        ];
        $arrExpectedRepositoryNames = [
            $strRepository1,
            $strRepository2,
        ];

        $mockGetGuzzleResponse = \Mockery::mock('\GuzzleHttp\Psr7\Response')
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(JSON_encode($arrRepositories))
            ->mock();

        $mockGuzzleConnection = \Mockery::mock('\GuzzleHttp\Client')
            ->shouldReceive('request')
            ->once()
            ->with(
                'GET',
                "$strGithubRoot/orgs/$strOrganisation/repos",
                [
                    'json' => [],
                ]
            )
            ->andReturn($mockGetGuzzleResponse)
            ->mock();

        $modelHelperGithub->setGuzzleConnection($mockGuzzleConnection); // inject guzzle

        $arrPushStaticDirectoryResponse = $this->_invokeProtMethod($modelHelperGithub, 'listRepositories');

        $this->assertEquals($arrExpectedRepositoryNames, $arrPushStaticDirectoryResponse);
    }
}
