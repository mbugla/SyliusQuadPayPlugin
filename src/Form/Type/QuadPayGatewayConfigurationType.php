<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

final class QuadPayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientId', TextType::class, [
                'label' => 'bitbag_sylius_quadpay_plugin.ui.client_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_quadpay_plugin.client_id.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('clientSecret', TextType::class, [
                'label' => 'bitbag_sylius_quadpay_plugin.ui.client_secret',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_quadpay_plugin.client_secret.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('apiEndpoint', TextType::class, [
                'label' => 'bitbag_sylius_quadpay_plugin.ui.api_endpoint',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_quadpay_plugin.api_endpoint.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('authTokenEndpoint', TextType::class, [
                'label' => 'bitbag_sylius_quadpay_plugin.ui.auth_token_endpoint',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_quadpay_plugin.auth_token_endpoint.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('apiAudience', TextType::class, [
                'label' => 'bitbag_sylius_quadpay_plugin.ui.api_audience',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bitbag_sylius_quadpay_plugin.api_audience.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();

                $data['payum.http_client'] = '@bitbag_sylius_quadpay_plugin.quadpay_api_client';

                $event->setData($data);
            })
        ;
    }
}
