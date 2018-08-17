<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Mocker;

use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use BitBag\SyliusQuadPayPlugin\PaymentProcessing\PaymentProcessorInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Sylius\Behat\Service\Mocker\MockerInterface;

final class QuadPayApiMocker
{
    /** @var MockerInterface */
    private $mocker;

    public function __construct(MockerInterface $mocker)
    {
        $this->mocker = $mocker;
    }

    public function mockApiCreatePayment(callable $action): void
    {
        $mockService = $this->mocker
            ->mockService('bitbag_sylius_quadpay_plugin.quadpay_api_client', QuadPayApiClientInterface::class)
        ;

        $mockService
            ->shouldReceive('createOrder')
            ->andReturn([
                'redirectUrl' => 'https://checkout.quadpay.com/checkout',
                'token' => 'test',
            ])
        ;

        $mockService->shouldReceive('setConfig');

        $action();

        $this->mocker->unmockService('bitbag_sylius_quadpay_plugin.quadpay_api_client');
    }

    public function mockApiSuccessfulPayment(callable $action): void
    {
        $mockService = $this->mocker
            ->mockService('bitbag_sylius_quadpay_plugin.quadpay_api_client', QuadPayApiClientInterface::class)
        ;

        $mockService
            ->shouldReceive('getOrderByToken', 'getOrderById')
            ->andReturn([
                'orderStatus' => QuadPayApiClientInterface::STATUS_APPROVED,
                'orderId' => 'test',
            ])
        ;

        $mockService->shouldReceive('setConfig');

        $action();

        $this->mocker->unmockService('bitbag_sylius_quadpay_plugin.quadpay_api_client');
    }

    public function mockApiCancelledPayment(callable $action): void
    {
        $mockService = $this->mocker
            ->mockService('bitbag_sylius_quadpay_plugin.quadpay_api_client', QuadPayApiClientInterface::class)
        ;

        $mockService
            ->shouldReceive('getOrderByToken')
            ->andThrow(new ClientException('', new Request('GET', ''), new \GuzzleHttp\Psr7\Response(404)))
        ;

        $mockService->shouldReceive('setConfig');

        $action();

        $this->mocker->unmockService('bitbag_sylius_quadpay_plugin.quadpay_api_client');
    }

    public function mockApiRefundedPayment(callable $action): void
    {
        $mockService = $this->mocker
            ->mockService('bitbag_sylius_quadpay_plugin.payment_processing.refund', PaymentProcessorInterface::class)
        ;

        $mockService
            ->shouldReceive('refund')
            ->andReturn([])
        ;

        $mockService->shouldReceive('process');

        $action();

        $this->mocker->unmockService('bitbag_sylius_quadpay_plugin.payment_processing.refund');
    }
}
