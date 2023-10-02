<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ostatnie wpisy');
    }

    public function testSeeContent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Rejestracja');
        $this->assertSelectorTextNotContains('h1', 'abcd');
    }

    public function testCreatePost(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test uuser

        $testUser = $userRepository->findOneByEmail('ohills@homenick.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $post = new Post();
        $post->setUser($testUser);
        $post->setCreatedAt(new \DateTimeImmutable('now'));
        $post->setTitle('tytul postu');
        $post->setContent('post conent');

        $entityManager->persist($post);
        $entityManager->flush();

        // test e.g. the profile page

        $client->request('GET', "/post/{$post->getId()}/edit");
        $this->assertInputValueSame('post[title]', 'tytul postu');
    }

    public function testDatabaseCount(): void
    {
        $postRepository = static::getContainer()->get(PostRepository::class);
        $totalPosts = $postRepository->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(16, $totalPosts);

    }

//    public function testAddPost(): void
//    {
//        $client = static::createClient();
//        $userRepository = static::getContainer()->get(UserRepository::class);
//
//        // retrieve the test user
//
//        $testUser = $userRepository->findOneByEmail('ohills@homenick.com');
//
//        // simulate $testUser being logged in
//        $client->loginUser($testUser);
//        $crawler = $client->request('GET', "/dashboard/profile");
//        $this->assertSelectorTextContains('h2', 'Informacje o profilu' );
//        $form = $crawler->selectButton('Save')->form([
//            'user_form[name]' => 'new name',
//        ]);
//        $client->submit($form);
//        $us = $userRepository->findOneBy([
//            'name' => 'new name'
//        ]);
//        $this->assertNotNull($us);
//        $this->assertSame('new name', $us->getName());
//    }

    public function testApiRegister(): void
    {
        $client = static::createClient();
        $client->request('POST', 'api/register', [], [], ['CONTENT_TYPE' => 'application/json'],
            '{"name":"name","email":"email", "password":"password"}');
        $this->assertResponseIsSuccessful();
    }

    public function testApiLoginAddPost(): void
    {
        $client = static::createClient();
        $client->request('POST', 'api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'],
            '{"username":"ohills@homenick.com","password":"ohills@homenick.com"}');
        $response = $client->getResponse();
//        dump(json_decode($response->getContent(), true)['token']);
//        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());
        $client->request('POST', '/api/post/new', [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => 'Bearer ' . json_decode($response->getContent(), true)['token']],
            '{"title":"post title", "content":"post content"}');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
    }
}
