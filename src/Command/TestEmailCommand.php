<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[AsCommand(name: 'app:test-email')]
class TestEmailCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $testEmail = 'drissiasma44@gmail.com'; // Replace with user-provided email
            $email = (new TemplatedEmail())
                ->from('amorrions@gmail.com')
                ->to($testEmail)
                ->subject('Test Email - SPIN Account Details')
                ->htmlTemplate('emails/registration.html.twig')
                ->context([
                    'user' => ['userFname' => 'Test User', 'userEmail' => $testEmail],
                    'plainPassword' => 'test123',
                    'loginUrl' => 'http://localhost:8000/login',
                ]);
            $this->mailer->send($email);
            $output->writeln('Email sent successfully to ' . $testEmail . '.');
        } catch (TransportExceptionInterface $e) {
            $output->writeln('Transport Error: ' . $e->getMessage() . ' [Code: ' . $e->getCode() . ']');
            $output->writeln('Trace: ' . $e->getTraceAsString());
        } catch (\Exception $e) {
            $output->writeln('Unexpected Error: ' . $e->getMessage() . ' [Code: ' . $e->getCode() . ']');
            $output->writeln('Trace: ' . $e->getTraceAsString());
        }
        return Command::SUCCESS;
    }
}