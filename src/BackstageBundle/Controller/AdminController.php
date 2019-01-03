<?php

namespace BackstageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function indexAction()
    {
         return new Response('<html><body>Admin page!</body></html>');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
         return new Response('<html><body>logout page!</body></html>');
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        return new Response('<html><body>logout page!</body></html>');
    }
}
