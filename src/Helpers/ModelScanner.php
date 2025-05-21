<?php
// src/Helpers/ModelScanner.php

namespace Akbarhossainr\ErdGenerator\Helpers;

use Illuminate\Filesystem\Filesystem;
use Mockery;

class ModelScanner
{
    protected $filesystem;
    protected $directory;

    public function __construct($directory = null, Filesystem $filesystem = null)
    {
        $this->directory = $directory ?: app_path('Models');
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function getModels()
    {
        $models = [];
        if (!$this->filesystem->exists($this->directory)) {
            return $models;
        }
        foreach ($this->filesystem->allFiles($this->directory) as $file) {
            $relativePath = $file->getRelativePathname();
            $class = $this->getClassFromFile($relativePath);
            if ($class) {
                $models[] = $class;
            }
        }
        return $models;
    }

    protected function getClassFromFile($relativePath)
    {
        $class = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);
        if (class_exists($class)) {
            return $class;
        }
        return null;
    }
}
