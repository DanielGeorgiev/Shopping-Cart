<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home_page")
     */
    public function indexAction(Request $request)
    {
        $productsRepo = $this->getDoctrine()->getRepository(Product::class);
        $products = $productsRepo->findAllWithCategories();

        $categoriesRepo = $this->getDoctrine()->getRepository(Category::class);
        $categories = $categoriesRepo->findAllMain();

        $paginator = $this->get('knp_paginator');
        $limit = 6;
        $productsForPage = $paginator->paginate(
            $products,
            $request->query->getInt('page', rand(1, ceil(count($products) / $limit))),
                $limit
        );

        return $this->render('home/index.html.twig', ['products' => $productsForPage, 'categories' => $categories]);
    }
}
