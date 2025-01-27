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

    public function findByBlogFilter(BlogFilter $blogFilter){
        $blogs = $this->createQueryBuilder('b');

        if ($blogFilter->getTitle()){
            $blogs->andWhere('b.title LIKE :title')->setParameter(
                'title', '%'.$blogFilter->getTitle().'%');

        }

    return $blogs->getQuery()->getResult();
    }
}
