<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category; // Assuming you have a Category entity
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'product')]
    public function index(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        $products = $entityManager->getRepository(Product::class)->findAll();
        $categories = $entityManager->getRepository(Category::class)->findAll();

        // Render the template and pass products and categories
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'user' => $user
        ]);
    }
}
