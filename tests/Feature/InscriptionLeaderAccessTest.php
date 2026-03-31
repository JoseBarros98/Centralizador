<?php

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\MarketingTeam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InscriptionLeaderAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create(['name' => 'inscription.view']);
        Permission::create(['name' => 'inscription.edit']);
        Permission::create(['name' => 'inscription.create']);
        Permission::create(['name' => 'inscription.delete']);
    }

    public function test_active_marketing_team_leader_sees_only_external_unassigned_inscriptions_in_index(): void
    {
        /** @var User $leader */
        $leader = User::factory()->createOne();
        MarketingTeam::create([
            'name' => 'Equipo Norte',
            'leader_id' => $leader->id,
            'active' => true,
        ]);

        /** @var User $externalSystemUser */
        $externalSystemUser = User::factory()->createOne([
            'name' => 'Sistema Externo',
            'email' => 'sistema.externo@centtest.local',
        ]);

        /** @var User $advisor */
        $advisor = User::factory()->createOne();

        $externalInscription = $this->createInscription($externalSystemUser, '1001', 'ESTUDIANTE EXTERNO');
        $linkedInscription = $this->createInscription($advisor, '1002', 'ESTUDIANTE VINCULADO');

        $response = $this->actingAs($leader)->get(route('inscriptions.index', [
            'month' => 'all',
            'year' => 'all',
        ]));

        $response->assertOk();
        $response->assertSee($externalInscription->full_name);
        $response->assertDontSee($linkedInscription->full_name);
    }

    public function test_active_marketing_team_leader_can_edit_only_external_unassigned_inscriptions(): void
    {
        /** @var User $leader */
        $leader = User::factory()->createOne();
        MarketingTeam::create([
            'name' => 'Equipo Centro',
            'leader_id' => $leader->id,
            'active' => true,
        ]);

        /** @var User $externalSystemUser */
        $externalSystemUser = User::factory()->createOne([
            'name' => 'Sistema Externo',
            'email' => 'sistema.externo@centtest.local',
        ]);

        /** @var User $advisor */
        $advisor = User::factory()->createOne();

        $externalInscription = $this->createInscription($externalSystemUser, '2001', 'EXTERNO EDITABLE');
        $linkedInscription = $this->createInscription($advisor, '2002', 'INTERNO BLOQUEADO');

        $this->actingAs($leader)
            ->get(route('inscriptions.edit', $externalInscription))
            ->assertOk();

        $this->actingAs($leader)
            ->get(route('inscriptions.edit', $linkedInscription))
            ->assertForbidden();
    }

    private function createInscription(User $creator, string $ci, string $fullName): Inscription
    {
        return Inscription::create([
            'code' => 'INS-' . $ci,
            'full_name' => $fullName,
            'ci' => $ci,
            'inscription_date' => now()->toDateString(),
            'created_by' => $creator->id,
            'total_paid' => 0,
        ]);
    }
}