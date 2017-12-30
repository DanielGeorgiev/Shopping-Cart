<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;


class CategoryController extends Controller
{
    /**
     * @Route("/category/{slug}", name="category_view")
     *
     * @param string $slug
     * @param Request $request
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

    /**
     * @Route("/category/create/new", name="category_add")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryCreate(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $category->setSlug();

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('home_page');
        }

        return $this->render('category/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/category/delete/{id}", name="category_delete")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryDelete(int $id, Request $request)
    {
        $categoryRepo = $this->getDoctrine()->getRepository(Category::class);
        $category = $categoryRepo->find($id);

        if ($category === null){
            return $this->redirectToRoute('home_page');
        }

        $form = $this->createForm(CategoryType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();

            for ($i = 0; $i < count($category->getProducts()); $i++){
                $currProduct = $category->getProducts()[$i];
                $em->remove($currProduct);
            }

            $em->remove($category);
            $em->flush();

            return $this->redirectToRoute('home_page');
        }

        return $this->render('category/delete.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }
}
