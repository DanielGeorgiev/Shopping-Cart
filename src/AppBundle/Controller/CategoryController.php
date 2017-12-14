<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class CategoryController extends Controller
{
    /**
     * @Route("/category/{slug}", name="category_view")
     *
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryView(string $slug)
    {
        $categoriesRepo = $this->getDoctrine()->getRepository(Category::class);
        $category = $categoriesRepo->findBy(['slug' => $slug])[0];
        $subCategories = $categoriesRepo->findSubCategories($category);

        $productsRepo = $this->getDoctrine()->getRepository(Product::class);
        $products = $productsRepo->findAllByGivenCategoryAndSubcategories($category, $subCategories);

        return $this->render('category/view.html.twig', [
            'subCategories' => $subCategories,
            'mainCategory' => $category,
            'products' => $products
        ]);
    }
}
