<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Validator\Constraints;

use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class OrderAmountValidator extends ConstraintValidator
{
    /**
     * @param PaymentInterface $payment
     * @param Constraint|OrderAmount $constraint
     *
     * {@inheritdoc}
     */
    public function validate($payment, Constraint $constraint): void
    {
        Assert::isInstanceOf($payment, PaymentInterface::class);

        Assert::isInstanceOf($constraint, OrderAmount::class);

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        if (null === $paymentMethod) {

            return;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (null === $gatewayConfig || ($gatewayConfig->getFactoryName() !== QuadPayGatewayFactory::FACTORY_NAME)) {

            return;
        }

        $config = $gatewayConfig->getConfig();

        $minimumAmount = $config['minimumAmount'];
        $maximumAmount = $config['maximumAmount'];

        if ($minimumAmount > $payment->getAmount()) {
            $this->context->buildViolation($constraint->minimumAmountMessage, [
                '{{ minimumAmount }}' => number_format(abs($minimumAmount / 100), 2, '.', ','),
            ])->atPath('method')->addViolation();
        }

        if ($maximumAmount < $payment->getAmount()) {
            $this->context->buildViolation($constraint->maximumAmountMessage, [
                '{{ maximumAmount }}' => number_format(abs($maximumAmount / 100), 2, '.', ','),
            ])->atPath('method')->addViolation();
        }
    }
}
