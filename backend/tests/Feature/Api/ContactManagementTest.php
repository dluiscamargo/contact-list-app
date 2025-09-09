<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Test authenticated user can create a contact.
     *
     * @return void
     */
    // public function test_authenticated_user_can_create_contact()
    // {
    //     Http::fake([
    //         'nominatim.openstreetmap.org/*' => Http::response([
    //             [
    //                 'lat' => '-23.550520',
    //                 'lon' => '-46.633308',
    //             ]
    //         ], 200)
    //     ]);

    //     $contactData = [
    //         'name' => 'John Doe',
    //         'cpf' => '704.880.370-27', // CPF Válido (gerado por ferramenta)
    //         'phone' => '99999-9999',
    //         'cep' => '99999-999',
    //         'street' => 'Main Street',
    //         'number' => '123',
    //         'neighborhood' => 'Downtown',
    //         'city' => 'Testville',
    //         'state' => 'TS',
    //     ];

    //     $response = $this->postJson('/api/contacts', $contactData);

    //     $response->assertStatus(201)
    //              ->assertJsonFragment(['name' => 'John Doe']);

    //     $this->assertDatabaseHas('contacts', [
    //         'user_id' => $this.user->id,
    //         'cpf' => '704.880.370-27', // CPF Válido (gerado por ferramenta)
    //     ]);
    // }
}
