<?php

namespace Preferred\Domain\Users\Tests\Feature;

use Preferred\Domain\Users\Entities\Permission;
use Preferred\Domain\Users\Entities\Profile;
use Preferred\Domain\Users\Entities\User;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    /** @var User */
    private $user;

    /** @var Profile */
    private $profile;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->profile = factory(Profile::class)->create(['user_id' => $this->user->id]);
    }

    /**
     * @group show
     * @group crud
     */
    public function testShowMe()
    {
        $this->actingAs($this->user)
            ->getJson(route('api.profiles.me'))
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $this->profile->name
            ]);
    }

    /**
     * @group show
     * @group crud
     */
    public function testShow()
    {
        Permission::create(['name' => 'view profiles']);
        $this->user->givePermissionTo('view profiles');

        $this->actingAs($this->user)
            ->getJson(route('api.profiles.show', $this->profile->id))
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $this->profile->name
            ]);
    }

    /**
     * @group show
     * @group crud
     */
    public function testCannotShowBecauseNotAllowed()
    {
        $user2 = factory(User::class)->create();

        $this->actingAs($user2)
            ->getJson(route('api.profiles.show', $this->profile->id))
            ->assertStatus(403);
    }

    /**
     * @group update
     * @group crud
     */
    public function testUpdateMe()
    {
        $this->actingAs($this->user)
            ->patchJson(route('api.profiles.me.update'), [
                'name'               => 'test',
                'anti_phishing_code' => 'TEST'
            ])
            ->assertSuccessful()
            ->assertJsonFragment([
                'name'             => 'test',
                'antiPhishingCode' => 'TE**'
            ]);
    }

    /**
     * @group update
     * @group crud
     */
    public function testUpdate()
    {
        Permission::create(['name' => 'update profiles']);
        $this->user->givePermissionTo('update profiles');

        $user2 = factory(User::class)->create();
        $profile2 = factory(Profile::class)->create(['user_id' => $user2->id]);

        $this->actingAs($this->user)
            ->patchJson(route('api.profiles.update', $profile2->id), [
                'name'               => 'test',
                'anti_phishing_code' => 'TEST'
            ])
            ->assertSuccessful()
            ->assertJsonFragment([
                'name'             => 'test',
                'antiPhishingCode' => 'TE**'
            ]);
    }

    /**
     * @group update
     * @group crud
     */
    public function testCannotUpdateBecauseNotAllowed()
    {
        $user2 = factory(User::class)->create();

        $this->actingAs($user2)
            ->patchJson(route('api.profiles.update', $this->profile->id), [
                'name'               => 'test',
                'anti_phishing_code' => 'TEST'
            ])
            ->assertStatus(403);
    }

    /**
     * @group update
     * @group crud
     */
    public function testCannotUpdateAntiPhishingCodeBecauseNotAlphaDash()
    {
        Permission::create(['name' => 'update profiles']);
        $this->user->givePermissionTo('update profiles');

        $this->actingAs($this->user)
            ->patchJson(route('api.profiles.me.update'), [
                'anti_phishing_code' => 'Test ***',
            ])
            ->assertStatus(422);
    }
}
