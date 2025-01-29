<?php

namespace App\Repository;

use App\Entity\Blog;
use App\Filter\BlogFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    public function GetBlogs(): array
    {
        return $this
            ->createQueryBuilder('b')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    public function findByBlogFilter(BlogFilter $blogFilter){
        $blogs = $this->createQueryBuilder('b');

        if ($blogFilter->getUser()){
            $blogs
                ->andWhere('b.user = :user')
                ->setParameter('user', $blogFilter->getUser());

        }

        if ($blogFilter->getTitle()){
            $blogs->andWhere('b.title LIKE :title')->setParameter(
                'title', '%'.$blogFilter->getTitle().'%');

        }

    return $blogs;
    }
}
