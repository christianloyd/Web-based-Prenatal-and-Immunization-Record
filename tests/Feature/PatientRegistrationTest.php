<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $midwife;

    protected function setUp(): void
    {
        parent::setUp();

        $this->midwife = User::factory()->create([
            'role' => 'midwife',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function authenticated_midwife_can_create_patient()
    {
        $this->actingAs($this->midwife);

        $patientData = [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'middle_name' => 'Cruz',
            'birthdate' => '1995-05-15',
            'contact' => '09123456789',
            'address' => '123 Main St, Barangay 1',
        ];

        $response = $this->post(route('midwife.patients.store'), $patientData);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'contact' => '09123456789',
        ]);
    }

    /** @test */
    public function patient_registration_requires_valid_phone_number()
    {
        $this->actingAs($this->midwife);

        $patientData = [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'birthdate' => '1995-05-15',
            'contact' => 'invalid-phone',
            'address' => '123 Main St',
        ];

        $response = $this->post(route('midwife.patients.store'), $patientData);

        $response->assertSessionHasErrors('contact');
    }

    /** @test */
    public function patient_registration_requires_valid_birthdate()
    {
        $this->actingAs($this->midwife);

        $patientData = [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'birthdate' => now()->addDay()->format('Y-m-d'), // Future date
            'contact' => '09123456789',
            'address' => '123 Main St',
        ];

        $response = $this->post(route('midwife.patients.store'), $patientData);

        $response->assertSessionHasErrors('birthdate');
    }

    /** @test */
    public function guest_cannot_create_patient()
    {
        $patientData = [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'contact' => '09123456789',
        ];

        $response = $this->post(route('midwife.patients.store'), $patientData);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function patient_can_be_updated()
    {
        $this->actingAs($this->midwife);

        $patient = Patient::factory()->create();

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => $patient->last_name,
            'birthdate' => $patient->birthdate,
            'contact' => '09987654321',
            'address' => $patient->address,
        ];

        $response = $this->put(route('midwife.patients.update', $patient), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'first_name' => 'Updated',
            'contact' => '09987654321',
        ]);
    }

    /** @test */
    public function patient_can_be_soft_deleted()
    {
        $this->actingAs($this->midwife);

        $patient = Patient::factory()->create();

        $response = $this->delete(route('midwife.patients.destroy', $patient));

        $response->assertRedirect();
        $this->assertSoftDeleted('patients', [
            'id' => $patient->id,
        ]);
    }
}
