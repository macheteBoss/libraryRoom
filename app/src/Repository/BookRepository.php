<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Book $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Book $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getAuthorIdsByBookId($bookId) {
        $conn = $this->_em->getConnection();

        $authorIds = [];

        $sql = '
            SELECT * FROM book_author
            WHERE book_id = ?
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$bookId]);
        $data = $resultSet->fetchAllAssociative();

        $authorIds = [];
        foreach ($data as $item) {
            $authorIds[] = $item['author_id'];
        }

        return $authorIds;
    }

    public function findBySearchData($filterData) {
        $conn = $this->_em->getConnection();

        $data = $filterData;

        $sql = 'SELECT b.id FROM book b';
        $where = '';

        if (array_key_exists('authors', $filterData) !== false) {
            $sql .= ' 
                INNER JOIN book_author ba ON b.id = ba.book_id 
                INNER JOIN author a ON a.id = ba.author_id
            ';
            $where = ' author_id IN (' . implode(',', $filterData['authors']) . ')';

            unset($data['authors']);
        }

        if (array_key_exists('image', $filterData)) {
            if ($where) {
                $where .= ' AND b.image != ""';
            } else {
                $where = 'b.image != ""';
            }

            unset($data['image']);
        }

        $list = [];
        foreach ($data as $key => $item) {
            $list[] = 'b.' . $key . ' LIKE "%' . $item . '%"';
        }

        if (!empty($list)) {
            if ($where) {
                $where .= ' AND ';
            }
            $where .= implode(' AND ', $list);
        }

        if ($where) {
            $sql .= ' WHERE ' . $where;
        }

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $ids = $resultSet->fetchAllAssociative();

        $data = [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $data[] = $id['id'];
            }
        }

        return $data;
    }

    // /**
    //  * @return Book[] Returns an array of Book objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
