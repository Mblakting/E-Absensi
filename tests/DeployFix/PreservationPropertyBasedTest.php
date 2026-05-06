<?php

namespace Tests\DeployFix;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;

/**
 * Property-Based Preservation Tests — Property 2 (Extended)
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8**
 *
 * These property-based tests generate many random test cases to verify that
 * Laravel application functionality is preserved across different inputs.
 * 
 * Uses Laravel's Faker to generate realistic test data and verify that
 * core application behaviors remain consistent.
 *
 * **EXPECTED OUTCOME ON UNFIXED CODE: PASS**
 * **EXPECTED OUTCOME AFTER FIX: PASS (no regressions)**
 */
class PreservationPropertyBasedTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    private function seedBaseData(): void
    {
        // Create base jurusan for testing
        $jurusanList = [
            ['kode_jurusan' => 'TKJ', 'nama_jurusan' => 'Teknik Komputer Jaringan'],
            ['kode_jurusan' => 'RPL', 'nama_jurusan' => 'Rekayasa Perangkat Lunak'],
            ['kode_jurusan' => 'MM', 'nama_jurusan' => 'Multimedia'],
            ['kode_jurusan' => 'TKR', 'nama_jurusan' => 'Teknik Kendaraan Ringan'],
        ];

        foreach ($jurusanList as $jurusan) {
            Jurusan::create($jurusan);
        }
    }

    /**
     * Property: For any valid siswa data, CRUD operations should work consistently
     * 
     * **Validates: Requirements 3.1, 3.8**
     */
    public function testPropertySiswaCrudConsistency(): void
    {
        $jurusanCodes = ['TKJ', 'RPL', 'MM', 'TKR'];
        
        // Generate 10 random siswa records and test CRUD operations
        for ($i = 0; $i < 10; $i++) {
            $nisn = $this->faker->unique()->numerify('##########');
            $namaLengkap = $this->faker->name();
            $kelas = $this->faker->randomElement(['X', 'XI', 'XII']) . ' ' . 
                     $this->faker->randomElement($jurusanCodes) . ' ' . 
                     $this->faker->numberBetween(1, 3);
            $noHp = $this->faker->phoneNumber();
            $kodeJurusan = $this->faker->randomElement($jurusanCodes);

            // Property: Create operation should always succeed with valid data
            $siswa = Siswa::create([
                'nisn' => $nisn,
                'nama_lengkap' => $namaLengkap,
                'kelas' => $kelas,
                'no_hp' => $noHp,
                'kode_jurusan' => $kodeJurusan,
                'password' => Hash::make('12345')
            ]);

            $this->assertInstanceOf(Siswa::class, $siswa);
            $this->assertEquals($nisn, $siswa->nisn);
            $this->assertEquals($namaLengkap, $siswa->nama_lengkap);

            // Property: Read operation should return the same data
            $foundSiswa = Siswa::find($nisn);
            $this->assertNotNull($foundSiswa);
            $this->assertEquals($namaLengkap, $foundSiswa->nama_lengkap);
            $this->assertEquals($kelas, $foundSiswa->kelas);
            $this->assertEquals($kodeJurusan, $foundSiswa->kode_jurusan);

            // Property: Update operation should modify only specified fields
            $newNama = $this->faker->name();
            $foundSiswa->update(['nama_lengkap' => $newNama]);
            
            $updatedSiswa = Siswa::find($nisn);
            $this->assertEquals($newNama, $updatedSiswa->nama_lengkap);
            $this->assertEquals($kelas, $updatedSiswa->kelas); // Should remain unchanged
            $this->assertEquals($kodeJurusan, $updatedSiswa->kode_jurusan); // Should remain unchanged

            // Property: Delete operation should remove the record completely
            $foundSiswa->delete();
            $deletedSiswa = Siswa::find($nisn);
            $this->assertNull($deletedSiswa);
        }
    }

    /**
     * Property: For any valid presensi data, database operations should work consistently
     * 
     * **Validates: Requirements 3.3, 3.4**
     */
    public function testPropertyPresensiOperationsConsistency(): void
    {
        // Create a test siswa first
        $siswa = Siswa::create([
            'nisn' => '9999999999',
            'nama_lengkap' => 'Test Siswa for Presensi',
            'kelas' => 'XII TKJ 1',
            'kode_jurusan' => 'TKJ',
            'password' => Hash::make('12345')
        ]);

        // Generate 5 random presensi records with unique dates
        $usedDates = [];
        for ($i = 0; $i < 5; $i++) {
            // Ensure unique date for this siswa (due to unique constraint on nisn + tgl_presensi)
            do {
                $tglPresensi = $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d');
            } while (in_array($tglPresensi, $usedDates));
            $usedDates[] = $tglPresensi;
            
            $jamIn = $this->faker->time('H:i:s', '08:00:00');
            $jamOut = $this->faker->time('H:i:s', '15:00:00');
            $lokasiIn = $this->faker->latitude() . ',' . $this->faker->longitude();
            $lokasiOut = $this->faker->latitude() . ',' . $this->faker->longitude();

            // Property: Insert presensi should always succeed with valid data
            $presensiId = DB::table('presensi')->insertGetId([
                'nisn' => $siswa->nisn,
                'tgl_presensi' => $tglPresensi,
                'jam_in' => $jamIn,
                'jam_out' => $jamOut,
                'foto_in' => 'test_in_' . $i . '.jpg',
                'foto_out' => 'test_out_' . $i . '.jpg',
                'lokasi_in' => $lokasiIn,
                'lokasi_out' => $lokasiOut,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assertIsInt($presensiId);
            $this->assertGreaterThan(0, $presensiId);

            // Property: Query presensi should return consistent data
            $presensi = DB::table('presensi')->find($presensiId);
            $this->assertNotNull($presensi);
            $this->assertEquals($siswa->nisn, $presensi->nisn);
            $this->assertEquals($tglPresensi, $presensi->tgl_presensi);
            $this->assertEquals($jamIn, $presensi->jam_in);
            $this->assertEquals($jamOut, $presensi->jam_out);

            // Property: Join with siswa table should work correctly
            $presensiWithSiswa = DB::table('presensi')
                ->join('siswa', 'presensi.nisn', '=', 'siswa.nisn')
                ->where('presensi.id', $presensiId)
                ->select('presensi.*', 'siswa.nama_lengkap')
                ->first();

            $this->assertNotNull($presensiWithSiswa);
            $this->assertEquals($siswa->nama_lengkap, $presensiWithSiswa->nama_lengkap);
        }
    }

    /**
     * Property: For any valid pengajuan izin data, operations should work consistently
     * 
     * **Validates: Requirements 3.3, 3.4**
     */
    public function testPropertyPengajuanIzinConsistency(): void
    {
        // Create a test siswa first
        $siswa = Siswa::create([
            'nisn' => '8888888888',
            'nama_lengkap' => 'Test Siswa for Izin',
            'kelas' => 'XI RPL 2',
            'kode_jurusan' => 'RPL',
            'password' => Hash::make('12345')
        ]);

        // Generate 5 random pengajuan izin records with unique dates
        $usedDates = [];
        for ($i = 0; $i < 5; $i++) {
            // Ensure unique date for this siswa (due to unique constraint on nisn + tgl_izin)
            do {
                $tglIzin = $this->faker->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d');
            } while (in_array($tglIzin, $usedDates));
            $usedDates[] = $tglIzin;
            
            $status = $this->faker->randomElement(['i', 's']); // izin or sakit
            $keterangan = $this->faker->sentence();
            $statusApproved = $this->faker->randomElement([0, 1, 2]); // pending, approved, rejected

            // Property: Insert pengajuan izin should always succeed
            $izinId = DB::table('pengajuan_izin')->insertGetId([
                'nisn' => $siswa->nisn,
                'tgl_izin' => $tglIzin,
                'status' => $status,
                'keterangan' => $keterangan,
                'status_approved' => $statusApproved,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assertIsInt($izinId);
            $this->assertGreaterThan(0, $izinId);

            // Property: Query pengajuan izin should return consistent data
            $izin = DB::table('pengajuan_izin')->find($izinId);
            $this->assertNotNull($izin);
            $this->assertEquals($siswa->nisn, $izin->nisn);
            $this->assertEquals($tglIzin, $izin->tgl_izin);
            $this->assertEquals($status, $izin->status);
            $this->assertEquals($keterangan, $izin->keterangan);
            $this->assertEquals($statusApproved, $izin->status_approved);

            // Property: Update status should work correctly
            $newStatus = $this->faker->randomElement([0, 1, 2]);
            DB::table('pengajuan_izin')
                ->where('id', $izinId)
                ->update(['status_approved' => $newStatus]);

            $updatedIzin = DB::table('pengajuan_izin')->find($izinId);
            $this->assertEquals($newStatus, $updatedIzin->status_approved);
            $this->assertEquals($keterangan, $updatedIzin->keterangan); // Should remain unchanged
        }
    }

    /**
     * Property: For any valid jurusan data, CRUD operations should work consistently
     * 
     * **Validates: Requirements 3.1, 3.2**
     */
    public function testPropertyJurusanCrudConsistency(): void
    {
        // Generate 5 random jurusan records
        for ($i = 0; $i < 5; $i++) {
            $kodeJurusan = strtoupper($this->faker->unique()->lexify('???'));
            $namaJurusan = $this->faker->words(3, true);

            // Property: Create jurusan should always succeed with valid data
            $jurusan = Jurusan::create([
                'kode_jurusan' => $kodeJurusan,
                'nama_jurusan' => $namaJurusan
            ]);

            $this->assertInstanceOf(Jurusan::class, $jurusan);
            $this->assertEquals($kodeJurusan, $jurusan->kode_jurusan);
            $this->assertEquals($namaJurusan, $jurusan->nama_jurusan);

            // Property: Read operation should return the same data
            $foundJurusan = Jurusan::find($kodeJurusan);
            $this->assertNotNull($foundJurusan);
            $this->assertEquals($namaJurusan, $foundJurusan->nama_jurusan);

            // Property: Update operation should modify only specified fields
            $newNama = $this->faker->words(3, true);
            $foundJurusan->update(['nama_jurusan' => $newNama]);
            
            $updatedJurusan = Jurusan::find($kodeJurusan);
            $this->assertEquals($newNama, $updatedJurusan->nama_jurusan);
            $this->assertEquals($kodeJurusan, $updatedJurusan->kode_jurusan); // Should remain unchanged

            // Property: Delete operation should remove the record (if no FK constraints)
            $foundJurusan->delete();
            $deletedJurusan = Jurusan::find($kodeJurusan);
            $this->assertNull($deletedJurusan);
        }
    }

    /**
     * Property: Laravel configuration should remain consistent across different access patterns
     * 
     * **Validates: Requirements 3.5, 3.7**
     */
    public function testPropertyConfigurationConsistency(): void
    {
        $configKeys = [
            'app.name',
            'app.env',
            'app.debug',
            'database.default',
            'auth.defaults.guard',
            'session.driver',
            'cache.default'
        ];

        // Property: Configuration values should be consistent across multiple reads
        for ($i = 0; $i < 3; $i++) {
            foreach ($configKeys as $key) {
                $value1 = config($key);
                $value2 = config($key);
                
                $this->assertEquals(
                    $value1, 
                    $value2, 
                    "Configuration key '{$key}' should return consistent values"
                );
            }
        }

        // Property: Auth guards configuration should remain stable
        $authGuards = config('auth.guards');
        $this->assertIsArray($authGuards);
        $this->assertArrayHasKey('siswa', $authGuards);
        $this->assertArrayHasKey('user', $authGuards);
        
        // Multiple reads should return the same structure
        $authGuards2 = config('auth.guards');
        $this->assertEquals($authGuards, $authGuards2);
    }

    /**
     * Property: Route resolution should work consistently for all defined routes
     * 
     * **Validates: Requirements 3.7, 3.8**
     */
    public function testPropertyRouteResolutionConsistency(): void
    {
        $testRoutes = [
            'login',
            'loginadmin', 
            'dashboard',
            'dashboardadmin',
            'presensi.create',
            'siswa.index',
            'laporan.index'
        ];

        // Property: Route URLs should be consistent across multiple generations
        foreach ($testRoutes as $routeName) {
            $url1 = route($routeName);
            $url2 = route($routeName);
            
            $this->assertEquals(
                $url1, 
                $url2, 
                "Route '{$routeName}' should generate consistent URLs"
            );
            
            $this->assertIsString($url1);
            $this->assertNotEmpty($url1);
        }
    }

    /**
     * Property: Database queries should return consistent results for the same input
     * 
     * **Validates: Requirements 3.3, 3.4**
     */
    public function testPropertyDatabaseQueryConsistency(): void
    {
        // Create test data
        $siswa = Siswa::create([
            'nisn' => '7777777777',
            'nama_lengkap' => 'Consistency Test Student',
            'kelas' => 'XII MM 1',
            'kode_jurusan' => 'MM',
            'password' => Hash::make('12345')
        ]);

        // Property: Same query should return same results multiple times
        for ($i = 0; $i < 5; $i++) {
            $result1 = DB::table('siswa')->where('nisn', '7777777777')->first();
            $result2 = DB::table('siswa')->where('nisn', '7777777777')->first();
            
            $this->assertEquals($result1, $result2);
            $this->assertEquals('Consistency Test Student', $result1->nama_lengkap);
        }

        // Property: Join queries should be consistent
        for ($i = 0; $i < 3; $i++) {
            $joinResult1 = DB::table('siswa')
                ->join('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
                ->where('siswa.nisn', '7777777777')
                ->select('siswa.*', 'jurusan.nama_jurusan')
                ->first();
                
            $joinResult2 = DB::table('siswa')
                ->join('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
                ->where('siswa.nisn', '7777777777')
                ->select('siswa.*', 'jurusan.nama_jurusan')
                ->first();
                
            $this->assertEquals($joinResult1, $joinResult2);
            $this->assertEquals('Multimedia', $joinResult1->nama_jurusan);
        }
    }
}