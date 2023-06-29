<?php declare(strict_types=1);

namespace App\Form\Type;

use App\Repository\PlayerRepository;
use App\Security\SteamUser;
use SteamID\SteamID;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayerType extends AbstractType
{
    public function __construct(
        private Security $security,
        private PlayerRepository $playerRepository,
        private TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('steamid', SteamIdType::class, [
                'required' => true,

                'label' => 'form.steam_id.label',
                'help' => 'form.steam_id.help',
                'attr' => [
                    'placeholder' => 'form.steam_id.placeholder',
                    'autocomplete' => 'off',
                ],

                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])

            ->add('description', TextType::class, [
                'required' => false,

                'label' => 'form.description.label',
                'help' => 'form.description.help',
                'attr' => [
                    'placeholder' => 'form.description.placeholder',
                    'autocomplete' => 'off',
                ],

                'constraints' => [
                    new Assert\Length(max: 255),
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'form.submit',
            ])
        ;

        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof SteamUser) {
            throw new \LogicException('This form should only be used in an authenticated context.');
        }

        $builder->get('steamid')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($currentUser) {
            $data = $event->getData();

            if (!$data instanceof SteamID) {
                return;
            }

            $existing = $this->playerRepository->findOneBy(['belongsTo' => $currentUser->getId(), 'steamId' => $data->getSteamID64()]);

            if (null !== $existing) {
                $error = new FormError(
                    $this->translator->trans('id_is_already_tracked', ['%steamId%' => $data->getSteamID64()], 'player'),
                    cause: 'player.id_is_already_tracked'
                );
                $error->setOrigin($event->getForm());

                $event->getForm()->addError($error);
            }
        }, -1);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'player',
        ]);
    }
}
