<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Product;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $user = $this->security->getUser();
        $favorites     = $user ? $user->getFavorites() : [];
        $averageRating = $user
            ? $this->em->getRepository(Rating::class)->findAverageRatingByUser($user)
            : null;

        $qb = $this->em->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.ratings', 'r')
            ->addSelect('AVG(r.value) AS avgRating')
            ->groupBy('p.id')
            ->orderBy('avgRating', 'DESC')
            ->setMaxResults(3);
        /** @var array<int, array{0: Product, avgRating: float|null}> $rows */
        $rows       = $qb->getQuery()->getResult();
        $topProducts = array_map(fn($row) => $row[0], $rows);

        $recentComments = $this->em->getRepository(Comment::class)
            ->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $purchaseCount = 0;
        if ($user) {
            foreach ($user->getCommandes() as $commande) {
                $purchaseCount += $commande->getProducts()->count();
            }

        }

        return $this->render('dashboard/index.html.twig', [
            'favorites'       => $favorites,
            'averageRating'   => $averageRating,
            'topProducts'     => $topProducts,
            'recent_comments' => $recentComments,
            'purchaseCount'   => $purchaseCount,
        ]);
    }
}
