<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\ResultPrinter;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    protected $configuration;

    protected function setUp(): void
    {
        $this->configuration = Configuration::getInstance(
            \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.xml'
        );
    }

    public function testExceptionIsThrownForNotExistingConfigurationFile(): void
    {
        $this->expectException(Exception::class);

        Configuration::getInstance('not_existing_file.xml');
    }

    public function testShouldReadColorsWhenTrueInConfigurationFile(): void
    {
        $configurationFilename =  \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.colors.true.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(ResultPrinter::COLOR_AUTO, $configurationValues['colors']);
    }

    public function testShouldReadColorsWhenFalseInConfigurationFile(): void
    {
        $configurationFilename =  \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.colors.false.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(ResultPrinter::COLOR_NEVER, $configurationValues['colors']);
    }

    public function testShouldReadColorsWhenEmptyInConfigurationFile(): void
    {
        $configurationFilename =  \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.colors.empty.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(ResultPrinter::COLOR_NEVER, $configurationValues['colors']);
    }

    public function testShouldReadColorsWhenInvalidInConfigurationFile(): void
    {
        $configurationFilename =  \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.colors.invalid.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(ResultPrinter::COLOR_NEVER, $configurationValues['colors']);
    }

    public function testInvalidConfigurationGeneratesValidationErrors(): void
    {
        $configurationFilename =  \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.colors.invalid.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);

        $this->assertTrue($configurationInstance->hasValidationErrors());
        $this->assertArraySubset([1 => ["Element 'phpunit', attribute 'colors': 'Something else' is not a valid value of the atomic type 'xs:boolean'."]], $configurationInstance->getValidationErrors());
    }

    public function testNonIntegerValueReturnsDefault(): void
    {
        $configurationFilename =  \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.columns.default.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(80, $configurationValues['columns']);
    }

    /**
     * @dataProvider configurationRootOptionsProvider
     *
     * @param bool|int|string $expected
     */
    public function testConfigurationRootOptions(string $optionName, string $optionValue, $expected): void
    {
        $tmpFilename = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit.' . $optionName . \uniqid() . '.xml';
        $xml         = "<phpunit $optionName='$optionValue'></phpunit>" . PHP_EOL;
        \file_put_contents($tmpFilename, $xml);

        $configurationInstance = Configuration::getInstance($tmpFilename);
        $this->assertFalse($configurationInstance->hasValidationErrors(), 'option causes validation error');

        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();
        $this->assertEquals($expected, $configurationValues[$optionName]);

        @\unlink($tmpFilename);
    }

    public function configurationRootOptionsProvider(): array
    {
        return [
            ['executionOrder', 'default', TestSuiteSorter::ORDER_DEFAULT],
            ['executionOrder', 'random', TestSuiteSorter::ORDER_RANDOMIZED],
            ['executionOrder', 'reverse', TestSuiteSorter::ORDER_REVERSED],
            ['columns', 'max', 'max'],
            ['stopOnFailure', 'true', true],
            ['stopOnWarning', 'true', true],
            ['stopOnIncomplete', 'true', true],
            ['stopOnRisky', 'true', true],
            ['stopOnSkipped', 'true', true],
            ['failOnWarning', 'true', true],
            ['failOnRisky', 'true', true],
            ['disableCodeCoverageIgnore', 'true', true],
            ['processIsolation', 'true', true],
            ['testSuiteLoaderFile', '/path/to/file', '/path/to/file'],
            ['reverseDefectList', 'true', true],
            ['registerMockObjectsFromTestArgumentsRecursively', 'true', true],
        ];
    }

    public function testFilterConfigurationIsReadCorrectly(): void
    {
        $this->assertEquals(
            [
                'whitelist' =>
                    [
                        'addUncoveredFilesFromWhitelist'     => true,
                        'processUncoveredFilesFromWhitelist' => false,
                        'include'                            =>
                            [
                                'directory' =>
                                    [
                                        0 =>
                                            [
                                                'path'   => '/path/to/files',
                                                'prefix' => '',
                                                'suffix' => '.php',
                                                'group'  => 'DEFAULT'
                                            ],
                                    ],
                                'file' =>
                                    [
                                        0 => '/path/to/file',
                                        1 => '/path/to/file',
                                    ],
                            ],
                        'exclude' =>
                            [
                                'directory' =>
                                    [
                                        0 =>
                                            [
                                                'path'   => '/path/to/files',
                                                'prefix' => '',
                                                'suffix' => '.php',
                                                'group'  => 'DEFAULT'
                                            ],
                                    ],
                                'file' =>
                                    [
                                        0 => '/path/to/file',
                                    ],
                            ],
                    ],
            ],
            $this->configuration->getFilterConfiguration()
        );
    }

    public function testGroupConfigurationIsReadCorrectly(): void
    {
        $this->assertEquals(
            [
                'include' =>
                    [
                        0 => 'name',
                    ],
                'exclude' =>
                    [
                        0 => 'name',
                    ],
            ],
            $this->configuration->getGroupConfiguration()
        );
    }

    public function testTestdoxGroupConfigurationIsReadCorrectly(): void
    {
        $this->assertEquals(
            [
                'include' =>
                    [
                        0 => 'name',
                    ],
                'exclude' =>
                    [
                        0 => 'name',
                    ],
            ],
            $this->configuration->getTestdoxGroupConfiguration()
        );
    }

    public function testListenerConfigurationIsReadCorrectly(): void
    {
        $dir         = __DIR__;
        $includePath = \ini_get('include_path');

        \ini_set('include_path', $dir . PATH_SEPARATOR . $includePath);

        $this->assertEquals(
            [
                0 =>
                    [
                        'class'     => 'MyListener',
                        'file'      => '/optional/path/to/MyListener.php',
                        'arguments' =>
                            [
                                0 =>
                                    [
                                        0 => 'Sebastian',
                                    ],
                                1 => 22,
                                2 => 'April',
                                3 => 19.78,
                                4 => null,
                                5 => new \stdClass,
                                6 => \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MyTestFile.php',
                                7 => \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MyRelativePath',
                                8 => true,
                            ],
                    ],
                [
                    'class'     => 'IncludePathListener',
                    'file'      => __FILE__,
                    'arguments' => []
                ],
                [
                    'class'     => 'CompactArgumentsListener',
                    'file'      => '/CompactArgumentsListener.php',
                    'arguments' =>
                        [
                            0 => 42,
                            1 => false,
                        ],
                ],
            ],
            $this->configuration->getListenerConfiguration()
        );

        \ini_set('include_path', $includePath);
    }

    public function testExtensionConfigurationIsReadCorrectly(): void
    {
        $dir         = __DIR__;
        $includePath = \ini_get('include_path');

        \ini_set('include_path', $dir . PATH_SEPARATOR . $includePath);

        $this->assertEquals(
            [
                0 =>
                    [
                        'class'     => 'MyExtension',
                        'file'      => '/optional/path/to/MyExtension.php',
                        'arguments' =>
                            [
                                0 =>
                                    [
                                        0 => 'Sebastian',
                                    ],
                                1 => 22,
                                2 => 'April',
                                3 => 19.78,
                                4 => null,
                                5 => new \stdClass,
                                6 => \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MyTestFile.php',
                                7 => \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MyRelativePath',
                            ],
                    ],
                [
                    'class'     => 'IncludePathExtension',
                    'file'      => __FILE__,
                    'arguments' => []
                ],
                [
                    'class'     => 'CompactArgumentsExtension',
                    'file'      => '/CompactArgumentsExtension.php',
                    'arguments' =>
                        [
                            0 => 42
                        ],
                ],
            ],
            $this->configuration->getExtensionConfiguration()
        );

        \ini_set('include_path', $includePath);
    }

    public function testLoggingConfigurationIsReadCorrectly(): void
    {
        $this->assertEquals(
            [
                'lowUpperBound'                  => '50',
                'highLowerBound'                 => '90',
                'coverage-html'                  => '/tmp/report',
                'coverage-clover'                => '/tmp/clover.xml',
                'coverage-crap4j'                => '/tmp/crap4j.xml',
                'crap4jThreshold'                => 50,
                'coverage-text'                  => '/tmp/coverage.txt',
                'coverageTextShowUncoveredFiles' => true,
                'coverageTextShowOnlySummary'    => true,
                'json'                           => '/tmp/logfile.json',
                'plain'                          => '/tmp/logfile.txt',
                'tap'                            => '/tmp/logfile.tap',
                'junit'                          => '/tmp/logfile.xml',
                'testdox-html'                   => '/tmp/testdox.html',
                'testdox-text'                   => '/tmp/testdox.txt',
                'testdox-xml'                    => '/tmp/testdox.xml'
            ],
            $this->configuration->getLoggingConfiguration()
        );
    }

    public function testPHPConfigurationIsReadCorrectly(): void
    {
        $this->assertEquals(
            [
                'include_path' =>
                    [
                        \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . '.',
                        '/path/to/lib'
                    ],
                'ini'    => ['foo' => ['value' => 'bar']],
                'const'  => ['FOO' => ['value' => false], 'BAR' => ['value' => true]],
                'var'    => ['foo' => ['value' => false]],
                'env'    => ['foo' => ['value' => true], 'bar' => ['value' => 'true', 'verbatim' => true], 'foo_force' => ['value' => 'forced', 'force' => true]],
                'post'   => ['foo' => ['value' => 'bar']],
                'get'    => ['foo' => ['value' => 'bar']],
                'cookie' => ['foo' => ['value' => 'bar']],
                'server' => ['foo' => ['value' => 'bar']],
                'files'  => ['foo' => ['value' => 'bar']],
                'request'=> ['foo' => ['value' => 'bar']],
            ],
            $this->configuration->getPHPConfiguration()
        );
    }

    /**
     * @backupGlobals enabled
     */
    public function testPHPConfigurationIsHandledCorrectly(): void
    {
        $this->configuration->handlePHPConfiguration();

        $path = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . '.' . PATH_SEPARATOR . '/path/to/lib';
        $this->assertStringStartsWith($path, \ini_get('include_path'));
        $this->assertFalse(\FOO);
        $this->assertTrue(\BAR);
        $this->assertFalse($GLOBALS['foo']);
        $this->assertTrue($_ENV['foo']);
        $this->assertEquals('forced', \getenv('foo_force'));
        $this->assertEquals('bar', $_POST['foo']);
        $this->assertEquals('bar', $_GET['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
        $this->assertEquals('bar', $_SERVER['foo']);
        $this->assertEquals('bar', $_FILES['foo']);
        $this->assertEquals('bar', $_REQUEST['foo']);
    }

    /**
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverwrittenExistingEnvArrayVariables(): void
    {
        $_ENV['foo'] = false;
        $this->configuration->handlePHPConfiguration();

        $this->assertFalse($_ENV['foo']);
        $this->assertEquals('forced', \getenv('foo_force'));
    }

    /**
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/2353
     */
    public function testHandlePHPConfigurationDoesForceOverwrittenExistingEnvArrayVariables(): void
    {
        $_ENV['foo_force'] = false;
        $this->configuration->handlePHPConfiguration();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', \getenv('foo_force'));
    }

    /**
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverriteVariablesFromPutEnv(): void
    {
        \putenv('foo=putenv');
        $this->configuration->handlePHPConfiguration();

        $this->assertTrue($_ENV['foo']);
        $this->assertEquals('putenv', \getenv('foo'));
    }

    /**
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesOverwriteVariablesFromPutEnvWhenForced(): void
    {
        \putenv('foo_force=putenv');
        $this->configuration->handlePHPConfiguration();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', \getenv('foo_force'));
    }

    public function testPHPUnitConfigurationIsReadCorrectly(): void
    {
        $this->assertEquals(
            [
                'backupGlobals'                              => true,
                'backupStaticAttributes'                     => false,
                'beStrictAboutChangesToGlobalState'          => false,
                'bootstrap'                                  => '/path/to/bootstrap.php',
                'cacheTokens'                                => false,
                'columns'                                    => 80,
                'colors'                                     => 'never',
                'stderr'                                     => false,
                'convertDeprecationsToExceptions'            => true,
                'convertErrorsToExceptions'                  => true,
                'convertNoticesToExceptions'                 => true,
                'convertWarningsToExceptions'                => true,
                'forceCoversAnnotation'                      => false,
                'stopOnFailure'                              => false,
                'stopOnWarning'                              => false,
                'reportUselessTests'                         => false,
                'strictCoverage'                             => false,
                'disallowTestOutput'                         => false,
                'enforceTimeLimit'                           => false,
                'extensionsDirectory'                        => '/tmp',
                'printerClass'                               => 'PHPUnit\TextUI\ResultPrinter',
                'testSuiteLoaderClass'                       => 'PHPUnit\Runner\StandardTestSuiteLoader',
                'defaultTestSuite'                           => 'My Test Suite',
                'verbose'                                    => false,
                'timeoutForSmallTests'                       => 1,
                'timeoutForMediumTests'                      => 10,
                'timeoutForLargeTests'                       => 60,
                'beStrictAboutResourceUsageDuringSmallTests' => false,
                'disallowTodoAnnotatedTests'                 => false,
                'failOnWarning'                              => false,
                'failOnRisky'                                => false,
                'ignoreDeprecatedCodeUnitsFromCodeCoverage'  => false,
                'executionOrder'                             => 0,
                'resolveDependencies'                        => false
            ],
            $this->configuration->getPHPUnitConfiguration()
        );
    }

    public function testXincludeInConfiguration(): void
    {
        $configurationWithXinclude = Configuration::getInstance(
            \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration_xinclude.xml'
        );

        $this->assertConfigurationEquals(
            $this->configuration,
            $configurationWithXinclude
        );
    }

    /**
     * @ticket 1311
     */
    public function testWithEmptyConfigurations(): void
    {
        $emptyConfiguration = Configuration::getInstance(
            \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration_empty.xml'
        );

        $logging = $emptyConfiguration->getLoggingConfiguration();
        $this->assertEmpty($logging);

        $php = $emptyConfiguration->getPHPConfiguration();
        $this->assertEmpty($php['include_path']);

        $phpunit = $emptyConfiguration->getPHPUnitConfiguration();
        $this->assertArrayNotHasKey('bootstrap', $phpunit);
        $this->assertArrayNotHasKey('testSuiteLoaderFile', $phpunit);
        $this->assertArrayNotHasKey('printerFile', $phpunit);

        $suite = $emptyConfiguration->getTestSuiteConfiguration();
        $this->assertEmpty($suite->getGroups());

        $filter = $emptyConfiguration->getFilterConfiguration();
        $this->assertEmpty($filter['whitelist']['include']['directory']);
        $this->assertEmpty($filter['whitelist']['include']['file']);
        $this->assertEmpty($filter['whitelist']['exclude']['directory']);
        $this->assertEmpty($filter['whitelist']['exclude']['file']);
    }

    public function testGetTestSuiteNamesReturnsTheNamesIfDefined(): void
    {
        $configuration = Configuration::getInstance(
            \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.suites.xml'
        );

        $names = $configuration->getTestSuiteNames();

        $this->assertEquals(['Suite One', 'Suite Two'], $names);
    }

    public function testTestSuiteConfigurationForASingleFileInASuite(): void
    {
        $configuration = Configuration::getInstance(
            \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.one-file-suite.xml'
        );

        $config = $configuration->getTestSuiteConfiguration();
        $tests  = $config->tests();

        $this->assertCount(1, $tests);
    }

    /**
     * Asserts that the values in $actualConfiguration equal $expectedConfiguration.
     *
     * @param Configuration $expectedConfiguration
     * @param Configuration $actualConfiguration
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertConfigurationEquals(Configuration $expectedConfiguration, Configuration $actualConfiguration): void
    {
        $this->assertEquals(
            $expectedConfiguration->getFilterConfiguration(),
            $actualConfiguration->getFilterConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getGroupConfiguration(),
            $actualConfiguration->getGroupConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getListenerConfiguration(),
            $actualConfiguration->getListenerConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getLoggingConfiguration(),
            $actualConfiguration->getLoggingConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getPHPConfiguration(),
            $actualConfiguration->getPHPConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getPHPUnitConfiguration(),
            $actualConfiguration->getPHPUnitConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getTestSuiteConfiguration(),
            $actualConfiguration->getTestSuiteConfiguration()
        );
    }
}