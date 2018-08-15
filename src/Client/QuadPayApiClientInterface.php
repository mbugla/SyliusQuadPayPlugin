<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Client;

interface QuadPayApiClientInterface
{
    public const STATUS_CREATED = 'created';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_ABANDONED = 'abandoned';

    public function setConfig(
        string $clientId,
        string $clientSecret,
        string $apiEndpoint,
        string $authTokenEndpoint,
        string $apiAudience
    ): void;

    public function getOrderUrl(?string $orderToken = null): string;

    public function getOauthTokenUrl(): string;

    public function createAccessToken(): array;

    public function createOrder(array $data): array;

    public function getOrder(string $orderToken): array;
}
