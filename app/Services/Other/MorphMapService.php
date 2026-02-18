<?php

namespace App\Services\Other;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class MorphMapService
{
    public static function initMorphMap(): void
    {
        $modelFiles = HelpService::getModelFiles(app_path('Models'));

        $mapping = [];
        foreach ($modelFiles as $classFilePath) {
            $className = explode('app/Models/', $classFilePath);
            $className = str_replace('/', '\\', end($className));
            $className = str_replace('.php', '', $className);
            $key = explode('\\', $className);
            $key = Str::snake(end($key));
            $mapping[$key] = "App\\Models\\{$className}";
        }

        // Set the morph map
        Relation::morphMap($mapping);
    }
}
