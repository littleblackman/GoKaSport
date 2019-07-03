<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

  private $em;

  public function __construct(EntityManagerInterface $em) {
    $this->em = $em;
  }


  /**
   * @Route("/users-list/{role}", name="usersList", defaults={"role": null})
   */
  public function usersList(Request $request, $role)
  {
      $users = $this->em->getRepository(User::class)->findByList($role);
      return $this->render('AppBundle:user:index.html.twig', ['users' => $users, 'role' => $role]);

  }

  /**
   * @Route("/show-user/{id}", name="showUser", defaults={"id": null})
   */
  public function showUser(Request $request, $id) {
    $user = $this->em->getRepository(User::class)->find($id);
    $editable = true;
    return $this->render('AppBundle:user:show.html.twig', ['user' => $user, 'editable' => $editable]);
  }

  /**
   * @Route("/get-users-ajax/{role}/{search}", name="usersListAjax", defaults={"search" = null})
   */
  public function getUsersListAjax($role, $search)
  {
      $em = $this->getDoctrine()->getManager();
      $users = $em->getRepository(User::class)->findLike($role, $search);

      $role = strtolower(str_replace('ROLE_', '', $role));

      return $this->render('AppBundle:user:usersListAjax.html.twig', ['users' => $users, 'role' => $role]);

  }

}
