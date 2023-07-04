<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\PlayerType;
use App\Repository\PlayerRepository;
use App\Service\PlayerService;
use App\Service\SteamIdService;
use App\Service\SteamWebApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class MainController extends MyAbstractController
{
    public function __construct(
        private readonly PlayerService $playerService,
        private readonly PlayerRepository $playerRepository,
        private readonly SteamWebApiService $steamWebApiService
    ) {
    }

    #[Route('/', name: 'app_home', methods: 'GET')]
    public function home(Request $request, SteamIdService $steamIdService): Response
    {
        if (!$this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED)) {
            return $this->render('login.html.twig');
        }

        $steamId = $request->query->getString('steamId', $this->getSteamUser()->getUserIdentifier());
        $steamId = $steamIdService->resolveSteamId($steamId);
        $steamId ??= $this->getSteamUser()->getUserIdentifier();
        $steamId = (string) $steamId;

        $players = $this->playerRepository->findBy(['belongsTo' => (string) $steamId]);

        return $this->render('app.html.twig', [
            'summaries' => $this->playerService->createSummary($players),
            'steamId' => $steamId === $this->getSteamUser()->getUserIdentifier() ? null : $steamId,
        ]);
    }

    #[Route('/manage-players', name: 'app_manage_players', methods: ['GET', 'POST'])]
    public function addPlayer(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED);

        $form = $this->createForm(PlayerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $player = $this->playerService
                ->createPlayer($this->getSteamUser()->getId(), $data['steamid'], $data['description'] ?? null)
            ;

            $this->addFlash('success', 'The player "'.$player->getSteamId()->getSteamID64().'" was added successfully.');

            return $this->redirectToRoute('app_manage_players');
        }

        $trackedPlayers = $this->playerRepository->findBy(['belongsTo' => $this->getSteamUser()->getId()]);
        $summaries = $this->steamWebApiService->fetchPlayerSummaries($trackedPlayers);

        return $this->render('manage_players.html.twig', [
            'form' => $form,
            'existing_players' => $summaries,
        ]);
    }
}
