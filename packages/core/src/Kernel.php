<?php

declare(strict_types=1);

namespace Drongo\Core;

/**
 * Drongo ERP Kernel.
 *
 * The kernel is the only shared entry point. Every module registers itself
 * here. Modules must never reach into another module's internals — they
 * communicate through the Kernel's event bus and public service interfaces.
 */
class Kernel
{
    protected array $modules = [];
    protected array $services = [];
    protected array $eventListeners = [];

    public function registerModule(string $moduleName, array $metadata): void
    {
        $this->modules[$moduleName] = $metadata;
    }

    public function getModule(string $moduleName): ?array
    {
        return $this->modules[$moduleName] ?? null;
    }

    public function listModules(): array
    {
        return array_keys($this->modules);
    }

    public function setService(string $name, object $service): void
    {
        $this->services[$name] = $service;
    }

    public function getService(string $name): ?object
    {
        return $this->services[$name] ?? null;
    }

    public function on(string $event, callable $listener): void
    {
        $this->eventListeners[$event][] = $listener;
    }

    public function emit(string $event, array $payload = []): void
    {
        foreach ($this->eventListeners[$event] ?? [] as $listener) {
            $listener($payload);
        }
    }
}