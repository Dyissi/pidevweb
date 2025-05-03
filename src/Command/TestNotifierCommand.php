<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Psr\Log\LoggerInterface;

#[AsCommand(name: 'app:test-notifier')]
class TestNotifierCommand extends Command
{
    private $notifier;
    private $logger;

    public function __construct(NotifierInterface $notifier, LoggerInterface $logger)
    {
        parent::__construct();
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notification = new Notification('Test OTP: 123456', ['sms']);
        $recipient = new Recipient('', '+21627100103');
        try {
            $this->notifier->send($notification, $recipient);
            $output->writeln('SMS sent to +21627100103');
            $this->logger->info('Test notifier SMS sent', ['user_nbr' => '+21627100103']);
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            $this->logger->error('Test notifier SMS failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_nbr' => '+21627100103'
            ]);
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}