<?php

namespace App\Tests\Controller;

use App\Entity\Claim;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClaimControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $claimRepository;
    private string $path = '/claim/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->claimRepository = $this->manager->getRepository(Claim::class);

        foreach ($this->claimRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Claim index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'claim[claimId]' => 'Testing',
            'claim[claimDescription]' => 'Testing',
            'claim[claimStatus]' => 'Testing',
            'claim[claimDate]' => 'Testing',
            'claim[claimCategory]' => 'Testing',
            'claim[id_user]' => 'Testing',
            'claim[id_user_to_claim]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->claimRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Claim();
        $fixture->setClaimId('My Title');
        $fixture->setClaimDescription('My Title');
        $fixture->setClaimStatus('My Title');
        $fixture->setClaimDate('My Title');
        $fixture->setClaimCategory('My Title');
        $fixture->setId_user('My Title');
        $fixture->setId_user_to_claim('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Claim');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Claim();
        $fixture->setClaimId('Value');
        $fixture->setClaimDescription('Value');
        $fixture->setClaimStatus('Value');
        $fixture->setClaimDate('Value');
        $fixture->setClaimCategory('Value');
        $fixture->setId_user('Value');
        $fixture->setId_user_to_claim('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'claim[claimId]' => 'Something New',
            'claim[claimDescription]' => 'Something New',
            'claim[claimStatus]' => 'Something New',
            'claim[claimDate]' => 'Something New',
            'claim[claimCategory]' => 'Something New',
            'claim[id_user]' => 'Something New',
            'claim[id_user_to_claim]' => 'Something New',
        ]);

        self::assertResponseRedirects('/claim/');

        $fixture = $this->claimRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getClaimId());
        self::assertSame('Something New', $fixture[0]->getClaimDescription());
        self::assertSame('Something New', $fixture[0]->getClaimStatus());
        self::assertSame('Something New', $fixture[0]->getClaimDate());
        self::assertSame('Something New', $fixture[0]->getClaimCategory());
        self::assertSame('Something New', $fixture[0]->getId_user());
        self::assertSame('Something New', $fixture[0]->getId_user_to_claim());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Claim();
        $fixture->setClaimId('Value');
        $fixture->setClaimDescription('Value');
        $fixture->setClaimStatus('Value');
        $fixture->setClaimDate('Value');
        $fixture->setClaimCategory('Value');
        $fixture->setId_user('Value');
        $fixture->setId_user_to_claim('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/claim/');
        self::assertSame(0, $this->claimRepository->count([]));
    }
}
