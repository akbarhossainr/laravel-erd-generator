<?php

namespace Akbarhossainr\ErdGenerator\Console;

use Illuminate\Console\Command;
use Akbarhossainr\ErdGenerator\Helpers\ModelScanner;

class MakeErdCommand extends Command
{
    protected $signature = 'make:erd {--format=mermaid} {--output=} {--columns}';
    protected $description = 'Generate an Entity-Relationship Diagram (ERD) from Eloquent models.';
    protected $modelScanner;

    public function __construct(ModelScanner $modelScanner = null)
    {
        parent::__construct();
        $this->modelScanner = $modelScanner ?: new ModelScanner();
    }

    public function handle()
    {
        $format = $this->option('format');
        $output = $this->option('output');
        $showColumns = $this->option('columns');

        $models = $this->modelScanner->getModels();
        if (empty($models)) {
            $this->error('No Eloquent models found in app/Models.');
            return 1;
        }

        $diagram = $this->generateErd($models, $format, $showColumns);

        if ($output) {
            file_put_contents($output, $diagram);
            $this->info("ERD generated and saved to {$output}");
        } else {
            $this->line($diagram);
        }
    }

    /**
     * Generate ERD in the specified format.
     */
    protected function generateErd(array $models, string $format, bool $showColumns): string
    {
        // Step 1: Collect model metadata (columns, relationships)
        $entities = [];
        foreach ($models as $modelClass) {
            $model = new $modelClass;
            $table = $model->getTable();
            $columns = $showColumns ? $this->getTableColumns($model) : [];
            $relations = $this->getModelRelations($model);
            $entities[$table] = [
                'class' => $modelClass,
                'columns' => $columns,
                'relations' => $relations,
            ];
        }
        // Step 2: Render ERD
        switch (strtolower($format)) {
            case 'plantuml':
                return $this->renderPlantUml($entities);
            case 'graphviz':
                return $this->renderGraphviz($entities);
            case 'mermaid':
            default:
                return $this->renderMermaid($entities);
        }
    }

    protected function getTableColumns($model): array
    {
        // Try to get columns from DB (requires DB connection)
        try {
            return \Schema::getColumnListing($model->getTable());
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getModelRelations($model): array
    {
        $reflect = new \ReflectionClass($model);
        $methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);
        $relations = [];
        foreach ($methods as $method) {
            if ($method->class !== get_class($model) || $method->getNumberOfParameters() > 0) continue;
            try {
                $result = $method->invoke($model);
                if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $related = get_class($result->getRelated());
                    $type = (new \ReflectionClass($result))->getShortName();
                    $relations[$method->getName()] = [
                        'type' => $type,
                        'related' => $related,
                        'pivot' => method_exists($result, 'getTable') ? $result->getTable() : null,
                    ];
                }
            } catch (\Throwable $e) {
                // skip
            }
        }
        return $relations;
    }

    protected function renderMermaid(array $entities): string
    {
        $lines = ["erDiagram"];
        foreach ($entities as $table => $meta) {
            $lines[] = "  {$table} {";
            foreach ($meta['columns'] as $col) {
                $lines[] = "    string {$col}";
            }
            $lines[] = "  }";
        }
        // Relationships
        foreach ($entities as $table => $meta) {
            foreach ($meta['relations'] as $rel) {
                $target = (new $rel['related'])->getTable();
                $lines[] = "  {$table} ||--o{ {$target} : {$rel['type']}";
            }
        }
        return implode("\n", $lines);
    }

    protected function renderPlantUml(array $entities): string
    {
        $lines = ["@startuml"];
        foreach ($entities as $table => $meta) {
            $lines[] = "entity {$table} {";
            foreach ($meta['columns'] as $col) {
                $lines[] = "  {$col}";
            }
            $lines[] = "}";
        }
        foreach ($entities as $table => $meta) {
            foreach ($meta['relations'] as $rel) {
                $target = (new $rel['related'])->getTable();
                $lines[] = "{$table} -- {$target} : {$rel['type']}";
            }
        }
        $lines[] = "@enduml";
        return implode("\n", $lines);
    }

    protected function renderGraphviz(array $entities): string
    {
        $lines = ["digraph ERD {"];
        foreach ($entities as $table => $meta) {
            $lines[] = "  {$table} [label=\"{$table}\"]";
        }
        foreach ($entities as $table => $meta) {
            foreach ($meta['relations'] as $rel) {
                $target = (new $rel['related'])->getTable();
                $lines[] = "  {$table} -> {$target} [label=\"{$rel['type']}\"]";
            }
        }
        $lines[] = "}";
        return implode("\n", $lines);
    }
}
