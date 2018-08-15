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

use GuzzleHttp\Client;

class QuadPayApiClient implements QuadPayApiClientInterface
{
    /** @var Client */
    protected $apiClient;

    /** @var string */
    protected $clientId;

    /** @var string */
    protected $clientSecret;

    /** @var string */
    protected $apiEndpoint;

    /** @var string */
    protected $authTokenEndpoint;

    /** @var string */
    protected $apiAudience;

    public function __construct(Client $client)
    {
        $this->apiClient = $client;
    }

    public function setConfig(
        string $clientId,
        string $clientSecret,
        string $apiEndpoint,
        string $authTokenEndpoint,
        string $apiAudience
    ): void {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->apiEndpoint = $apiEndpoint;
        $this->authTokenEndpoint = $authTokenEndpoint;
        $this->apiAudience = $apiAudience;
    }

    public function getOrderUrl(?string $orderToken = null): string
    {
        $url = sprintf('https://%s/order', parse_url($this->apiEndpoint)['host']);

        if (null !== $orderToken) {
            return sprintf('%s?token=%s', $url, $orderToken);
        }

        return $url;
    }

    public function getOauthTokenUrl(): string
    {
        return sprintf('https://%s/oauth/token', parse_url($this->authTokenEndpoint)['host']);
    }

    public function createAccessToken(): array
    {
        return $this->request('POST', $this->getOauthTokenUrl(), [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'audience' => $this->apiAudience,
            'grant_type' => 'client_credentials',
        ]);
    }

    public function createOrder(array $data): array
    {
        return $this->request('POST', $this->getOrderUrl(), $data, $this->createAccessToken()['access_token']);
    }

    public function getOrder(string $orderToken): array
    {
        return $this->request('GET', $this->getOrderUrl($orderToken), [], $this->createAccessToken()['access_token']);
    }

    protected function getHeaders(?string $accessToken = null): array
    {
        $headers =  [
            'Content-Type' => 'application/json',
        ];

        if (null !== $accessToken) {
            $headers['Authorization'] = sprintf('Bearer %s', $accessToken);
        }

        return $headers;
    }

    protected function request(string $method, string $url, array $data = [], ?string $accessToken = null): array
    {
        $options = [
            'json' => $data,
            'headers' => $this->getHeaders($accessToken),
        ];

        $result = $this->apiClient->request($method, $url, $options);

        return \GuzzleHttp\json_decode((string)$result->getBody(), true);
    }
}
