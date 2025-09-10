<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        // Create a fake user and authenticate using Sanctum
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_can_list_patients()
    {
        $this->authenticate();

        Patient::factory()->count(3)->create();

        $response = $this->getJson('/api/patients');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'age', 'address', 'contact', 'created_at', 'updated_at']
                     ]
                 ]);

        $this->assertCount(3, $response->json('data'));
    }

     
    public function test_can_show_patient()
    {
        $this->authenticate();

        $patient = Patient::factory()->create();

        $response = $this->getJson("/api/patients/{$patient->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $patient->id]);
    }
 

     
}
