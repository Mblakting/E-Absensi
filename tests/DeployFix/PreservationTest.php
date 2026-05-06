<?php

namespace Tests\DeployFix;

use PHPUnit\Framework\TestCase;

/**
 * Preservation Property Tests — Property 2
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8
 *
 * These tests observe behaviors on UNFIXED code that are NOT broken by the autoloader bug.
 * They establish the baseline that must be preserved after the fix is applied.
 *
 * EXPECTED OUTCOME ON UNFIXED CODE: PASS
 *   — these behaviors are not affected by the classmap bug, so they pass before and after fix.
 *
 * EXPECTED OUTCOME AFTER FIX: PASS (no regressions)
 *
 * Preservation property (from bugfix.md):
 *   FOR ALL X WHERE NOT isBugCondition(X) DO
 *     ASSERT F(X) = F'(X)
 *   END FOR
 */
class PreservationTest extends TestCase
{
    /** Absolute path to the project root. */
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        // tests/DeployFix/ → two levels up → project root
        $this->projectRoot = dirname(__DIR__, 2);
    }

    // -------------------------------------------------------------------------
    // 1. Startup script structure preservation (Requirements 3.4, 3.5, 3.6)
    // -------------------------------------------------------------------------

    /**
     * railway-start.sh must exist and be executable.
     *
     * On Unix/Linux (the Railway deployment target), the file must have the
     * executable bit set. On Windows (local dev), is_executable() always
     * returns false for non-.exe files, so we skip that check and only
     * verify the file exists and is readable.
     *
     * Validates: Requirements 3.4, 3.5, 3.6
     */
    public function testRailwayStartShExistsAndIsExecutable(): void
    {
        $path = $this->projectRoot . '/railway-start.sh';

        $this->assertFileExists(
            $path,
            'railway-start.sh must exist in the project root.'
        );

        $this->assertIsReadable(
            $path,
            'railway-start.sh must be readable.'
        );

        // On Unix/Linux (the actual deployment target), also verify the
        // executable bit. Skip on Windows where is_executable() is unreliable
        // for shell scripts.
        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertTrue(
                is_executable($path),
                'railway-start.sh must be executable (chmod +x) on Unix/Linux.'
            );
        }
    }

    /**
     * railway-start.sh must contain `php artisan migrate --force`.
     *
     * Validates: Requirement 3.4
     */
    public function testRailwayStartShContainsMigrateCommand(): void
    {
        $content = $this->readStartupScript();

        $this->assertStringContainsString(
            'php artisan migrate --force',
            $content,
            'railway-start.sh must contain "php artisan migrate --force" (Requirement 3.4).'
        );
    }

    /**
     * railway-start.sh must contain `php artisan db:seed --force`.
     *
     * Validates: Requirement 3.5
     */
    public function testRailwayStartShContainsSeedCommand(): void
    {
        $content = $this->readStartupScript();

        $this->assertStringContainsString(
            'php artisan db:seed --force',
            $content,
            'railway-start.sh must contain "php artisan db:seed --force" (Requirement 3.5).'
        );
    }

    /**
     * railway-start.sh must contain `php artisan storage:link --force`.
     *
     * Validates: Requirement 3.6
     */
    public function testRailwayStartShContainsStorageLinkCommand(): void
    {
        $content = $this->readStartupScript();

        $this->assertStringContainsString(
            'php artisan storage:link --force',
            $content,
            'railway-start.sh must contain "php artisan storage:link --force" (Requirement 3.6).'
        );
    }

    /**
     * railway-start.sh must contain `chmod -R 775 storage bootstrap/cache`.
     *
     * Validates: Requirements 3.4, 3.6
     */
    public function testRailwayStartShContainsChmodCommand(): void
    {
        $content = $this->readStartupScript();

        $this->assertStringContainsString(
            'chmod -R 775 storage bootstrap/cache',
            $content,
            'railway-start.sh must contain "chmod -R 775 storage bootstrap/cache".'
        );
    }

    /**
     * The order of commands in railway-start.sh must be:
     *   migrate → seed → storage:link
     *
     * Validates: Requirements 3.4, 3.5, 3.6
     */
    public function testRailwayStartShCommandOrderIsCorrect(): void
    {
        $content = $this->readStartupScript();

        $migratePos     = strpos($content, 'php artisan migrate --force');
        $seedPos        = strpos($content, 'php artisan db:seed --force');
        $storageLinkPos = strpos($content, 'php artisan storage:link --force');

        $this->assertNotFalse($migratePos,     'migrate command not found in railway-start.sh');
        $this->assertNotFalse($seedPos,        'db:seed command not found in railway-start.sh');
        $this->assertNotFalse($storageLinkPos, 'storage:link command not found in railway-start.sh');

        $this->assertLessThan(
            $seedPos,
            $migratePos,
            'migrate must appear BEFORE db:seed in railway-start.sh (Requirement 3.4 before 3.5).'
        );

        $this->assertLessThan(
            $storageLinkPos,
            $seedPos,
            'db:seed must appear BEFORE storage:link in railway-start.sh (Requirement 3.5 before 3.6).'
        );
    }

    // -------------------------------------------------------------------------
    // 2. nixpacks.toml structure preservation (Requirements 3.7)
    // -------------------------------------------------------------------------

    /**
     * nixpacks.toml must exist.
     *
     * Validates: Requirement 3.7
     */
    public function testNixpacksTomlExists(): void
    {
        $path = $this->projectRoot . '/nixpacks.toml';

        $this->assertFileExists(
            $path,
            'nixpacks.toml must exist in the project root.'
        );
    }

    /**
     * nixpacks.toml must contain `npm ci`.
     *
     * Validates: Requirement 3.7
     */
    public function testNixpacksTomlContainsNpmCi(): void
    {
        $content = $this->readNixpacksToml();

        $this->assertStringContainsString(
            'npm ci',
            $content,
            'nixpacks.toml must contain "npm ci" to install frontend dependencies.'
        );
    }

    /**
     * nixpacks.toml must contain `npm run build`.
     *
     * Validates: Requirement 3.7
     */
    public function testNixpacksTomlContainsNpmRunBuild(): void
    {
        $content = $this->readNixpacksToml();

        $this->assertStringContainsString(
            'npm run build',
            $content,
            'nixpacks.toml must contain "npm run build" to compile frontend assets.'
        );
    }

    /**
     * nixpacks.toml must have a [phases.install] or [phases.build] section.
     *
     * Validates: Requirement 3.7
     */
    public function testNixpacksTomlHasInstallOrBuildPhase(): void
    {
        $content = $this->readNixpacksToml();

        $hasInstallPhase = str_contains($content, '[phases.install]');
        $hasBuildPhase   = str_contains($content, '[phases.build]');

        $this->assertTrue(
            $hasInstallPhase || $hasBuildPhase,
            'nixpacks.toml must have a [phases.install] or [phases.build] section.'
        );
    }

    // -------------------------------------------------------------------------
    // 3. Artisan command availability (Requirements 3.4, 3.7)
    // -------------------------------------------------------------------------

    /**
     * `php artisan list` must exit with code 0 — Laravel boots successfully.
     *
     * This verifies the app can start up without fatal errors.
     * Does NOT require a running database or HTTP server.
     *
     * Validates: Requirements 3.4, 3.7
     */
    public function testArtisanListExitsWithZero(): void
    {
        $artisan = $this->projectRoot . '/artisan';

        $this->assertFileExists(
            $artisan,
            'artisan file must exist in the project root.'
        );

        $output     = [];
        $returnCode = 0;

        exec(
            sprintf('cd %s && php artisan list 2>&1', escapeshellarg($this->projectRoot)),
            $output,
            $returnCode
        );

        $this->assertSame(
            0,
            $returnCode,
            sprintf(
                '`php artisan list` must exit with code 0 (Laravel boots successfully). ' .
                "Output:\n%s",
                implode("\n", $output)
            )
        );
    }

    // -------------------------------------------------------------------------
    // 4. Config/auth guard preservation (Requirements 3.1, 3.2, 3.3)
    // -------------------------------------------------------------------------

    /**
     * config/auth.php must define a `siswa` guard using App\Models\Siswa as provider model.
     *
     * Validates: Requirements 3.1, 3.8
     */
    public function testAuthConfigHasSiswaGuardWithCorrectModel(): void
    {
        $authConfig = $this->loadAuthConfig();

        // Guard 'siswa' must exist
        $this->assertArrayHasKey(
            'siswa',
            $authConfig['guards'],
            'config/auth.php must define a "siswa" guard (Requirement 3.1).'
        );

        // The provider for 'siswa' guard must exist in providers
        $siswaGuard    = $authConfig['guards']['siswa'];
        $providerName  = $siswaGuard['provider'] ?? null;

        $this->assertNotNull(
            $providerName,
            '"siswa" guard must specify a provider.'
        );

        $this->assertArrayHasKey(
            $providerName,
            $authConfig['providers'],
            sprintf('"siswa" guard references provider "%s" which must be defined.', $providerName)
        );

        // The provider model must be App\Models\Siswa
        $providerModel = $authConfig['providers'][$providerName]['model'] ?? null;

        $this->assertSame(
            'App\\Models\\Siswa',
            $providerModel,
            'The "siswa" guard provider must use App\\Models\\Siswa as the model (Requirement 3.1).'
        );
    }

    /**
     * config/auth.php must define a `user` guard using App\Models\User as provider model.
     *
     * Validates: Requirements 3.1, 3.2
     */
    public function testAuthConfigHasUserGuardWithCorrectModel(): void
    {
        $authConfig = $this->loadAuthConfig();

        // Guard 'user' must exist
        $this->assertArrayHasKey(
            'user',
            $authConfig['guards'],
            'config/auth.php must define a "user" guard (Requirement 3.1).'
        );

        // The provider for 'user' guard must exist in providers
        $userGuard    = $authConfig['guards']['user'];
        $providerName = $userGuard['provider'] ?? null;

        $this->assertNotNull(
            $providerName,
            '"user" guard must specify a provider.'
        );

        $this->assertArrayHasKey(
            $providerName,
            $authConfig['providers'],
            sprintf('"user" guard references provider "%s" which must be defined.', $providerName)
        );

        // The provider model must be App\Models\User
        $providerModel = $authConfig['providers'][$providerName]['model'] ?? null;

        $this->assertSame(
            'App\\Models\\User',
            $providerModel,
            'The "user" guard provider must use App\\Models\\User as the model (Requirement 3.1).'
        );
    }

    // -------------------------------------------------------------------------
    // 5. Bootstrap providers preservation (Requirement 3.7)
    // -------------------------------------------------------------------------

    /**
     * bootstrap/providers.php must register App\Providers\AppServiceProvider.
     *
     * Validates: Requirement 3.7
     */
    public function testBootstrapProvidersRegistersAppServiceProvider(): void
    {
        $providersPath = $this->projectRoot . '/bootstrap/providers.php';

        $this->assertFileExists(
            $providersPath,
            'bootstrap/providers.php must exist.'
        );

        $providers = require $providersPath;

        $this->assertIsArray(
            $providers,
            'bootstrap/providers.php must return an array.'
        );

        $this->assertContains(
            'App\\Providers\\AppServiceProvider',
            $providers,
            'bootstrap/providers.php must register App\\Providers\\AppServiceProvider (Requirement 3.7).'
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function readStartupScript(): string
    {
        $path = $this->projectRoot . '/railway-start.sh';

        $this->assertFileExists($path, 'railway-start.sh must exist.');

        $content = file_get_contents($path);

        $this->assertNotFalse($content, 'Could not read railway-start.sh.');

        return $content;
    }

    private function readNixpacksToml(): string
    {
        $path = $this->projectRoot . '/nixpacks.toml';

        $this->assertFileExists($path, 'nixpacks.toml must exist.');

        $content = file_get_contents($path);

        $this->assertNotFalse($content, 'Could not read nixpacks.toml.');

        return $content;
    }

    /**
     * Load config/auth.php and return its array, resolving ::class constants.
     *
     * @return array<string, mixed>
     */
    private function loadAuthConfig(): array
    {
        $path = $this->projectRoot . '/config/auth.php';

        $this->assertFileExists($path, 'config/auth.php must exist.');

        // The file uses App\Models\User::class etc., so we need the autoloader.
        // vendor/autoload.php is already loaded by PHPUnit bootstrap.
        $config = require $path;

        $this->assertIsArray($config, 'config/auth.php must return an array.');

        return $config;
    }
}
