<?php

namespace App\EventListener;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;

class BookChangedListener
{
    public function postPersist(Book $book, PostPersistEventArgs $event)
    {
        if ('cli' != php_sapi_name()) {
            $eventManager = $event->getObjectManager();

            foreach ($book->getAuthors() as $author) {
                $author->setCountBooks($author->getCountBooks() + 1);
            }

            $eventManager->flush();
        }
    }

    public function preRemove(Book $book, PreRemoveEventArgs $event)
    {
        if ('cli' != php_sapi_name()) {
            $eventManager = $event->getObjectManager();

            foreach ($book->getAuthors() as $author) {
                $author->setCountBooks($author->getCountBooks() - 1);
            }

            $eventManager->flush();
        }
    }

    public function postUpdate(Book $book, PostUpdateEventArgs $event)
    {
        if ('cli' != php_sapi_name()) {
            $eventManager = $event->getObjectManager();

            $authorIds = $eventManager->getRepository(Book::class)->getAuthorIdsByBookId($book->getId());
            $buf = $authorIds;

            foreach ($book->getAuthors() as $key => $author) {
                if (!in_array($author->getId(), $authorIds)) {
                    $author->setCountBooks($author->getCountBooks() + 1);
                } else {
                    unset($buf[$key]);
                }
            }

            if (!empty($buf)) {
                $authorRepository = $eventManager->getRepository(Author::class);

                foreach ($buf as $bufItem) {
                    $author = $authorRepository->find($bufItem);
                    $author->setCountBooks($author->getCountBooks() - 1);
                }
            }

            $eventManager->flush();
        }
    }
}