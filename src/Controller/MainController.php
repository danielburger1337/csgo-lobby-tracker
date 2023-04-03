<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\PlayerType;
use App\Model\TestModel;
use App\Repository\PlayerRepository;
use App\Service\PlayerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class MainController extends MyAbstractController
{
    public function __construct(
        private PlayerService $playerService,
        private PlayerRepository $playerRepository
    ) {
    }

    #[Route('/', name: 'app_home', methods: 'GET')]
    public function home(): Response
    {
        if (!$this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED)) {
            return $this->render('login.html.twig');
        }

        $players = $this->playerRepository->findBy(['belongsTo' => $this->getSteamUser()->getId()]);

        $summaries = $this->playerService->createSummary($players);

        \usort($summaries, function (TestModel $a) {
            if (\str_starts_with($a->miniProfile->richPresence ?? '', 'In Lobby')) {
                return -1;
            }

            return 1;
        });

        return $this->render('app.html.twig', [
            'summaries' => $summaries,
        ]);
    }

    #[Route('/add-player', name: 'app_player_add', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_player_add');
        }

        return $this->render('add_player.html.twig', [
            'form' => $form,
        ]);
    }
}
