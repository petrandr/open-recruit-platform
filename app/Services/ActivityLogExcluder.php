<?php

namespace App\Services;

class ActivityLogExcluder
{
    protected array $excludedModels;

    public function __construct()
    {
        $this->excludedModels = config('activitylog.excluded_models', []);
    }

    public function shouldExclude($model): bool
    {
        foreach ($this->excludedModels as $excludedModel) {
            if ($model instanceof $excludedModel) {
                return true;
            }
        }

        return false;
    }
}
