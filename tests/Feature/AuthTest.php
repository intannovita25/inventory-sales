<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_guest_cannot_open_protected_pages(): void
    {
        foreach (['/products', '/sales', '/purchases', '/users'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'salah-total'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correct_credentials_can_login_and_open_dashboard(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'role' => User::ROLE_ADMIN]);

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_password_is_stored_hashed(): void
    {
        $user = User::create([
            'name' => 'Uji Hash',
            'email' => 'hash@example.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_STAFF,
        ]);

        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }
}
