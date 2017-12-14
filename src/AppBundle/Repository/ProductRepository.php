<?php

namespace AppBundle\Repository;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllWithCategories()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT p, c 
                            FROM AppBundle:Product p
                            INNER JOIN p.category c');

        return $query->getResult();
    }

    public function findAllByGivenCategoryAndSubcategories($category, $subCategories)
    {
        $em = $this->getEntityManager();
        $query = "SELECT p FROM AppBundle:Product p WHERE p.category = :category ";

        for ($i = 1; $i <= count($subCategories); $i++){
            $query .= "OR p.category = :subCategory" . $i . ' ';
        }

        $query = $em->createQuery($query);

        $query->setParameter('category', $category);

        for ($i = 0; $i < count($subCategories); $i++){
            $query->setParameter("subCategory" . ($i + 1), $subCategories[$i]);
        }

        return $query->getResult();
    }
}