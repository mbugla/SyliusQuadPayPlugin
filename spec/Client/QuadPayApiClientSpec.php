<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusQuadPayPlugin\Client;

use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClient;
use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;

final class QuadPayApiClientSpec extends ObjectBehavior
{
    function let(ClientInterface $client): void
    {
        $this->beConstructedWith($client);

        $this->setConfig(
            'test',
            'test',
            'https://api-ci.quadpay.com/',
            'https://api-ci.quadpay.com/',
            'https://api-ci.quadpay.com/'
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(QuadPayApiClient::class);
    }

    function it_implements_quadpay_api_client_interface(): void
    {
        $this->shouldHaveType(QuadPayApiClientInterface::class);
    }

    function it_creates_order(ClientInterface $client, ResponseInterface $tokenResponse, ResponseInterface $orderResponse): void
    {
        $tokenResponse->getBody()->willReturn('{
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciO",
            "expires_in": 86400,
            "scope": "read:me merchant",
            "token_type": "Bearer"
        }');

        $orderResponse->getBody()->willReturn('{"orderId": "test"}');

        $client->request(
            'POST',
            'https://api-ci.quadpay.com/oauth/token',
            [
                'json' => [
                    'client_id' => 'test',
                    'client_secret' => 'test',
                    'audience' => 'https://api-ci.quadpay.com/',
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        )->willReturn($tokenResponse);

        $client->request(
            'POST',
            'https://api-ci.quadpay.com/order',
            [
                'json' => [
                    'test' => 'test',
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciO',
                ],
            ]
        )->willReturn($orderResponse);

        $this->createOrder(['test' => 'test'])->shouldReturn(['orderId' => 'test']);
    }

    function it_gets_order_by_id(ClientInterface $client, ResponseInterface $tokenResponse, ResponseInterface $orderResponse): void
    {
        $tokenResponse->getBody()->willReturn('{
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciO",
            "expires_in": 86400,
            "scope": "read:me merchant",
            "token_type": "Bearer"
        }');

        $client->request(
            'POST',
            'https://api-ci.quadpay.com/oauth/token',
            [
                'json' => [
                    'client_id' => 'test',
                    'client_secret' => 'test',
                    'audience' => 'https://api-ci.quadpay.com/',
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        )->willReturn($tokenResponse);

        $orderResponse->getBody()->willReturn('{"orderId": "h4897t4htye8iype"}');

        $client->request(
            'GET',
            'https://api-ci.quadpay.com/order/h4897t4htye8iype',
            [
                'json' => [],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciO',
                ],
            ]
        )->willReturn($orderResponse);

        $this->getOrderById('h4897t4htye8iype')->shouldReturn(['orderId' => 'h4897t4htye8iype']);
    }

    function it_gets_order_by_token(ClientInterface $client, ResponseInterface $tokenResponse, ResponseInterface $orderResponse): void
    {
        $tokenResponse->getBody()->willReturn('{
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciO",
            "expires_in": 86400,
            "scope": "read:me merchant",
            "token_type": "Bearer"
        }');

        $client->request(
            'POST',
            'https://api-ci.quadpay.com/oauth/token',
            [
                'json' => [
                    'client_id' => 'test',
                    'client_secret' => 'test',
                    'audience' => 'https://api-ci.quadpay.com/',
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        )->willReturn($tokenResponse);

        $orderResponse->getBody()->willReturn('{"orderId": "h4897t4htye8iype"}');

        $client->request(
            'GET',
            'https://api-ci.quadpay.com/order?token=h4897t4htye8iype',
            [
                'json' => [],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciO',
                ],
            ]
        )->willReturn($orderResponse);

        $this->getOrderByToken('h4897t4htye8iype')->shouldReturn(['orderId' => 'h4897t4htye8iype']);
    }

    function it_refunds(
        ClientInterface $client,
        ResponseInterface $tokenResponse,
        ResponseInterface $orderResponse,
        ResponseInterface $refundResponse
    ): void {
        $tokenResponse->getBody()->willReturn('{
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciO",
            "expires_in": 86400,
            "scope": "read:me merchant",
            "token_type": "Bearer"
        }');

        $client->request(
            'POST',
            'https://api-ci.quadpay.com/oauth/token',
            [
                'json' => [
                    'client_id' => 'test',
                    'client_secret' => 'test',
                    'audience' => 'https://api-ci.quadpay.com/',
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        )->willReturn($tokenResponse);

        $orderResponse->getBody()->willReturn('{"orderId": "h4897t4htye8iype"}');

        $client->request(
            'GET',
            'https://api-ci.quadpay.com/order?token=gf74yr4iuyti4hjtikht',
            [
                'json' => [],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciO',
                ],
            ]
        )->willReturn($orderResponse);

        $refundResponse->getBody()->willReturn('{"refundId": "test"}');

        $client->request(
            'POST',
            'https://api-ci.quadpay.com/order/h4897t4htye8iype/refund',
            [
                'json' => [
                    'amount' => 20.77,
                    'merchantRefundReference' => 'hfeirjtlegjktejio',
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciO',
                ],
            ]
        )->willReturn($refundResponse);

        $this->refund(20.77, 'hfeirjtlegjktejio', 'gf74yr4iuyti4hjtikht')->shouldReturn([
            'refundId' => 'test',
        ]);
    }
}
