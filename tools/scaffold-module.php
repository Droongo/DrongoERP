<?php

declare(strict_types=1);

/**
 * Drongo ERP — Module scaffolding helper.
 *
 * Usage: php tools\scaffold-module.php <ModuleName> [owner-team]
 *
 * Example: php tools\scaffold-module.php accounting @drongo-org/accounting-module
 */

if ($argc < 2) {
    echo "Usage: php $argv[0] <ModuleName> [owner-team]\n";
    exit(1);
}

$moduleName = Str::studly($argv[1]);
$moduleKey  = Str::snake($moduleName);
$ownerTeam  = $argv[2] ?? '@drongo-org/platform-team';
$root       = __DIR__ . '/..';
$moduleDir  = "$root/modules/$moduleKey";

if (is_dir($moduleDir)) {
    echo "Module $moduleKey already exists.\n";
    exit(1);
}

// Create directories
foreach (["src", "tests", "config", "migrations"] as $d) {
    @mkdir("$moduleDir/$d", 0777, true);
}

$ns = "Drongo\\Module\\$moduleName";

// composer.json
$composer = <<<JSON
{
  "name": "drongo/module-$moduleKey",
  "description": "Drongo ERP — $moduleName module",
  "type": "library",
  "license": "proprietary",
  "require": {
    "php": "^8.1",
    "drongo/package-core": "self.version"
  },
  "autoload": {
    "psr-4": {
      "Drongo\\Module\\$moduleName\\\\": "src/"
    }
  },
  "extra": {
    "drongo": {
      "module": {
        "name": "$moduleKey",
        "displayName": "$moduleName",
        "ownerTeam": "$ownerTeam",
        "dependsOn": [],
        "dbSchema": "${moduleKey}_",
        "namespacePrefix": "$ns"
      }
    }
  }
}
JSON;
file_put_contents("$moduleDir/composer.json", $composer);

// Module entry point
$modulePhp = <<<PHP
<?php

declare(strict_types=1);

namespace Drongo\\Module\\$moduleName;

use Drongo\Core\Kernel;

class ${moduleName}Module
{
    public static function register(Kernel \$kernel): void
    {
        \$kernel->registerModule('$moduleKey', [
            'displayName' => '$moduleName',
            'ownerTeam' => '$ownerTeam',
            'dbSchema' => '${moduleKey}_',
        ]);
    }

    public static function boot(Kernel \$kernel): void
    {
        // TODO: register routes, listeners, migrations
    }
}
PHP;
file_put_contents("$moduleDir/src/${moduleName}Module.php", $modulePhp);

// Test
file_put_contents("$moduleDir/tests/${moduleName}Test.php", <<<PHP
<?php

declare(strict_types=1);

namespace Drongo\Module\$moduleName\Tests;

use PHPUnit\Framework\TestCase;

class ${moduleName}Test extends TestCase
{
    public function testPlaceholder(): void
    {
        \$this->assertTrue(true);
    }
}
PHP);

echo "✔ Scaffolded module '$moduleKey' at modules/$moduleKey/\n";
echo "✔ Remember to:\n";
echo "   1. Add modules/$moduleKey to root composer.json merge-plugin.include\n";
echo "   2. Register the module in apps/*/src/bootstrap.php\n";
echo "   3. Add /modules/$moduleKey/ to .github/CODEOWNERS\n";
echo "   4. Commit and open a PR\n";

class Str
{
    public static function studly(string $v): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $v)));
    }

    public static function snake(string $v): string
    {
        return strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', str_replace('-', '_', $v)));
    }
}