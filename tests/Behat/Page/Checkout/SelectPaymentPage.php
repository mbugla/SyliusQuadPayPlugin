<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Page\Checkout;

use Behat\Mink\Driver\Selenium2Driver;
use Sylius\Behat\Page\Shop\Checkout\SelectPaymentPageInterface;
use Sylius\Behat\Page\Shop\Checkout\SelectPaymentPage as BaseSelectPaymentPage;

class SelectPaymentPage extends BaseSelectPaymentPage implements SelectPaymentPageInterface
{
    public function selectPaymentMethod($paymentMethod): void
    {
        if ($this->getDriver() instanceof Selenium2Driver) {
            $this->getElement('payment_method_select', ['%payment_method%' => $paymentMethod])->click();

            return;
        }

        $paymentMethodOptionElement = $this->getElement('payment_method_select_value', ['%payment_method%' => strtolower($paymentMethod)]);
        $paymentMethodOptionElement->selectOption($paymentMethodOptionElement->getAttribute('value'));
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'address_step_label' => '.steps a:contains("Address")',
            'checkout_subtotal' => '#sylius-checkout-subtotal',
            'next_step' => '#next-step',
            'order_cannot_be_paid_message' => '#sylius-order-cannot-be-paid',
            'payment_method_option' => '.item:contains("%payment_method%") input',
            'payment_method_select' => '.item:contains("%payment_method%") > .field > .ui.radio.checkbox',
            'payment_method_select_value' => 'input[value="%payment_method%"]',
            'shipping_step_label' => '.steps a:contains("Shipping")',
            'warning_no_payment_methods' => '#sylius-order-cannot-be-paid',
        ]);
    }
}
