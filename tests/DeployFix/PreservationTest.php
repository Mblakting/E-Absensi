<?php

namespace Tests\DeployFix;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;

/**
 * Preservation Property Tests — Property 2
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8**
 *
 * These tests observe behaviors on UNFIXED code for non-buggy inputs (application features 
 * that don't involve deployment or authentication). They establish the baseline that must 
 * be preserved after the deployment and authentication fixes are applied.
 *
 * **IMPORTANT**: Follow observation-first methodology
 * - Observe behavior on UNFIXED code for non-buggy inputs
 * - Test Laravel application features that continue to work (CRUD operations, presensi, laporan)
 * - Test database operations and migrations function correctly
 * - Test static assets (CSS, JS, images) are served properly
 * - Test UI navigation and existing workflows remain unchanged
 *
 * **EXPECTED OUTCOME ON UNFIXED CODE: PASS**
 *   — these behaviors are not affected by deployment or authentication bugs
 *
 * **EXPECTED OUTCOME AFTER FIX: PASS (no regressions)**
 *
 * Preservation property (from bugfix.md):
 *   FOR ALL X WHERE NOT isBugCondition(X) DO
 *     ASSERT F(X) = F'(X)
 *   END FOR
 */
class PreservationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** Absolute path to the project root. */
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        // tests/DeployFix/ → two levels up → project root
        $this->projectRoot = dirname(__DIR__, 2);
        
        // Seed basic data for testing
        $this->seedTestData();
    }

    /**
     * Seed basic test data for preservation tests
     */
    private function seedTestData(): void
    {
        // Create test jurusan
        Jurusan::create([
            'kode_jurusan' => 'TKJ',
            'nama_jurusan' => 'Teknik Komputer Jaringan'
        ]);

        Jurusan::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak'
        ]);

        // Create test admin user
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123')
        ]);

        // Create test siswa
        Siswa::create([
            'nisn' => '1234567890',
            'nama_lengkap' => 'Test Student',
            'kelas' => 'XII TKJ 1',
            'no_hp' => '081234567890',
            'kode_jurusan' => 'TKJ',
            'password' => Hash::make('12345')
        ]);

        Siswa::create([
            'nisn' => '0987654321',
            'nama_lengkap' => 'Another Student',
            'kelas' => 'XI RPL 2',
            'no_hp' => '089876543210',
            'kode_jurusan' => 'RPL',
            'password' => Hash::make('12345')
        ]);
    }

    // -------------------------------------------------------------------------
    // 1. Database Operations Preservation (Requirements 3.1, 3.2, 3.3)
    // -------------------------------------------------------------------------

    /**
     * Property: Database CRUD operations for Siswa model work correctly
     * 
     * **Validates: Requirements 3.1, 3.8**
     */
    public function testSiswaCrudOperationsWork(): void
    {
        // Test Create
        $newSiswa = Siswa::create([
            'nisn' => '1111111111',
            'nama_lengkap' => 'New Test Student',
            'kelas' => 'X TKJ 1',
            'no_hp' => '081111111111',
            'kode_jurusan' => 'TKJ',
            'password' => Hash::make('12345')
        ]);

        $this->assertInstanceOf(Siswa::class, $newSiswa);
        $this->assertEquals('1111111111', $newSiswa->nisn);
        $this->assertEquals('New Test Student', $newSiswa->nama_lengkap);

        // Test Read
        $foundSiswa = Siswa::where('nisn', '1111111111')->first();
        $this->assertNotNull($foundSiswa);
        $this->assertEquals('New Test Student', $foundSiswa->nama_lengkap);

        // Test Update
        $foundSiswa->update(['nama_lengkap' => 'Updated Student Name']);
        $updatedSiswa = Siswa::find('1111111111');
        $this->assertEquals('Updated Student Name', $updatedSiswa->nama_lengkap);

        // Test Delete
        $foundSiswa->delete();
        $deletedSiswa = Siswa::find('1111111111');
        $this->assertNull($deletedSiswa);
    }

    /**
     * Property: Database CRUD operations for Jurusan model work correctly
     * 
     * **Validates: Requirements 3.1, 3.2**
     */
    public function testJurusanCrudOperationsWork(): void
    {
        // Test Create
        $newJurusan = Jurusan::create([
            'kode_jurusan' => 'MM',
            'nama_jurusan' => 'Multimedia'
        ]);

        $this->assertInstanceOf(Jurusan::class, $newJurusan);
        $this->assertEquals('MM', $newJurusan->kode_jurusan);
        $this->assertEquals('Multimedia', $newJurusan->nama_jurusan);

        // Test Read
        $foundJurusan = Jurusan::where('kode_jurusan', 'MM')->first();
        $this->assertNotNull($foundJurusan);
        $this->assertEquals('Multimedia', $foundJurusan->nama_jurusan);

        // Test Update
        $foundJurusan->update(['nama_jurusan' => 'Multimedia Updated']);
        $updatedJurusan = Jurusan::find('MM');
        $this->assertEquals('Multimedia Updated', $updatedJurusan->nama_jurusan);

        // Test Delete (only if no siswa references it)
        $foundJurusan->delete();
        $deletedJurusan = Jurusan::find('MM');
        $this->assertNull($deletedJurusan);
    }

    /**
     * Property: Database relationships between Siswa and Jurusan work correctly
     * 
     * **Validates: Requirements 3.1, 3.2**
     */
    public function testSiswaJurusanRelationshipWorks(): void
    {
        $siswa = Siswa::where('nisn', '1234567890')->first();
        $this->assertNotNull($siswa);

        // Test relationship
        $jurusan = $siswa->jurusan;
        $this->assertInstanceOf(Jurusan::class, $jurusan);
        $this->assertEquals('TKJ', $jurusan->kode_jurusan);
        $this->assertEquals('Teknik Komputer Jaringan', $jurusan->nama_jurusan);

        // Test reverse relationship
        $tkjJurusan = Jurusan::where('kode_jurusan', 'TKJ')->first();
        $siswaList = $tkjJurusan->siswa;
        $this->assertGreaterThan(0, $siswaList->count());
        $this->assertTrue($siswaList->contains('nisn', '1234567890'));
    }

    /**
     * Property: Presensi table operations work correctly
     * 
     * **Validates: Requirements 3.3, 3.4**
     */
    public function testPresensiOperationsWork(): void
    {
        $today = now()->toDateString();
        
        // Test Create presensi record
        DB::table('presensi')->insert([
            'nisn' => '1234567890',
            'tgl_presensi' => $today,
            'jam_in' => '07:30:00',
            'foto_in' => 'test_foto_in.jpg',
            'lokasi_in' => '-6.200000,106.816666',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test Read presensi record
        $presensi = DB::table('presensi')
            ->where('nisn', '1234567890')
            ->where('tgl_presensi', $today)
            ->first();

        $this->assertNotNull($presensi);
        $this->assertEquals('1234567890', $presensi->nisn);
        $this->assertEquals('07:30:00', $presensi->jam_in);

        // Test Update presensi (jam_out)
        DB::table('presensi')
            ->where('nisn', '1234567890')
            ->where('tgl_presensi', $today)
            ->update([
                'jam_out' => '15:30:00',
                'foto_out' => 'test_foto_out.jpg',
                'lokasi_out' => '-6.200000,106.816666',
                'updated_at' => now(),
            ]);

        $updatedPresensi = DB::table('presensi')
            ->where('nisn', '1234567890')
            ->where('tgl_presensi', $today)
            ->first();

        $this->assertEquals('15:30:00', $updatedPresensi->jam_out);
        $this->assertEquals('test_foto_out.jpg', $updatedPresensi->foto_out);
    }

    /**
     * Property: Pengajuan izin table operations work correctly
     * 
     * **Validates: Requirements 3.3, 3.4**
     */
    public function testPengajuanIzinOperationsWork(): void
    {
        $today = now()->toDateString();
        
        // Test Create pengajuan izin
        DB::table('pengajuan_izin')->insert([
            'nisn' => '1234567890',
            'tgl_izin' => $today,
            'status' => 'i', // izin
            'keterangan' => 'Sakit demam',
            'status_approved' => 0, // pending
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test Read pengajuan izin
        $izin = DB::table('pengajuan_izin')
            ->where('nisn', '1234567890')
            ->where('tgl_izin', $today)
            ->first();

        $this->assertNotNull($izin);
        $this->assertEquals('1234567890', $izin->nisn);
        $this->assertEquals('i', $izin->status);
        $this->assertEquals('Sakit demam', $izin->keterangan);
        $this->assertEquals(0, $izin->status_approved);

        // Test Update pengajuan izin (approve)
        DB::table('pengajuan_izin')
            ->where('id', $izin->id)
            ->update([
                'status_approved' => 1, // approved
                'updated_at' => now(),
            ]);

        $approvedIzin = DB::table('pengajuan_izin')->find($izin->id);
        $this->assertEquals(1, $approvedIzin->status_approved);
    }

    // -------------------------------------------------------------------------
    // 2. Laravel Application Features Preservation (Requirements 3.5, 3.6, 3.7)
    // -------------------------------------------------------------------------

    /**
     * Property: Laravel configuration loading works correctly
     * 
     * **Validates: Requirements 3.5, 3.7**
     */
    public function testLaravelConfigurationLoading(): void
    {
        // Test app config
        $appName = config('app.name');
        $this->assertNotNull($appName);
        
        // Test database config
        $dbConnection = config('database.default');
        $this->assertNotNull($dbConnection);
        
        // Test auth config
        $authGuards = config('auth.guards');
        $this->assertIsArray($authGuards);
        $this->assertArrayHasKey('siswa', $authGuards);
        $this->assertArrayHasKey('user', $authGuards);
        
        // Test school config (custom config)
        $schoolConfig = config('school');
        $this->assertNotNull($schoolConfig);
    }

    /**
     * Property: Laravel service providers boot correctly
     * 
     * **Validates: Requirements 3.7**
     */
    public function testServiceProvidersBootCorrectly(): void
    {
        // Test that app service provider is registered
        $providers = app()->getLoadedProviders();
        $this->assertArrayHasKey('App\\Providers\\AppServiceProvider', $providers);
        
        // Test that core Laravel services are available
        $this->assertTrue(app()->bound('db'));
        $this->assertTrue(app()->bound('auth'));
        $this->assertTrue(app()->bound('session'));
        $this->assertTrue(app()->bound('view'));
    }

    /**
     * Property: Laravel middleware stack works correctly
     * 
     * **Validates: Requirements 3.6, 3.7**
     */
    public function testMiddlewareStackWorks(): void
    {
        // Test that middleware classes exist and can be instantiated
        $authenticateMiddleware = app()->make('App\\Http\\Middleware\\Authenticate');
        $this->assertNotNull($authenticateMiddleware);
        
        $redirectIfAuthenticatedMiddleware = app()->make('App\\Http\\Middleware\\RedirectIfAuthenticated');
        $this->assertNotNull($redirectIfAuthenticatedMiddleware);
    }

    // -------------------------------------------------------------------------
    // 3. Static Assets and Frontend Preservation (Requirements 3.6, 3.8)
    // -------------------------------------------------------------------------

    /**
     * Property: Static CSS assets exist and are accessible
     * 
     * **Validates: Requirements 3.6, 3.8**
     */
    public function testStaticCssAssetsExist(): void
    {
        $cssPath = public_path('assets/css/style.css');
        $this->assertFileExists($cssPath, 'Main CSS file must exist');
        
        $bootstrapCssPath = public_path('assets/css/inc/bootstrap');
        $this->assertDirectoryExists($bootstrapCssPath, 'Bootstrap CSS directory must exist');
    }

    /**
     * Property: Static JavaScript assets exist and are accessible
     * 
     * **Validates: Requirements 3.6, 3.8**
     */
    public function testStaticJavaScriptAssetsExist(): void
    {
        $jsPath = public_path('assets/js/base.js');
        $this->assertFileExists($jsPath, 'Main JS file must exist');
        
        $jqueryPath = public_path('assets/js/lib/jquery-3.4.1.min.js');
        $this->assertFileExists($jqueryPath, 'jQuery library must exist');
        
        $bootstrapJsPath = public_path('assets/js/lib/bootstrap.min.js');
        $this->assertFileExists($bootstrapJsPath, 'Bootstrap JS library must exist');
    }

    /**
     * Property: Static image assets exist and are accessible
     * 
     * **Validates: Requirements 3.6, 3.8**
     */
    public function testStaticImageAssetsExist(): void
    {
        $logoPath = public_path('assets/img/logo.png');
        $this->assertFileExists($logoPath, 'Logo image must exist');
        
        $faviconPath = public_path('assets/img/favicon.png');
        $this->assertFileExists($faviconPath, 'Favicon must exist');
        
        $kosongPath = public_path('assets/img/kosong.jpg');
        $this->assertFileExists($kosongPath, 'Default empty image must exist');
        
        // Test icon directory
        $iconDir = public_path('assets/img/icon');
        $this->assertDirectoryExists($iconDir, 'Icon directory must exist');
    }

    // -------------------------------------------------------------------------
    // 4. Laravel Artisan Commands Preservation (Requirements 3.4, 3.5)
    // -------------------------------------------------------------------------

    /**
     * Property: Laravel artisan commands work correctly
     * 
     * **Validates: Requirements 3.4, 3.5**
     */
    public function testArtisanCommandsWork(): void
    {
        // Test artisan list command
        $exitCode = \Artisan::call('list');
        $this->assertEquals(0, $exitCode, 'Artisan list command should work');
        
        // Test route list command
        $exitCode = \Artisan::call('route:list');
        $this->assertEquals(0, $exitCode, 'Artisan route:list command should work');
        
        // Test config cache command
        $exitCode = \Artisan::call('config:cache');
        $this->assertEquals(0, $exitCode, 'Artisan config:cache command should work');
    }

    // -------------------------------------------------------------------------
    // 5. UI Navigation and Workflow Preservation (Requirements 3.7, 3.8)
    // -------------------------------------------------------------------------

    /**
     * Property: Route definitions remain unchanged
     * 
     * **Validates: Requirements 3.7, 3.8**
     */
    public function testRouteDefinitionsRemainUnchanged(): void
    {
        $router = app('router');
        $routes = $router->getRoutes();
        
        // Test that key routes exist
        $routeNames = [];
        foreach ($routes as $route) {
            if ($route->getName()) {
                $routeNames[] = $route->getName();
            }
        }
        
        // Essential student routes
        $this->assertContains('login', $routeNames);
        $this->assertContains('dashboard', $routeNames);
        $this->assertContains('presensi.create', $routeNames);
        $this->assertContains('presensi.store', $routeNames);
        $this->assertContains('presensi.histori', $routeNames);
        
        // Essential admin routes
        $this->assertContains('loginadmin', $routeNames);
        $this->assertContains('dashboardadmin', $routeNames);
        $this->assertContains('siswa.index', $routeNames);
        $this->assertContains('presensi.monitoring', $routeNames);
        $this->assertContains('laporan.index', $routeNames);
    }

    /**
     * Property: Controller classes can be instantiated correctly
     * 
     * **Validates: Requirements 3.7, 3.8**
     */
    public function testControllerClassesCanBeInstantiated(): void
    {
        // Test that all main controllers can be instantiated
        $dashboardController = app()->make('App\\Http\\Controllers\\DashboardController');
        $this->assertNotNull($dashboardController);
        
        $presensiController = app()->make('App\\Http\\Controllers\\PresensiController');
        $this->assertNotNull($presensiController);
        
        $siswaController = app()->make('App\\Http\\Controllers\\SiswaController');
        $this->assertNotNull($siswaController);
        
        $laporanController = app()->make('App\\Http\\Controllers\\LaporanController');
        $this->assertNotNull($laporanController);
        
        $jurusanController = app()->make('App\\Http\\Controllers\\JurusanController');
        $this->assertNotNull($jurusanController);
        
        $authController = app()->make('App\\Http\\Controllers\\Authcontroller');
        $this->assertNotNull($authController);
    }

    // -------------------------------------------------------------------------
    // 6. File System and Storage Preservation (Requirements 3.6)
    // -------------------------------------------------------------------------

    /**
     * Property: Storage directories exist and are writable
     * 
     * **Validates: Requirements 3.6**
     */
    public function testStorageDirectoriesExistAndWritable(): void
    {
        // Test storage directories
        $storagePath = storage_path();
        $this->assertDirectoryExists($storagePath);
        $this->assertTrue(is_writable($storagePath));
        
        // Test public storage directories
        $publicStoragePath = storage_path('app/public');
        $this->assertDirectoryExists($publicStoragePath);
        
        // Test that we can create directories in storage
        $testDir = storage_path('app/public/test_preservation');
        Storage::disk('public')->makeDirectory('test_preservation');
        $this->assertDirectoryExists($testDir);
        
        // Clean up
        Storage::disk('public')->deleteDirectory('test_preservation');
    }

    /**
     * Property: File upload functionality works correctly
     * 
     * **Validates: Requirements 3.6, 3.8**
     */
    public function testFileUploadFunctionalityWorks(): void
    {
        // Test that we can write files to storage
        $testContent = 'Test preservation content';
        $testFileName = 'test_preservation_file.txt';
        
        Storage::disk('public')->put($testFileName, $testContent);
        
        $this->assertTrue(Storage::disk('public')->exists($testFileName));
        $this->assertEquals($testContent, Storage::disk('public')->get($testFileName));
        
        // Clean up
        Storage::disk('public')->delete($testFileName);
    }

    // -------------------------------------------------------------------------
    // 7. Original Startup Script and Configuration Preservation
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
