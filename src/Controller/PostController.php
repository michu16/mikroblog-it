<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class PostController extends AbstractController
{

    #[Route('/', name: 'posts.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('post/index.html.twig');
    }

    #[Route('/post/new', name: 'posts.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $post = new Post();
//        $post->setTitle('Tytuł wpisu');
//        $post->setContent('Zawartość wpisu');
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest(($request));

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            return $this->redirectToRoute('posts.index');
        }
        return $this->render('post/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/post/{id}', name: 'posts.show', methods: ['GET'])]
    public function show($id): Response
    {
        return $this->render('post/show.html.twig');
    }

    #[Route('/post/{id}/edit', name: 'posts.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            return $this->redirectToRoute('posts.index');
        }
//        return $this->redirectToRoute('posts.index');
        return $this->render('post/edit.html.twig', ['form' => $form]);
    }

    #[Route('/post/{id}/delete', name: 'posts.delete', methods: ['POST'])]
    public function delete($id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return new Response('Usuwanie posta z bazy danych');
    }

    #[Route('/posts/user/{id}', name: 'posts.user', methods: ['GET'])]
    public function user($id): Response
    {
        return $this->render('post/index.html.twig');
    }

    #[Route('/toggleFollow/{user}', name: 'toggleFollow', methods: ['GET'])]
    public function toggleFollow($user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return new Response('logika dla przełączania funkcjonalności like/dislike');
    }
}