<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusQuadPayPlugin\Twig\Extension;

use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use BitBag\SyliusQuadPayPlugin\Repository\PaymentMethodRepositoryInterface;
use BitBag\SyliusQuadPayPlugin\Twig\Extension\RenderWidgetExtension;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

final class RenderWidgetExtensionSpec extends ObjectBehavior
{
    function let(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        EngineInterface $templatingEngine
    ): void {
        $this->beConstructedWith($paymentMethodRepository, $templatingEngine);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(RenderWidgetExtension::class);
    }

    function it_extends_twig_extension(): void
    {
        $this->shouldHaveType(\Twig_Extension::class);
    }

    function it_returns_functions(): void
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(1);

        /** @var \Twig_SimpleFunction $function */
        $function = $functions[0];

        $function->shouldHaveType(\Twig_SimpleFunction::class);

        $function->getName()->shouldReturn('bitbag_quadpay_render_widget');
    }

    function it_renders_quadpay_widget(
        ChannelInterface $channel,
        EngineInterface $templatingEngine,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig
    ): void {
        $gatewayConfig->getConfig()->willReturn([
            'minimumAmount' => 300,
            'maximumAmount' => 3000,
        ]);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $paymentMethodRepository->findOneByGatewayFactoryNameAndChannel(QuadPayGatewayFactory::FACTORY_NAME, $channel)->willReturn($paymentMethod);
        $templatingEngine->render('@BitBagSyliusQuadPayPlugin/_widget.html.twig', [
            'amount' => 100,
            'paymentMethod' => $paymentMethod,
            'minAmount' => 300,
            'maxAmount' => 3000,
        ])->willReturn('<div>BitBag</div>');

        $this->renderQuadPayWidget(100, $channel)->shouldReturn('<div>BitBag</div>');
    }
}
