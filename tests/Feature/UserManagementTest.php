<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_users_and_create_event_admin()
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'role' => 'superadmin',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));
        $response->assertStatus(200);

        $storeResponse = $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Event Manager',
            'email' => 'eventmgr@test.com',
            'role' => 'event_admin',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'is_active' => '1',
        ]);

        $storeResponse->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'eventmgr@test.com',
            'role' => 'event_admin',
        ]);
    }

    public function test_event_admin_cannot_access_user_management()
    {
        $eventAdmin = User::create([
            'name' => 'Event Admin Only',
            'email' => 'eventadmin@test.com',
            'role' => 'event_admin',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($eventAdmin)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_user_can_update_own_profile_and_password()
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'original@test.com',
            'role' => 'event_admin',
            'password' => Hash::make('oldpassword123'),
        ]);

        $updateResponse = $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);

        $passwordResponse = $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $passwordResponse->assertRedirect();
        $this->assertTrue(Hash::check('newpassword456', $user->fresh()->password));
    }
}
