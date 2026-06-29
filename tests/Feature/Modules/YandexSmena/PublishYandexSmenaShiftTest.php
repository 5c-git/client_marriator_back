<?php

namespace Tests\Feature\Modules\YandexSmena;

use App\Enum\Order\OrderStatusEnum;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Models\User;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Modules\YandexSmena\Jobs\PublishYandexSmenaEventJob;
use Modules\YandexSmena\Models\SmenaPayment;
use Modules\YandexSmena\Models\SmenaProfession;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Models\SmenaSite;
use Tests\TestCase;

class PublishYandexSmenaShiftTest extends TestCase
{
    use RefreshDatabase;

    private function createManagerUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    private function createViewActivity(): ViewActivities
    {
        return ViewActivities::create([
            'uuid' => 'activity-'.uniqid(),
            'name' => 'Продавец',
            'active' => true,
        ]);
    }

    private function createPlace(): Place
    {
        $brand = Brand::create(['uuid' => 'brand-'.uniqid(), 'name' => 'Brand']);

        return Place::create([
            'uuid' => 'place-'.uniqid(),
            'brand_id' => $brand->id,
            'name' => 'ТЦ',
            'address_kladr' => 'Москва',
            'latitude' => 55.0,
            'longitude' => 37.0,
        ]);
    }

    private function createMappings(Place $place, ViewActivities $activity): array
    {
        $site = SmenaSite::create([
            'place_id' => $place->id,
            'external_id' => 'yandex-site-'.uniqid(),
            'name' => $place->name,
        ]);

        $payment = SmenaPayment::create([
            'code' => 'PAY',
            'external_id' => 'yandex-pay-'.uniqid(),
            'name' => 'Тариф',
            'amount_per_hour' => 100,
            'currency' => 'RUB',
        ]);

        $profession = SmenaProfession::create([
            'view_activity_id' => $activity->id,
            'external_id' => 'yandex-prof-'.uniqid(),
            'name' => $activity->name,
            'rest_length_min' => 30,
            'yandex_smena_payment_id' => $payment->id,
        ]);

        return [$site, $profession, $payment];
    }

    private function createOrderActivity(User $user, int $count = 1): OrderActivities
    {
        $place = $this->createPlace();
        $activity = $this->createViewActivity();
        $this->createMappings($place, $activity);

        $order = Order::create([
            'place_id' => $place->id,
            'user_id' => $user->id,
            'status' => OrderStatusEnum::new->value,
        ]);

        return OrderActivities::create([
            'order_id' => $order->id,
            'view_activity_id' => $activity->id,
            'count' => $count,
            'date_start' => Carbon::parse('2026-07-05 10:00:00', 'Europe/Moscow'),
            'date_end' => Carbon::parse('2026-07-05 18:00:00', 'Europe/Moscow'),
            'need_foto' => false,
        ]);
    }

    private function createTaskActivity(User $user, int $count = 1): TaskActivity
    {
        $place = $this->createPlace();
        $activity = $this->createViewActivity();
        $this->createMappings($place, $activity);

        $order = Order::create([
            'place_id' => $place->id,
            'user_id' => $user->id,
            'status' => OrderStatusEnum::new->value,
        ]);

        $task = Task::create([
            'order_id' => $order->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'status' => OrderStatusEnum::new->value,
            'price' => 0,
            'income' => 0,
            'scope_of_services' => 0,
        ]);

        return TaskActivity::create([
            'task_id' => $task->id,
            'order_activity_id' => null,
            'view_activity_id' => $activity->id,
            'count' => $count,
            'date_start' => Carbon::parse('2026-07-05 10:00:00', 'Europe/Moscow'),
            'date_end' => Carbon::parse('2026-07-05 18:00:00', 'Europe/Moscow'),
            'need_foto' => false,
        ]);
    }

    public function test_publish_shift_from_order_activity(): void
    {
        Queue::fake();
        $user = $this->createManagerUser();
        Passport::actingAs($user, ['personalArea']);

        $activity = $this->createOrderActivity($user);

        $response = $this->postJson('/api/yandex-smena/publish-shift', [
            'orderId' => $activity->order_id,
            'orderActivityId' => $activity->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 1);

        $this->assertCount(1, SmenaShift::all());

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.create'
                && $envelope['payload']['length'] === 480
                && $envelope['payload']['rest_length'] === 30;
        });
    }

    public function test_publish_shift_from_task_activity(): void
    {
        Queue::fake();
        $user = $this->createManagerUser();
        Passport::actingAs($user, ['personalArea']);

        $activity = $this->createTaskActivity($user);

        $response = $this->postJson('/api/yandex-smena/publish-shift', [
            'taskId' => $activity->task_id,
            'taskActivityId' => $activity->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 1);

        $this->assertCount(1, SmenaShift::all());
    }

    public function test_publish_shift_creates_multiple_shifts_for_count(): void
    {
        Queue::fake();
        $user = $this->createManagerUser();
        Passport::actingAs($user, ['personalArea']);

        $activity = $this->createOrderActivity($user, 3);

        $response = $this->postJson('/api/yandex-smena/publish-shift', [
            'orderId' => $activity->order_id,
            'orderActivityId' => $activity->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.count', 3);

        $this->assertCount(3, SmenaShift::all());
        $this->assertCount(3, SmenaShift::query()->where('order_activity_id', $activity->id)->get());
    }

    public function test_publish_shift_returns_422_when_mapping_missing(): void
    {
        Queue::fake();
        $user = $this->createManagerUser();
        Passport::actingAs($user, ['personalArea']);

        $place = $this->createPlace();
        $activity = $this->createViewActivity();

        $order = Order::create([
            'place_id' => $place->id,
            'user_id' => $user->id,
            'status' => OrderStatusEnum::new->value,
        ]);

        $orderActivity = OrderActivities::create([
            'order_id' => $order->id,
            'view_activity_id' => $activity->id,
            'count' => 1,
            'date_start' => Carbon::parse('2026-07-05 10:00:00', 'Europe/Moscow'),
            'date_end' => Carbon::parse('2026-07-05 18:00:00', 'Europe/Moscow'),
            'need_foto' => false,
        ]);

        $response = $this->postJson('/api/yandex-smena/publish-shift', [
            'orderId' => $order->id,
            'orderActivityId' => $orderActivity->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertCount(0, SmenaShift::all());
    }

    public function test_publish_shift_forbidden_for_non_manager(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['personalArea']);

        $response = $this->postJson('/api/yandex-smena/publish-shift', [
            'orderId' => 1,
            'orderActivityId' => 1,
        ]);

        $response->assertForbidden();
    }
}
