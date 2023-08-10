<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookFilterType;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/", name="app_book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository, Request $request): Response
    {
        $form = $this->createForm(BookFilterType::class, null, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);

        $repository = $this->getDoctrine()
            ->getRepository(Book::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $filterData = $this->getFormDataArray($form);
            $ids = $repository->findBySearchData($filterData);

            if (!empty($filterData)) {
                $books = (!empty($ids)) ? $bookRepository->findBy(['id' => $ids]) : [];
            } else {
                $books = $bookRepository->findAll();
            }

            return $this->render('book/index.html.twig', [
                'books' => $books,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="app_book_new", methods={"GET", "POST"})
     */
    public function new(Request $request, BookRepository $bookRepository, FileUploader $fileUploader): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $fileName = $fileUploader->upload($imageFile);
                $book->setImage($fileName);
            }

            $bookRepository->add($book);
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_book_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Book $book, BookRepository $bookRepository, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $fileName = $fileUploader->upload($imageFile);
                $book->setImage($fileName);
            }

            $bookRepository->add($book);
            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_book_delete", methods={"POST"})
     */
    public function delete(Request $request, Book $book, BookRepository $bookRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            if ($book->getImage()) {
                unlink($this->getParameter('images_directory') . '/' . $book->getImage());
            }
            $bookRepository->remove($book);
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Метод для вывода из формы массива полей и их значений.
     * Метод немного не корректен, т.к. можно было придумать рекурсию, но при моей реализации с ManyToMany свойствами не было возможным это придумать
     */
    private function getFormDataArray($form)
    {
        $data = [];
        foreach ( $form as $key => $value) {
            if (is_object($value->getData())) {
                $buf = [];
                $object = $value->getData();
                foreach ($object as $item) {
                    $buf[] = $item->getId();
                }

                if (!empty($buf)) {
                    $data[$key] = $buf;
                }
            } else {
                if ($value->getData()) {
                    $data[$key] = $value->getData();
                }
            }
        }

        return $data;
    }
}