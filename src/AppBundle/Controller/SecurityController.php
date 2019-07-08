<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Entity\Person;


use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{

    public function loginAction(Request $request)
    {

            // if authentiticated redirect to dashboard
            if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
              return $this->redirectToRoute('dashboard');
            }

            $authenticationUtils = $this->get('security.authentication_utils');

            return $this->render('AppBundle:Security:login.html.twig', array(
                  'last_username' => $authenticationUtils->getLastUsername(),
                  'error'         => $authenticationUtils->getLastAuthenticationError(),
             ));
    }

    /**
     * @Route("/create-account", name="signin")
     */
    public function signinAction(UserPasswordEncoderInterface $encoder, Request $request, EntityManagerInterface $em)
    {
            $user = new User();

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if($form->isValid() && $form->isSubmitted())
            {
                    $datas = $form->getData();
                    $user->setRoles([$datas->getRoles()]);
                    $encoded = $encoder->encodePassword($user, $datas->getPassword());
                    $user->setPassword($encoded);
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success', 'Compte créé ! Connectez-vous');
                    return $this->redirectToRoute('login');
            }

            return $this->render('AppBundle:Security:signin.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/create-user", name="create-user")
     */
    public function registerAction(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {

    
        foreach($this->fixtures() as $data) {

                $firstname = trim($data['firstname']);
                $lastname  = trim($data['lastname']);
                $role      = trim($data['role']);

                if($role == "ROLE_ADMIN") { $min = 1974; $max = 1976 ;};
                if($role == "ROLE_PLAYER") { $min = 2003; $max = 2014 ;};
                if($role == "ROLE_COACH")  { $min = 1988; $max = 2001 ;};
                if($role == "ROLE_REFEREE"){ $min = 1978; $max = 2001 ;};
                if($role == "ROLE_MANAGER"){ $min = 1970; $max = 1995 ;};

                $y = rand($min, $max);
                $m = rand(1,12);
                $d = rand(1,28);
                if($m < 10) $m = '0'.$m;
                if($d < 10) $d = '0'.$d;

                $birthdate = $y.'-'.$m.'-'.$d;

                $fname = strtolower(str_replace(array(' ',"'", '-', 'é', 'è', 'ï'), array('', '', '', 'e', 'e', 'i'), $firstname));
                $lname = ucfirst(strtolower(str_replace(array(' ',"'", '-', 'é', 'è', 'ï'), array('', '', '', 'e', 'e', 'i'), $lastname)));

                $username = $fname.$lname;
                $password = $username;

                $providers = ['gmail.com', 'hotmail.fr', 'etsik.com', 'yahoo.fr', 'live.fr'];
                $key = rand(1,4);

                $email = $fname.'.'.strtolower($lname).'@'.$providers[$key];

                $person = new Person();
                $person->setFirstname($firstname);
                $person->setLastname($lastname);
                $person->setBirthdate($birthdate);
                $person->setIsActive(1);
                $em->persist($person);

                $user = new User();
                $encoded = $encoder->encodePassword($user, $password);
                $user->setUsername($username);
                $user->setPassword($encoded);
                $user->setEmail($email);
                $user->setRoles([$role]);
                $user->setIsActive(1);
                $user->setPerson($person);

                $em->persist($user);
                $em->flush();
        }

        return $this->redirectToRoute('usersList');

    }

    public function fixtures() {

        $datas = array(
                                array(  'lastname' => 'Etsik'  , 'firstname' => 'Sandy'  ,    'role' => 'ROLE_ADMIN' ),
                                array(  'lastname' => 'Granger'  , 'firstname' => 'Hermione'  ,    'role' => 'ROLE_MANAGER' ),
                                array(  'lastname' => 'Potter'  , 'firstname' => 'Harry'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Ron'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Abbot'  , 'firstname' => 'Hannah'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Barjow'  , 'firstname' => 'Monsieur'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Beurk'  , 'firstname' => 'Caractacus'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Black'  , 'firstname' => 'Regulus Arcturus'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Boot'  , 'firstname' => 'Terry'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Carmichael'  , 'firstname' => 'Eddie'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Cole'  , 'firstname' => 'madame'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Crockford'  , 'firstname' => 'Doris'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Dearborn'  , 'firstname' => 'Caradoc'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Diggory'  , 'firstname' => 'Amos'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Dippet'  , 'firstname' => 'Armando'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Dumbledore'  , 'firstname' => 'Abelforth'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Dursley'  , 'firstname' => 'Dudley'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Fenwick'  , 'firstname' => 'Benjy'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Flint'  , 'firstname' => 'Marcus'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Gaunt'  , 'firstname' => 'Elvis Marvolo'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Jones'  , 'firstname' => 'Gwenog'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Lestrange'  , 'firstname' => 'Rabastan'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Londubat'  , 'firstname' => 'Frank'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Londubat'  , 'firstname' => 'Neville'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Malefoy'  , 'firstname' => 'Narcissa'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Ogden'  , 'firstname' => 'Bob'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Poufsouffle'  , 'firstname' => 'Helga'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Robards'  , 'firstname' => 'Gawain'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Rusard'  , 'firstname' => 'Argus'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Spinnet'  , 'firstname' => 'Alicia'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Trelawney'  , 'firstname' => 'Sybille'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Bill'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Worpel'  , 'firstname' => 'Eldred'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Zabini'  , 'firstname' => 'Blaise'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'de PortRéal'  , 'firstname' => 'Baratheon'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Frey'  , 'firstname' => 'Walder'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Greyjoy'  , 'firstname' => 'Balon'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Sand'  , 'firstname' => 'Ellaria'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Luwin'  , 'firstname' => 'Mestre'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Reed'  , 'firstname' => 'Meera'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Payne'  , 'firstname' => 'Ilyn'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Drogo'  , 'firstname' => 'Khal'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Giantsbane'  , 'firstname' => 'Tormund'  ,    'role' => 'ROLE_REFEREE' ),
                    );

         return $datas;
    }

}
