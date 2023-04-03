<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\MyAbstractController;
use App\Form\Type\PlayerType;
use App\Repository\PlayerRepository;
use App\Security\Voter\PlayerVoter;
use App\Service\PlayerService;
use SBSEDV\Bundle\ResponseBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

#[Route('/api/v1/players', name: 'api_v1_player_')]
class PlayerController extends MyAbstractController
{
    public function __construct(
        private PlayerService $playerService,
        private PlayerRepository $playerRepository
    ) {
    }

    #[Route('', name: 'list', methods: 'GET')]
    public function listPlayers(): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED);

        $currentUser = $this->getSteamUser();

        $players = $this->playerRepository->findBy(['belongsTo' => $currentUser->getId()]);

        return $this->apiResponse(
            msg: $this->trans('found_x', ['%count%' => \count($players)], 'player'),
            data: $players,
            status: Response::HTTP_CREATED,
            context: ['groups' => ['player']]
        );
    }

    #[Route('', name: 'create', methods: 'POST')]
    public function createPlayer(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED);

        $currentUser = $this->getSteamUser();

        $form = $this->createForm(PlayerType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $data = $form->getData();

        $player = $this->playerService
            ->createPlayer($currentUser->getId(), $data['steamid'], $data['description'] ?? null)
        ;

        return $this->apiResponse(
            msg: $this->trans('created_successfully', ['%steamId%' => $data['steamid']], 'player'),
            data: $player,
            status: Response::HTTP_CREATED,
            context: ['groups' => ['player']]
        );
    }

    #[Route('/{playerId}', name: 'delete', methods: 'DELETE', requirements: ['playerId' => Requirement::DIGITS])]
    public function removePlayer(int $playerId): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED);

        $player = $this->playerService->findOrFail($playerId);

        $this->denyAccessUnlessGranted(PlayerVoter::ATTRIBUTE_DELETE, $player);

        $this->playerRepository->remove($player, true);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
