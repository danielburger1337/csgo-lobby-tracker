<?php declare(strict_types=1);

namespace App\Form\Type;

use App\Service\SteamIdService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SteamIdType extends AbstractType
{
    public function __construct(
        private readonly SteamIdService $steamIdService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (empty($data)) {
                    return;
                }

                $steamId = $this->steamIdService->resolveSteamId($data);

                if (null === $steamId) {
                    $formError = new FormError('Invalid steam id.', cause: 'invalid_steam_id');

                    $event->getForm()->addError($formError);

                    return;
                }

                $event->setData($steamId);
            });
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
