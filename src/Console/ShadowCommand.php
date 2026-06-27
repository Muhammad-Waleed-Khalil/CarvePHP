<?php

declare(strict_types=1);

namespace Carve\Console;

use Carve\Shadow\ShadowTrafficManager;
use Illuminate\Console\Command;

final class ShadowCommand extends Command
{
    protected $signature = 'carve:shadow
        {action : enable|disable|report}
        {--route= : Route pattern to shadow}
        {--target= : Target service URL}
        {--last=1000 : Number of recent shadow results to report}';

    protected $description = 'Manage shadow traffic configuration and reporting';

    public function handle(): int
    {
        $action = $this->argument('action');
        $manager = app(ShadowTrafficManager::class);

        match ($action) {
            'enable' => $this->enableShadow($manager),
            'disable' => $this->disableShadow($manager),
            'report' => $this->showReport($manager),
            default => $this->error("Unknown action: {$action}. Use enable, disable, or report."),
        };

        return self::SUCCESS;
    }

    private function enableShadow(ShadowTrafficManager $manager): void
    {
        $route = $this->option('route');
        $target = $this->option('target');

        if (! $route || ! $target) {
            $this->error('Both --route and --target are required for enable');

            return;
        }

        $manager->enable($route, $target);
        $this->info("Shadow enabled for {$route} -> {$target}");
    }

    private function disableShadow(ShadowTrafficManager $manager): void
    {
        $route = $this->option('route');

        if (! $route) {
            $this->error('--route is required for disable');

            return;
        }

        $manager->disable($route);
        $this->info("Shadow disabled for {$route}");
    }

    private function showReport(ShadowTrafficManager $manager): void
    {
        $report = $manager->report((int) $this->option('last'));
        $this->line($report);
    }
}
