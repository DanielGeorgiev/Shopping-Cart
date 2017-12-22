<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class CategoryController extends Controller
{
    /**
     * @Route("/category/{slug}", name="category_view")
     *
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryView(string $slug, Request $request)
    {
        $categoriesRepo = $this->getDoctrine()->getRepository(Category::class);
        $category = $categoriesRepo->findBy(['slug' => $slug]);

        if ($category == null){
            return $this->redirectToRoute('home_page');
        }

        $subCategories = $categoriesRepo->findSubCategories($category[0]);

        $productsRepo = $this->getDoctrine()->getRepository(Product::class);
        $products = $productsRepo->findAllByGivenCategoryAndSubcategories($category, $subCategories);

        $paginator = $this->get('knp_paginator');
        $productsForPage = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('category/view.html.twig', [
            'subCategories' => $subCategories,
            'mainCategory' => $category[0],
            'products' => $productsForPage
        ]);
    }
}
