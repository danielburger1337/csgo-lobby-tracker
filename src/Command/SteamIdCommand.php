<?php declare(strict_types=1);

namespace App\Command;

use App\Service\SteamIdService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:steam:id', 'Resolve a steam user identifier to the 64-bit SteamID.')]
class SteamIdCommand extends Command
{
    public function __construct(
        private SteamIdService $steamIdService
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument('steamid', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $steamIdInput = $input->getArgument('steamid');
        $steamId = $this->steamIdService->resolveSteamId($steamIdInput);

        if (null === $steamId) {
            $output->writeln(\sprintf('<error>"%s" is not a valid steam id.</error>', $steamIdInput));

            return Command::INVALID;
        }

        $output->writeln($steamId->getSteamID64());

        return Command::SUCCESS;
    }
}
