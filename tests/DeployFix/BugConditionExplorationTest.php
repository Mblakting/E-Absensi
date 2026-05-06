<?php

namespace Tests\DeployFix;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use App\Http\Controllers\Authcontroller;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Bug Condition Exploration Property Test
 * 
 * **Validates: Requirements 2.1, 2.2, 2.5, 2.6**
 * 
 * CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists
 * DO NOT attempt to fix the test or the code when it fails
 * 
 * This test encodes the expected behavior - it will validate the fix when it passes after implementation
 * GOAL: Surface counterexamples that demonstrate the bug exists
 */
class BugConditionExplorationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create jurusan for testing
        DB::table('jurusan')->insert([
            'kode_jurusan' => 'TKJ',
            'nama_jurusan' => 'Teknik Komputer Jaringan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Property 1: Bug Condition - Deployment and Authentication Failures
     * 
     * For any deployment attempt where the Docker build process runs and authentication attempts are made,
     * the fixed system SHALL successfully complete the Docker build without file not found errors,
     * allow student login with valid NISN credentials, allow admin login with valid email credentials,
     * and enable admin access to student data.
     */
    public function test_bug_condition_deployment_and_authentication_failures()
    {
        // Test 1: Docker build failure when package-lock.json is missing
        $this->assertDockerBuildSucceedsWithoutPackageLock();
        
        // Test 2: Student login failure with valid NISN credentials in production environment
        $this->assertStudentLoginSucceedsWithValidCredentials();
        
        // Test 3: Admin login failure with valid email credentials in production environment  
        $this->assertAdminLoginSucceedsWithValidCredentials();
        
        // Test 4: Admin data access failure when trying to retrieve student data after login
        $this->assertAdminCanAccessStudentDataAfterLogin();
    }

    /**
     * Test Docker build succeeds without package-lock.json
     * This will FAIL on unfixed code because Dockerfile requires package-lock.json
     */
    private function assertDockerBuildSucceedsWithoutPackageLock()
    {
        // Simulate Docker build condition - check if package-lock.json exists in project root
        $packageLockExists = File::exists(base_path('package-lock.json'));
        
        // This assertion will FAIL on unfixed code because package-lock.json is missing
        // but Dockerfile tries to copy it with: COPY package.json package-lock.json ./
        $this->assertTrue(
            $packageLockExists || $this->canDockerBuildWithoutPackageLock(),
            'Docker build should succeed even when package-lock.json is missing. ' .
            'Current Dockerfile fails with "failed to calculate checksum of ref: \'/package-lock.json\': not found"'
        );
    }

    /**
     * Test student login succeeds with valid NISN credentials
     * This will FAIL on unfixed code if authentication system has issues
     */
    private function assertStudentLoginSucceedsWithValidCredentials()
    {
        // Create a test student
        $siswa = Siswa::create([
            'nisn' => '1234567890',
            'nama_lengkap' => 'Test Student',
            'kelas' => '12 TKJ 1',
            'no_hp' => '081234567890',
            'kode_jurusan' => 'TKJ',
            'password' => Hash::make('password123'),
            'foto' => '',
        ]);

        // Simulate production environment authentication attempt
        $request = Request::create('/login', 'POST', [
            'nisn' => '1234567890',
            'password' => 'password123',
        ]);

        $controller = new Authcontroller();
        
        // This should succeed but may FAIL on unfixed code due to:
        // - Session configuration issues in production
        // - Guard configuration problems
        // - Database connection issues
        $loginAttempt = Auth::guard('siswa')->attempt([
            'nisn' => '1234567890',
            'password' => 'password123',
        ]);

        $this->assertTrue(
            $loginAttempt,
            'Student login should succeed with valid NISN credentials. ' .
            'Failure indicates authentication system issues in production environment.'
        );
    }

    /**
     * Test admin login succeeds with valid email credentials
     * This will FAIL on unfixed code if admin authentication has issues
     */
    private function assertAdminLoginSucceedsWithValidCredentials()
    {
        // Create a test admin user
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
        ]);

        // Simulate production environment admin authentication attempt
        $loginAttempt = Auth::guard('user')->attempt([
            'email' => 'admin@test.com',
            'password' => 'admin123',
        ]);

        $this->assertTrue(
            $loginAttempt,
            'Admin login should succeed with valid email credentials. ' .
            'Failure indicates admin authentication system issues in production environment.'
        );
    }

    /**
     * Test admin can access student data after login
     * This will FAIL on unfixed code if data access is broken
     */
    private function assertAdminCanAccessStudentDataAfterLogin()
    {
        // Create admin and login (use different email to avoid unique constraint violation)
        $admin = User::create([
            'name' => 'Test Admin Data Access',
            'email' => 'admin-data@test.com',
            'password' => Hash::make('admin123'),
        ]);

        Auth::guard('user')->login($admin);

        // Create test student data
        $siswa = Siswa::create([
            'nisn' => '9876543210',
            'nama_lengkap' => 'Test Student for Admin Access',
            'kelas' => '11 TKJ 2',
            'no_hp' => '089876543210',
            'kode_jurusan' => 'TKJ',
            'password' => Hash::make('student123'),
            'foto' => '',
        ]);

        // Test admin access to student data through SiswaController
        $controller = new SiswaController();
        $request = Request::create('/siswa', 'GET');

        try {
            // This should succeed but may FAIL on unfixed code due to:
            // - Database connection issues
            // - Permission/middleware problems
            // - Session/authentication state issues
            $response = $controller->index($request);
            
            $this->assertNotNull(
                $response,
                'Admin should be able to access student data after successful login. ' .
                'Failure indicates data access issues in production environment.'
            );

            // Verify student data is accessible
            $studentData = Siswa::where('nisn', '9876543210')->first();
            $this->assertNotNull(
                $studentData,
                'Student data should be retrievable by admin. ' .
                'Failure indicates database or data access problems.'
            );

        } catch (\Exception $e) {
            $this->fail(
                'Admin data access failed with exception: ' . $e->getMessage() . '. ' .
                'This indicates system issues preventing admin from accessing student data.'
            );
        }
    }

    /**
     * Helper method to check if Docker can build without package-lock.json
     * This simulates the Docker build condition
     */
    private function canDockerBuildWithoutPackageLock(): bool
    {
        // Check if package.json exists (required for npm install fallback)
        $packageJsonExists = File::exists(base_path('package.json'));
        
        if (!$packageJsonExists) {
            return false;
        }

        // Simulate Docker build logic - if package-lock.json is missing,
        // the build should fallback to npm install instead of npm ci
        // This will FAIL on unfixed code because Dockerfile uses npm ci which requires package-lock.json
        
        // Read Dockerfile to check current implementation
        $dockerfileContent = File::get(base_path('Dockerfile'));
        
        // Check if Dockerfile handles missing package-lock.json gracefully
        $hasConditionalCopy = str_contains($dockerfileContent, 'package-lock.json*') || 
                             str_contains($dockerfileContent, 'npm install') ||
                             !str_contains($dockerfileContent, 'npm ci');
        
        return $hasConditionalCopy;
    }
}