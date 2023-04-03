<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\MyAbstractController;
use App\Repository\PlayerRepository;
use App\Service\PlayerService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class SummaryController extends MyAbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private PlayerService $playerService
    ) {
    }

    #[Route('/api/v1/summary', 'api_v1_summary')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED);

        $players = $this->playerRepository->findBy(['belongsTo' => $this->getSteamUser()->getId()]);

        $summaries = $this->playerService->createSummary($players);

        return $this->apiResponse(
            msg: $this->trans('found_x_summaries', ['%count%' => \count($summaries)]),
            data: $summaries,
            context: [
                'groups' => ['player', 'playersummary', 'miniprofile', 'test'],
            ]
        );
    }
}
