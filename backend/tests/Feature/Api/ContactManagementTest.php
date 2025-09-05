<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
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
    public function test_authenticated_user_can_create_contact()
    {
        $contactData = [
            'name' => 'John Doe',
            'cpf' => '123.456.789-00',
            'phone' => '99999-9999',
            'cep' => '99999-999',
            'street' => 'Main Street',
            'number' => '123',
            'neighborhood' => 'Downtown',
            'city' => 'Testville',
            'state' => 'TS',
            'latitude' => '-23.550520',
            'longitude' => '-46.633308',
        ];

        $response = $this->postJson('/api/contacts', $contactData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'John Doe']);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'cpf' => '123.456.789-00',
        ]);
    }
}
