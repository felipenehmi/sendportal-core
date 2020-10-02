<?php

namespace Tests;

use Illuminate\Testing\TestResponse;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;

trait SendportalTestSupportTrait {

    /**
     * Create a user with attached workspace.
     */
    protected function createUserWithWorkspace(): User
    {
        return factory(Workspace::class)->create()->owner;
    }

    /**
     * Create a user with attached workspace, returning both workspace and user.
     */
    protected function createUserAndWorkspace(): array
    {
        $workspace = factory(Workspace::class)->create();

        return [$workspace, $workspace->owner];
    }

    /**
     * Log in the given user.
     */
    protected function loginUser(User $user): void
    {
        auth()->login($user);
    }

    protected function createUserWithWorkspaceAndEmailService(): array
    {
        $user = factory(User::class)->create();
        $workspace = factory(Workspace::class)->create([
            'owner_id' => $user->id,
        ]);
        $emailService = factory(EmailService::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        return [$workspace, $emailService];
    }

    protected function createEmailService(): EmailService
    {
        return factory(EmailService::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    protected function createCampaign(EmailService $emailService): Campaign
    {
        return factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);
    }

    protected function createSegment(): Segment
    {
        return factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    protected function createSubscriber(): Subscriber
    {
        return factory(Subscriber::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    public function assertLoginRedirect(TestResponse $response): void
    {
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function createUserAndLogin(array $states = [], array $overrides = []): User
    {
        $user = factory(User::class)->states($states)->create($overrides);
        $this;

        return $user;
    }

    public function createWorkspaceUser(Workspace $workspace, array $overrides = []): User
    {
        $user = factory(User::class)->create($overrides);
        $workspace->users()->attach($user, ['role' => Workspace::ROLE_MEMBER]);

        return $user;
    }
}
