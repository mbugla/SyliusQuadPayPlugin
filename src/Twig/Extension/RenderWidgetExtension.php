<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Twig\Extension;

use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use BitBag\SyliusQuadPayPlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

final class RenderWidgetExtension extends \Twig_Extension
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var EngineInterface */
    private $templatingEngine;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        EngineInterface $templatingEngine
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->templatingEngine = $templatingEngine;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_Function('bitbag_quadpay_render_widget', [$this, 'renderQuadPayWidget'], ['is_safe' => ['html']]),
        ];
    }

    public function renderQuadPayWidget(int $amount, ChannelInterface $channel, PaymentMethodInterface $paymentMethod = null): string
    {
        if (null === $paymentMethod) {
            $paymentMethod = $this->paymentMethodRepository->findOneByGatewayFactoryNameAndChannel(QuadPayGatewayFactory::FACTORY_NAME, $channel);
        }

        if (null === $paymentMethod) {

            return '';
        }

        $config = $paymentMethod->getGatewayConfig()->getConfig();

        return $this->templatingEngine->render('@BitBagSyliusQuadPayPlugin/_widget.html.twig', [
            'amount' => $amount,
            'paymentMethod' => $paymentMethod,
            'minAmount' => $config['minimumAmount'],
            'maxAmount' => $config['maximumAmount'],
        ]);
    }
}
