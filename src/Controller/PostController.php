<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class PostController extends AbstractController
{

    #[Route('/', name: 'posts.index', methods: ['GET'])]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findAllPosts(
            $request->query->getInt('page', 1)
        );

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/new', name: 'posts.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $post = new Post();
        $post->setTitle('Tytuł wpisu');
        $post->setContent('Zawartość wpisu');
        $post->setUser($this->getUser());
        $post->setCreatedAt(new \DateTimeImmutable('now'));
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest(($request));

        if ($form->isSubmitted() && $form->isValid()) {
//            $post = $form->getData();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('posts.index');
        }
        return $this->render('post/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/post/{id}', name: 'posts.show', methods: ['GET'])]
    public function show(Post $post, EntityManagerInterface $entityManager): Response
    {
        $isFollowing = $entityManager->getRepository(User::class)
            ->isFollowing($this->getUser(), $post->getUser()) ?? false;
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'isFollowing' => $isFollowing
        ]);
    }

    #[Route('/post/{id}/edit', name: 'posts.edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('posts.index');
        }
//        return $this->redirectToRoute('posts.index');
        return $this->render('post/edit.html.twig', ['form' => $form]);
    }

    #[Route('/post/{id}/delete', name: 'posts.delete', methods: ['POST'])]
    public function delete(Post $post, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();
        return $this->redirectToRoute('posts.index');
    }

    #[Route('/posts/user/{id}', name: 'posts.user', methods: ['GET'])]
    public function user(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $posts = $doctrine->getRepository(Post::class)
            ->findAllUserPosts($request->query->getInt('page', 1), $id);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'user' => $posts[0]?->getUser()->getName()
        ]);
    }

    #[Route('/toggleFollow/{user}', name: 'toggleFollow', methods: ['GET'])]
    public function toggleFollow(EntityManagerInterface $entityManager, User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $isFollowing = $entityManager->getRepository(User::class)
            ->isFollowing($this->getUser(), $user) ?? false;
        if ($isFollowing){
            $this->getUser()->removeFollowing($user);
        } else{
            $this->getUser()->addFollowing($user);
        }
        $entityManager->flush();
        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }
}