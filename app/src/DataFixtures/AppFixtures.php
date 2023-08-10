<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $author = new Author();
            $author->setName('Author' . $i);
            $author->setCountBooks($i);

            $manager->persist($author);
        }

        $manager->flush();

        $authorRepository = $manager->getRepository(Author::class);

        for ($i = 1; $i <= 5; $i++) {
            $book = new Book();
            $book->setTitle('Book' . $i);
            $book->setYear(rand(1000, 2023));
            for ($g = $i; $g < 5; $g++) {
                $book->addAuthor(
                    $authorRepository->findOneBy(['name' => 'Author' . $g])
                );
            }

            $manager->persist($book);
        }

        $manager->flush();
    }
}
