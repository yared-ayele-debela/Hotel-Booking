<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class CacheReset extends Command
{
    protected $signature = 'permissions:cache-reset';

    protected $description = 'Reset the permissions cache';

    public function handle()
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $cacheExists = $permissionRegistrar->getCacheRepository()->has($permissionRegistrar->cacheKey);

        if ($permissionRegistrar->forgetCachedPermissions()) {
            $this->info('Permission cache flushed.');
        } elseif ($cacheExists) {
            $this->error('Unable to flush cache.');
        }
    }
}
