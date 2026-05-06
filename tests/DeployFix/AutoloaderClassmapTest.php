<?php

namespace Tests\DeployFix;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Bug Condition Exploration Test — Property 1
 *
 * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5
 *
 * This test checks that the Composer classmap includes ALL App\ classes.
 *
 * EXPECTED OUTCOME ON UNFIXED CODE: FAIL
 *   — classmap was generated before app/ files were fully available (nixpacks bug),
 *     so several App\ classes are missing from the classmap.
 *
 * EXPECTED OUTCOME AFTER FIX: PASS
 *   — classmap is generated after all app/ files are present, so all classes are found.
 *
 * Bug condition (from bugfix.md):
 *   isBugCondition(X) = X.composerDumpRunBeforeAppFilesCopied = TRUE
 *                    OR X.startupScriptRegeneratesAutoloader = FALSE
 */
class AutoloaderClassmapTest extends TestCase
{
    /**
     * All App\ classes that MUST be present in the Composer classmap.
     *
     * @return array<string, array{string}>
     */
    public static function appClassProvider(): array
    {
        return [
            'App\\Models\\Siswa'                                    => ['App\\Models\\Siswa'],
            'App\\Models\\Jurusan'                                  => ['App\\Models\\Jurusan'],
            'App\\Models\\User'                                     => ['App\\Models\\User'],
            'App\\Http\\Controllers\\Authcontroller'                => ['App\\Http\\Controllers\\Authcontroller'],
            'App\\Http\\Controllers\\SiswaController'               => ['App\\Http\\Controllers\\SiswaController'],
            'App\\Http\\Controllers\\JurusanController'             => ['App\\Http\\Controllers\\JurusanController'],
            'App\\Http\\Controllers\\PresensiController'            => ['App\\Http\\Controllers\\PresensiController'],
            'App\\Http\\Controllers\\LaporanController'             => ['App\\Http\\Controllers\\LaporanController'],
            'App\\Http\\Controllers\\FaceRecognitionController'     => ['App\\Http\\Controllers\\FaceRecognitionController'],
            'App\\Http\\Controllers\\DashboardController'           => ['App\\Http\\Controllers\\DashboardController'],
            'App\\Http\\Middleware\\Authenticate'                   => ['App\\Http\\Middleware\\Authenticate'],
            'App\\Http\\Middleware\\RedirectIfAuthenticated'        => ['App\\Http\\Middleware\\RedirectIfAuthenticated'],
            'App\\Providers\\AppServiceProvider'                    => ['App\\Providers\\AppServiceProvider'],
            'App\\Traits\\HasversineDistance'                       => ['App\\Traits\\HasversineDistance'],
        ];
    }

    /**
     * Load the Composer classmap from vendor/composer/autoload_classmap.php.
     *
     * @return array<string, string>
     */
    private function loadClassmap(): array
    {
        $classmapPath = dirname(__DIR__, 2) . '/vendor/composer/autoload_classmap.php';

        $this->assertFileExists(
            $classmapPath,
            'vendor/composer/autoload_classmap.php must exist — run `composer install` first.'
        );

        $classmap = require $classmapPath;

        $this->assertIsArray(
            $classmap,
            'autoload_classmap.php must return an array.'
        );

        return $classmap;
    }

    /**
     * Property 1: Bug Condition — Autoloader Classmap Missing App Classes
     *
     * Asserts that each known App\ class is present as a key in the Composer classmap.
     *
     * This test FAILS when the classmap was generated before app/ files were available
     * (the bug condition), and PASSES after the fix is applied.
     *
     * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5
     *
     * @dataProvider appClassProvider
     */
    #[DataProvider('appClassProvider')]
    public function testAppClassIsPresentInClassmap(string $className): void
    {
        $classmap = $this->loadClassmap();

        $this->assertArrayHasKey(
            $className,
            $classmap,
            sprintf(
                'COUNTEREXAMPLE: Class "%s" is NOT present in vendor/composer/autoload_classmap.php. ' .
                'This confirms the bug: composer install --optimize-autoloader was run before ' .
                'app/ files were available in the build context (nixpacks install phase), ' .
                'so the classmap is incomplete. Any Laravel request using this class will ' .
                'result in HTTP 500 "Class not found".',
                $className
            )
        );
    }

    /**
     * Sanity check: the classmap file itself must be readable and non-empty.
     *
     * Validates: Requirements 1.3
     */
    public function testClassmapFileIsReadableAndNonEmpty(): void
    {
        $classmap = $this->loadClassmap();

        $this->assertNotEmpty(
            $classmap,
            'autoload_classmap.php returned an empty array — classmap was not generated correctly.'
        );
    }

    /**
     * Aggregate test: collect ALL missing App\ classes in one assertion.
     *
     * This provides a complete list of counterexamples in a single test run,
     * making it easy to see the full scope of the bug at once.
     *
     * Validates: Requirements 1.3, 1.5
     */
    public function testAllAppClassesArePresentInClassmap(): void
    {
        $classmap = $this->loadClassmap();

        $expectedClasses = array_keys(self::appClassProvider());

        $missingClasses = array_filter(
            $expectedClasses,
            fn(string $class) => !array_key_exists($class, $classmap)
        );

        $this->assertEmpty(
            $missingClasses,
            sprintf(
                "COUNTEREXAMPLES — The following App\\ classes are MISSING from the classmap:\n%s\n\n" .
                "Root cause: `composer install --optimize-autoloader` ran in the nixpacks `install` phase " .
                "BEFORE app/ files were copied into the build context. " .
                "The classmap was generated without seeing any App\\ classes.\n" .
                "Fix required: move `composer install` to the `build` phase in nixpacks.toml, " .
                "and add `composer dump-autoload --optimize` as step 1 in railway-start.sh.",
                implode("\n", array_map(fn($c) => "  - $c", $missingClasses))
            )
        );
    }
}
