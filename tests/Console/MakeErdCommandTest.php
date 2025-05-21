<?php
// tests/Console/MakeErdCommandTest.php

namespace Tests\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase;
use YourVendor\ErdGenerator\Console\MakeErdCommand;
use Mockery;

class MakeErdCommandTest extends TestCase
{
    protected $mockScanner;

    public static function setUpBeforeClass(): void
    {
        // Remove any Mockery aliasing or static mocking attempts
    }

    protected function getPackageProviders($app)
    {
        return [\YourVendor\ErdGenerator\ErdGeneratorServiceProvider::class];
    }

    public function setUp(): void
    {
        parent::setUp();
        // Register command manually for test
        $this->mockScanner = $this->createMock(\YourVendor\ErdGenerator\Helpers\ModelScanner::class);
        $this->app->make('Illuminate\\Contracts\\Console\\Kernel')->registerCommand(new MakeErdCommand($this->mockScanner));
    }

    public function test_no_models_found_outputs_error()
    {
        $this->mockScanner->method('getModels')->willReturn([]);
        $this->artisan('make:erd')->expectsOutput('No Eloquent models found in app/Models.')->assertExitCode(1);
    }

    public function test_mermaid_output_to_console()
    {
        $model = new class {
            public function getTable() { return 'users'; }
        };
        $this->mockScanner->method('getModels')->willReturn([get_class($model)]);
        $command = $this->getMockBuilder(MakeErdCommand::class)
            ->setConstructorArgs([$this->mockScanner])
            ->onlyMethods(['getTableColumns', 'getModelRelations'])
            ->getMock();
        $command->method('getTableColumns')->willReturn(['id', 'name']);
        $command->method('getModelRelations')->willReturn([]);
        $this->app->make('Illuminate\Contracts\Console\Kernel')->registerCommand($command);
        $this->artisan('make:erd --format=mermaid')->expectsOutputToContain('erDiagram')->assertExitCode(0);
    }

    public function test_plantuml_output_to_file()
    {
        $model = new class {
            public function getTable() { return 'posts'; }
        };
        $this->mockScanner->method('getModels')->willReturn([get_class($model)]);
        $command = $this->getMockBuilder(MakeErdCommand::class)
            ->setConstructorArgs([$this->mockScanner])
            ->onlyMethods(['getTableColumns', 'getModelRelations'])
            ->getMock();
        $command->method('getTableColumns')->willReturn(['id', 'title']);
        $command->method('getModelRelations')->willReturn([]);
        $this->app->make('Illuminate\Contracts\Console\Kernel')->registerCommand($command);
        $outputFile = __DIR__.'/erd-test.puml';
        @unlink($outputFile);
        $this->artisan('make:erd --format=plantuml --output='.$outputFile)->expectsOutput('ERD generated and saved to '.$outputFile)->assertExitCode(0);
        $this->assertFileExists($outputFile);
        $this->assertStringContainsString('@startuml', file_get_contents($outputFile));
        unlink($outputFile);
    }

    public function test_graphviz_with_relations()
    {
        $model = new class {
            public function getTable() { return 'comments'; }
        };
        $relatedModel = new class {
            public function getTable() { return 'posts'; }
        };
        $this->mockScanner->method('getModels')->willReturn([get_class($model)]);
        $command = $this->getMockBuilder(MakeErdCommand::class)
            ->setConstructorArgs([$this->mockScanner])
            ->onlyMethods(['getTableColumns', 'getModelRelations'])
            ->getMock();
        $command->method('getTableColumns')->willReturn(['id', 'body']);
        $command->method('getModelRelations')->willReturn([
            'post' => [
                'type' => 'BelongsTo',
                'related' => get_class($relatedModel),
                'pivot' => null,
            ],
        ]);
        $this->app->make('Illuminate\Contracts\Console\Kernel')->registerCommand($command);
        $this->artisan('make:erd --format=graphviz')->expectsOutputToContain('digraph ERD')->assertExitCode(0);
    }
}
