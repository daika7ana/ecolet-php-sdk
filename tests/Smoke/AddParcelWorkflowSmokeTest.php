<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelResult;
use Daika7ana\Ecolet\DTOs\Orders\Order;
use Daika7ana\Ecolet\DTOs\Orders\OrderToSend;
use Daika7ana\Ecolet\Tests\Smoke\Concerns\InteractsWithAuthenticatedSmokeClient;
use Daika7ana\Ecolet\Tests\Smoke\Support\AddParcelSmokePayloadFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class AddParcelWorkflowSmokeTest extends TestCase
{
    use InteractsWithAuthenticatedSmokeClient;

    #[Group('smoke')]
    public function testCanCompleteAddParcelWorkflowAndDownloadWaybillOnStaging(): void
    {
        $client = $this->makeAuthenticatedClient('add-parcel-workflow');
        ['orderToSend' => $orderToSend, 'order' => $order] = $this->completeWorkflow($client);

        $this->assertNotNull($orderToSend->orderId);
        $this->assertNotSame('', $order->number);
        $this->assertNotNull($order->status);

        $waybill = $client->orders()->downloadWaybill($orderToSend->orderId);
        $waybillContent = (string) $waybill->stream;

        $this->assertNotSame('', $waybillContent);
        $this->assertStringStartsWith('%PDF', $waybillContent);
        $this->assertNotNull($waybill->contentType);
        $this->assertStringContainsString('pdf', strtolower($waybill->contentType));
        $this->assertNotNull($waybill->contentDisposition);
        $this->assertStringContainsString('attachment', strtolower($waybill->contentDisposition));
        $this->assertStringContainsString('filename=', strtolower($waybill->contentDisposition));
        $this->assertStringContainsString('.pdf', strtolower($waybill->contentDisposition));
    }

    /**
     * @return array{orderToSend: OrderToSend, order: Order}
     */
    private function completeWorkflow(Client $client): array
    {
        $reloadResult = $client->addParcel()->reloadForm(AddParcelSmokePayloadFactory::workflowPayload());

        $this->assertTrue($reloadResult->isFormResponse());
        $this->assertNotNull($reloadResult->formResponse);
        $this->assertFalse(
            $reloadResult->formResponse->hasErrors(),
            'Reload form returned errors: ' . json_encode($reloadResult->formResponse->errors, JSON_THROW_ON_ERROR),
        );

        [$serviceSlug, $pickupDay, $pickupDate, $pickupTime] = $this->resolveWorkflowService($reloadResult);

        $sendOrderResult = $client->addParcel()->sendOrder(
            AddParcelSmokePayloadFactory::workflowPayload($serviceSlug, $pickupDay, $pickupDate, $pickupTime),
        );

        $this->assertTrue($sendOrderResult->isOrderResponse());
        $this->assertNotNull($sendOrderResult->orderToSendId);

        $orderToSend = $this->waitForCreatedOrder($client, $sendOrderResult->orderToSendId);

        $this->assertSame($sendOrderResult->orderToSendId, $orderToSend->id);
        $this->assertSame('ordered', $orderToSend->status);
        $this->assertNotNull($orderToSend->orderId);

        $order = $client->orders()->getOrder($orderToSend->orderId);

        $this->assertSame($orderToSend->orderId, $order->id);

        return [
            'orderToSend' => $orderToSend,
            'order' => $order,
        ];
    }

    /**
     * @return array{0: string, 1: ?string, 2: ?string, 3: ?string}
     */
    private function resolveWorkflowService(AddParcelResult $reloadResult): array
    {
        $formResponse = $reloadResult->formResponse;

        if ($formResponse === null) {
            $this->fail('Reload form did not return a form response.');
        }

        $statuses = $formResponse->pricing->statuses;
        $additionalServices = $formResponse->pricing->additionalServices;
        $pickupDates = $formResponse->pricing->pickupDates;

        foreach ($statuses as $serviceSlug => $isAvailable) {
            if ($isAvailable !== true) {
                continue;
            }

            $serviceAdditionalServices = $additionalServices[$serviceSlug] ?? [];
            $supportsCod = ($serviceAdditionalServices['cod'] ?? false) === true;

            if (! $supportsCod) {
                continue;
            }

            $pickupOptions = $pickupDates[$serviceSlug] ?? null;

            if (! is_array($pickupOptions)) {
                return [$serviceSlug, null, null, null];
            }

            $pickupInfo = $pickupOptions[0] ?? $pickupOptions;

            if (! is_array($pickupInfo)) {
                return [$serviceSlug, null, null, null];
            }

            $pickupDay = is_string($pickupInfo['day'] ?? null) ? $pickupInfo['day'] : null;
            $pickupDate = is_string($pickupInfo['date'] ?? null) ? $pickupInfo['date'] : null;
            $pickupTime = null;

            if (is_array($pickupInfo['hours'] ?? null) && is_string($pickupInfo['hours'][0] ?? null)) {
                $pickupTime = $pickupInfo['hours'][0];
            }

            return [$serviceSlug, $pickupDay, $pickupDate, $pickupTime];
        }

        $this->fail('No available service supporting the configured payload was returned by reload-form.');

        throw new \RuntimeException('Unreachable.');
    }

    private function waitForCreatedOrder(Client $client, int $orderToSendId): OrderToSend
    {
        $lastOrderToSend = null;

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $lastOrderToSend = $client->ordersToSend()->getOrderToSend($orderToSendId);

            if ($lastOrderToSend->status === 'error') {
                $this->fail('Order-to-send entered error status: ' . ($lastOrderToSend->error ?? 'unknown error'));
            }

            if ($lastOrderToSend->orderId !== null) {
                return $lastOrderToSend;
            }

            usleep(500000);
        }

        $this->fail(sprintf(
            'Timed out waiting for order_to_send %d to produce an order_id. Last status: %s',
            $orderToSendId,
            $lastOrderToSend?->status ?? 'unknown',
        ));

        throw new \RuntimeException('Unreachable.');
    }
}
