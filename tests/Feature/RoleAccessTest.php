<?php

namespace Tests\Feature;

use App\Models\Charger;
use App\Models\Location;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function createLocation(string $nama, float $lat, float $lng): Location
    {
        $location = Location::create([
            'nama_lokasi'     => $nama,
            'alamat'          => 'Jl. Contoh No. 1',
            'detail_area'     => 'Area Parkir',
            'latitude'        => $lat,
            'longitude'       => $lng,
            'jam_operasional' => '24 Jam',
            'status'          => 'aktif',
        ]);

        Charger::create([
            'location_id'  => $location->id_location,
            'charger_type' => 'CCS2',
            'power_kw'     => 50,
            'status'       => 'available',
        ]);

        return $location;
    }

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_driver_cannot_open_admin_pages(): void
    {
        $this->actingAs($this->userWithRole('driver'))
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole('driver'))
            ->get(route('admin.tariffs.index'))
            ->assertForbidden();
    }

    public function test_operator_cannot_open_admin_pages(): void
    {
        $this->actingAs($this->userWithRole('operator'))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_and_operator_cannot_open_driver_pages(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('spklu.index'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole('operator'))
            ->get(route('reservations.index'))
            ->assertForbidden();
    }

    public function test_dashboard_redirects_each_role_to_its_own_dashboard(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($this->userWithRole('operator'))
            ->get(route('dashboard'))
            ->assertRedirect(route('operator.dashboard'));

        $this->actingAs($this->userWithRole('driver'))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_each_role_can_open_its_own_dashboard(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($this->userWithRole('operator'))
            ->get(route('operator.dashboard'))
            ->assertOk();
    }

    public function test_driver_can_list_and_open_spklu(): void
    {
        $location = $this->createLocation('SPKLU Malioboro', -7.7928, 110.3658);
        Tariff::create([
            'id_location'            => $location->id_location,
            'harga_per_kwh'          => 2466,
            'biaya_layanan'          => 0,
            'biaya_minimum'          => 0,
            'overstay_fee_per_menit' => 500,
            'periode_berlaku'        => now()->subDay(),
        ]);

        $driver = $this->userWithRole('driver');

        $this->actingAs($driver)
            ->get(route('spklu.index', ['search' => 'Malioboro']))
            ->assertOk()
            ->assertSee('SPKLU Malioboro');

        $this->actingAs($driver)
            ->get(route('spklu.show', $location->id_location))
            ->assertOk()
            ->assertSee('SPKLU Malioboro')
            ->assertSee('2.466');
    }

    public function test_nearby_api_sorts_locations_by_distance_and_respects_radius(): void
    {
        $this->createLocation('SPKLU Surabaya', -7.2575, 112.7521);
        $this->createLocation('SPKLU Yogyakarta', -7.7928, 110.3658);

        $driver = $this->userWithRole('driver');

        // Dari Yogyakarta, radius default 50 km hanya memuat lokasi Yogyakarta.
        $this->actingAs($driver)
            ->getJson(route('api.locations.nearby', ['latitude' => -7.7956, 'longitude' => 110.3695]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_lokasi', 'SPKLU Yogyakarta');

        // Radius 500 km memuat keduanya, terdekat lebih dulu.
        $this->actingAs($driver)
            ->getJson(route('api.locations.nearby', [
                'latitude' => -7.7956, 'longitude' => 110.3695, 'radius' => 500,
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama_lokasi', 'SPKLU Yogyakarta')
            ->assertJsonPath('data.1.nama_lokasi', 'SPKLU Surabaya');
    }

    public function test_nearby_api_requires_coordinates(): void
    {
        $this->actingAs($this->userWithRole('driver'))
            ->getJson(route('api.locations.nearby'))
            ->assertStatus(422);
    }

    public function test_register_always_creates_a_driver(): void
    {
        $this->post('/register', [
            'name'                  => 'Calon Operator',
            'email'                 => 'calon@example.com',
            'password'              => 'password-kuat-123',
            'password_confirmation' => 'password-kuat-123',
            'role'                  => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'calon@example.com',
            'role'  => 'driver',
        ]);
    }
}