<?php

declare(strict_types=1);

/**
 * Drongo ERP — Module boundary validator.
 *
 * Enforces:
 *  1. No circular dependencies between modules.
 *  2. Modules only depend on declared modules in their composer.json `extra.drongo.module.dependsOn`.
 *  3. No module imports another module's `Internal\` namespace.
 *
 * Run:  php tools/validate-module-boundaries.php
 * Exit codes: 0 = OK, 1 = violation found.
 */

$root = __DIR__ . '/..';
$violations = [];

// --- 1. Build module graph from composer.json extra.drongo.module ---
$moduleGraph = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/modules', RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getBasename() !== 'composer.json') {
        continue;
    }
    $dir = dirname($file->getPathname());
    $composer = json_decode(file_get_contents($file->getPathname()), true);
    $meta = $composer['extra']['drongo']['module'] ?? null;
    if (!$meta) {
        continue;
    }
    $name = $meta['name'];
    $moduleGraph[$name] = [
        'path' => $dir,
        'dependsOn' => $meta['dependsOn'] ?? [],
        'namespace' => $meta['namespacePrefix'] ?? '',
    ];
}

// --- 2. Cycle detection (DFS) ---
$WHITE = 0; $GRAY = 1; $BLACK = 2;
$color = array_fill_keys(array_keys($moduleGraph), $WHITE);

function dfs(string $node, array &$graph, array &$color, array &$stack, array &$violations): void {
    global $WHITE, $GRAY, $BLACK;
    $color[$node] = $GRAY;
    $stack[] = $node;

    foreach (($graph[$node]['dependsOn'] ?? []) as $dep) {
        if (!isset($graph[$dep])) {
            $violations[] = "Module '$node' depends on undeclared module '$dep'.";
            continue;
        }
        if ($color[$dep] === $GRAY) {
            $cycle = array_merge(array_slice($stack, array_search($dep, $stack)), [$dep]);
            $violations[] = 'CYCLE DETECTED: ' . implode(' -> ', $cycle);
        } elseif ($color[$dep] === $WHITE) {
            dfs($dep, $graph, $color, $stack, $violations);
        }
    }

    array_pop($stack);
    $color[$node] = $BLACK;
}

foreach ($moduleGraph as $node => $data) {
    if ($color[$node] === $WHITE) {
        dfs($node, $moduleGraph, $color, [], $violations);
    }
}

// --- 3. Internal namespace leakage check ---
foreach ($moduleGraph as $consumer => $data) {
    $consumerPath = realpath($data['path']);
    if (!$consumerPath) continue;

    $srcIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($consumerPath . '/src', RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($srcIterator as $phpFile) {
        if ($phpFile->getExtension() !== 'php') continue;
        $content = file_get_contents($phpFile->getPathname());

        foreach ($moduleGraph as $other => $otherData) {
            if ($other === $consumer) continue;
            $internalNs = $otherData['namespace'] . '\\Internal\\';
            if (strpos($content, $internalNs) !== false) {
                $violations[] = "Module '$consumer' accesses internals of module '$other' ({$internalNs}). Use its public API instead.";
            }
        }
    }
}

// --- Report ---
if ($violations) {
    echo "MODULE BOUNDARY VIOLATIONS:\n";
    foreach ($violations as $v) {
        echo "  ✖ $v\n";
    }
    exit(1);
}

echo "✔ Module boundaries valid — no cycles, no internal leakage.\n";
exit(0);