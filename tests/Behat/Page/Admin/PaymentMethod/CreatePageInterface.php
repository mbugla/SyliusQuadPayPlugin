<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setClientId(string $clientId): void;

    public function setClientSecret(string $clientSecret): void;

    public function setApiEndpoint(string $apiEndpoint): void;

    public function setAuthTokenEndpoint(string $authTokenEndpoint): void;

    public function setApiAudience(string $apiAudience): void;

    public function setMinimumAmount(string $minimumAmount): void;

    public function setMaximumAmount(string $maximumAmount): void;

    public function containsErrorWithMessage(string $message, bool $strict = true): bool;
}
