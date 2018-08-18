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

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function setClientId(string $clientId): void
    {
        $this->getDocument()->fillField('Client ID', $clientId);
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->getDocument()->fillField('Client Secret', $clientSecret);
    }

    public function setApiEndpoint(string $apiEndpoint): void
    {
        $this->getDocument()->fillField('API Endpoint', $apiEndpoint);
    }

    public function setAuthTokenEndpoint(string $authTokenEndpoint): void
    {
        $this->getDocument()->fillField('Auth Token Endpoint', $authTokenEndpoint);
    }

    public function setApiAudience(string $apiAudience): void
    {
        $this->getDocument()->fillField('API Audience', $apiAudience);
    }

    public function setMinimumAmount(string $minimumAmount): void
    {
        $this->getDocument()->fillField('Minimum amount', $minimumAmount);
    }

    public function setMaximumAmount(string $maximumAmount): void
    {
        $this->getDocument()->fillField('Maximum amount', $maximumAmount);
    }

    public function containsErrorWithMessage(string $message, bool $strict = true): bool
    {
        $validationMessageElements = $this->getDocument()->findAll('css', '.sylius-validation-error');
        $result = false;

        /** @var NodeElement $validationMessageElement */
        foreach ($validationMessageElements as $validationMessageElement) {
            if (true === $strict && $message === $validationMessageElement->getText()) {
                return true;
            }

            if (false === $strict && strstr($validationMessageElement->getText(), $message)) {
                return true;
            }
        }

        return $result;
    }
}
