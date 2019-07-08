<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\User;
use AppBundle\Form\UserPlayerType;
use AppBundle\Entity\Team;
use AppBundle\Entity\Sport;
use AppBundle\Entity\Person;
use AppBundle\Entity\Player;
use AppBundle\Entity\SportPosition;

use Symfony\Component\HttpFoundation\JsonResponse;





use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


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
   * @Route("/creation-joueur/{team_id}/{type_call}", name="editPlayerForm")
   */
  public function editPlayerForm(Request $request, UserPasswordEncoderInterface $encoder, $team_id = null, $type_call = null)
  {
    $em = $this->em;

    $user = new User();
    $team = $em->getRepository(Team::class)->find($team_id);

    $sport = $team->getSport();

    $_SESSION['sport'] = $sport;

    $form = $this->createForm(UserPlayerType::class, $user);
    $form->handleRequest($request);

    if($form->isValid() && $form->isSubmitted())
    {
        $datas = $form->getData();
        $encoded = $encoder->encodePassword($user, $datas->getPassword());

        $fname = strtolower(str_replace(array(' ',"'", '-', 'é', 'è', 'ï'), array('', '', '', 'e', 'e', 'i'), $datas->getPerson()->getFirstname()));
        $lname = ucfirst(strtolower(str_replace(array(' ',"'", '-', 'é', 'è', 'ï'), array('', '', '', 'e', 'e', 'i'), $datas->getPerson()->getLastname())));
        $username = $fname.$lname;

        $user->setUsername($username);
        $user->setPassword($encoded);
        $user->setRole([$datas->getRole()]);
        $em->persist($user);
        $team->addUser($user);
        $em->persist($team);
        $em->flush();
        $this->addFlash('success', 'Compte créé ! Connectez-vous');
        return $this->redirectToRoute('login');
    }


    return $this->render('AppBundle:user:editPlayer.html.twig', ['form' => $form->createView(), 'type_call' => $type_call, 'team_id' => $team_id, 'sport_name' => $sport->getName()]);
  }

  /**
   * @Route("/add-user-team-ajax", name="addUserTeamAjax")
   */
  public function addUserTeamAjax(Request $request, UserPasswordEncoderInterface $encoder)
  {

    $dt_user_player = $request->get('user_player');
    $dt_person = $dt_user_player['person'];
    $dt_player = $dt_person['player'];

    $em = $this->em;
    $team = $em->getRepository(Team::class)->find($request->get('team_id'));
    $position = $em->getRepository(SportPosition::class)->find($dt_player['position']);


    // player
    $player = new Player();
    $player->setPosition($position);
    $em->persist($player);

    // person
    $person = new Person();
    $person->setFirstname($dt_person['firstname']);
    $person->setLastname($dt_person['lastname']);
    $person->setBirthdate(new \DateTime($dt_person['birthdate']['year'].'-'.$dt_person['birthdate']['month'].'-'.$dt_person['birthdate']['day']));
    $em->persist($person);

    // user
    $user = new User();
    $encoded = $encoder->encodePassword($user, $dt_user_player['password']);
    $fname = strtolower(str_replace(array(' ',"'", '-', 'é', 'è', 'ï'), array('', '', '', 'e', 'e', 'i'), $person->getFirstname()));
    $lname = ucfirst(strtolower(str_replace(array(' ',"'", '-', 'é', 'è', 'ï'), array('', '', '', 'e', 'e', 'i'), $person->getLastname())));
    $username = $fname.$lname;

    // check username
    if($userExist = $this->em->getRepository(User::class)->findByUsername($username)) {
      $username = $username.'-'.date('his');
    }

    $user->setEmail($dt_user_player['email']);
    $user->setUsername($username);
    $user->setPassword($encoded);
    $user->setRoles([$dt_user_player['roles']]);
    $em->persist($user);

    $person->setPlayer($player);
    $user->setPerson($person);
    $team->addUser($user);
    $em->persist($team);
    $em->flush();

    return new JsonResponse(['player' => $user->toArray()]);

  }

  /**
   * @Route("/check-if-email-exist/{email}", name="userEmailExist")
   */
  public function userEmailExist($email = null)
  {
      if(!$user = $this->em->getRepository(User::class)->findByEmail($email))
      {
        return new JsonResponse(['user' => null]);
      } else {
        return new JsonResponse(['user' => $user->toArray()]);
      }

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
