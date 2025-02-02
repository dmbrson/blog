<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Filter\BlogFilter;
use App\Form\BlogFilterType;
use App\Form\BlogType;
use App\Message\ContentWatchJob;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('user/blog')]
final class BlogController extends AbstractController
{
    #[Route('/', name: 'app_user_blog_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, BlogRepository $blogRepository): Response
    {
        $blogFilter = new BlogFilter();

        // Получаем текущего пользователя
        $user = $this->getUser();

        // Если пользователь не админ, фильтруем блоги только по нему
        if (!$this->isGranted('ROLE_ADMIN')) {
            $blogFilter->setUser($user);
        }

        $form = $this->createForm(BlogFilterType::class, $blogFilter);
        $form->handleRequest($request);

        $pagination = $paginator->paginate(
            $blogRepository->findByBlogFilter($blogFilter), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5
        );

        return $this->render('blog/index.html.twig', [
            'blogs' => $pagination,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_user_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        MessageBusInterface $bus,
    ): Response {
        $blog = new Blog($this->getUser());

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            $bus->dispatch(new ContentWatchJob($blog->getId()));

            return $this->redirectToRoute('app_user_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[IsGranted('edit', 'blog')]
    #[Route('/{id}/edit', name: 'app_user_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[IsGranted('edit', 'blog')]
    #[Route('/delete/{id}', name: 'app_user_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
            $this->addFlash('success', 'Blog deleted successfully.');
        }

        return $this->redirectToRoute('app_user_blog_index', [], Response::HTTP_SEE_OTHER);
    }
}
