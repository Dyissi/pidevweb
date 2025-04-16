<?php

namespace App\Tests\Controller;

use App\Entity\Claimaction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClaimactionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $claimactionRepository;
    private string $path = '/claimaction/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->claimactionRepository = $this->manager->getRepository(Claimaction::class);

        foreach ($this->claimactionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Claimaction index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'claimaction[claimActionId]' => 'Testing',
            'claimaction[claimActionType]' => 'Testing',
            'claimaction[claimActionStartDate]' => 'Testing',
            'claimaction[claimActionEndDate]' => 'Testing',
            'claimaction[claimActionNotes]' => 'Testing',
            'claimaction[claimId]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->claimactionRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Claimaction();
        $fixture->setClaimActionId('My Title');
        $fixture->setClaimActionType('My Title');
        $fixture->setClaimActionStartDate('My Title');
        $fixture->setClaimActionEndDate('My Title');
        $fixture->setClaimActionNotes('My Title');
        $fixture->setClaimId('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Claimaction');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Claimaction();
        $fixture->setClaimActionId('Value');
        $fixture->setClaimActionType('Value');
        $fixture->setClaimActionStartDate('Value');
        $fixture->setClaimActionEndDate('Value');
        $fixture->setClaimActionNotes('Value');
        $fixture->setClaimId('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'claimaction[claimActionId]' => 'Something New',
            'claimaction[claimActionType]' => 'Something New',
            'claimaction[claimActionStartDate]' => 'Something New',
            'claimaction[claimActionEndDate]' => 'Something New',
            'claimaction[claimActionNotes]' => 'Something New',
            'claimaction[claimId]' => 'Something New',
        ]);

        self::assertResponseRedirects('/claimaction/');

        $fixture = $this->claimactionRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getClaimActionId());
        self::assertSame('Something New', $fixture[0]->getClaimActionType());
        self::assertSame('Something New', $fixture[0]->getClaimActionStartDate());
        self::assertSame('Something New', $fixture[0]->getClaimActionEndDate());
        self::assertSame('Something New', $fixture[0]->getClaimActionNotes());
        self::assertSame('Something New', $fixture[0]->getClaimId());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Claimaction();
        $fixture->setClaimActionId('Value');
        $fixture->setClaimActionType('Value');
        $fixture->setClaimActionStartDate('Value');
        $fixture->setClaimActionEndDate('Value');
        $fixture->setClaimActionNotes('Value');
        $fixture->setClaimId('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/claimaction/');
        self::assertSame(0, $this->claimactionRepository->count([]));
    }
}
