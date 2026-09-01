<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Business;
use App\Models\User;
use App\Policies\BasePolicy;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

final class SamplePolicyDummyModel extends Model
{
    protected $guarded = [];
}

final class SamplePolicy extends BasePolicy {}

final class BasePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
    }

    public function test_policy_allows_access_when_business_id_matches_active_context(): void
    {
        $user = new User;
        $user->id = '11111111-1111-1111-1111-111111111111';

        $biz = new Business;
        $biz->id = '22222222-2222-2222-2222-222222222222';
        $biz->name = 'Biz Active';
        Context::setBusiness($biz);

        $policy = new SamplePolicy;
        $matchingModel = new SamplePolicyDummyModel;
        $matchingModel->business_id = '22222222-2222-2222-2222-222222222222';

        $otherModel = new SamplePolicyDummyModel;
        $otherModel->business_id = '33333333-3333-3333-3333-333333333333';

        $this->assertTrue($policy->view($user, $matchingModel));
        $this->assertTrue($policy->update($user, $matchingModel));
        $this->assertTrue($policy->delete($user, $matchingModel));

        $this->assertFalse($policy->view($user, $otherModel));
        $this->assertFalse($policy->update($user, $otherModel));
        $this->assertFalse($policy->delete($user, $otherModel));
    }

    public function test_policy_denies_access_when_no_active_context(): void
    {
        $user = new User;
        $user->id = '11111111-1111-1111-1111-111111111111';

        $policy = new SamplePolicy;
        $model = new SamplePolicyDummyModel;
        $model->business_id = '22222222-2222-2222-2222-222222222222';

        $this->assertFalse($policy->viewAny($user));
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->view($user, $model));
    }
}
