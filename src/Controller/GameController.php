<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/')]
    public function homepage(): Response
    {
        return $this->render('game/game.html.twig', [
            'title' => 'hoi',
        ]);
    }
}