<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

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

        /* reset bdd manually before fixture
        delete FROM `person` WHERE id > 1;
        delete FROM `lbm_user` WHERE id > 1;
        ALTER TABLE person AUTO_INCREMENT = 2;
        ALTER TABLE lbm_user AUTO_INCREMENT = 2;
        */

        foreach($this->fixtures() as $data) {

                $firstname = trim($data['firstname']);
                $lastname  = trim($data['lastname']);
                $role      = trim($data['role']);

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
                                array(  'lastname' => 'Granger'  , 'firstname' => 'Hermione'  ,    'role' => 'ROLE_MANAGER' ),
                                array(  'lastname' => 'Potter'  , 'firstname' => 'Harry'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Ron'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Abbot'  , 'firstname' => 'Hannah'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Abercrombie'  , 'firstname' => 'Euan'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Barjow'  , 'firstname' => 'Monsieur'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Belby'  , 'firstname' => 'Marcus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bell'  , 'firstname' => 'Katie'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Beurk'  , 'firstname' => 'Caractacus'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Bibine'  , 'firstname' => 'Madame'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Binns'  , 'firstname' => 'Cuthbert'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Black'  , 'firstname' => 'Elladora'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Black'  , 'firstname' => 'Regulus Arcturus'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Black'  , 'firstname' => 'Sirius'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bones'  , 'firstname' => 'Amélia Susan'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bones'  , 'firstname' => 'Susan'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Boot'  , 'firstname' => 'Terry'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Brown'  , 'firstname' => 'Lavande'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Burbage'  , 'firstname' => 'Charity'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bryce'  , 'firstname' => 'Frank'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bullstrode'  , 'firstname' => 'Milicent'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Carmichael'  , 'firstname' => 'Eddie'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Catogan'  , 'firstname' => 'Chevalier du'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Chang'  , 'firstname' => 'Cho'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Chourave'  , 'firstname' => 'Pomona'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Cole'  , 'firstname' => 'madame'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Coote'  , 'firstname' => 'Ritchie'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Corner'  , 'firstname' => 'Michael'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Crabbe'  , 'firstname' => 'Vincent'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Cresswell'  , 'firstname' => 'Dirk'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Crivey'  , 'firstname' => 'Colin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Crivey'  , 'firstname' => 'Denis'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Crockford'  , 'firstname' => 'Doris'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Croupton'  , 'firstname' => 'Bartemius Sr'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Croupton'  , 'firstname' => 'Bartemius Jr'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Cuffe'  , 'firstname' => 'Barnabas'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Danlmur'  , 'firstname' => 'Ernie'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Deauclair'  , 'firstname' => 'Pénélope'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Davies '  , 'firstname' => 'Roger'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dearborn'  , 'firstname' => 'Caradoc'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Delacour'  , 'firstname' => 'Fleur'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Delacour'  , 'firstname' => 'Gabrielle'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Delaney Podmore'  , 'firstname' => 'Sir Patrick'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Diggle'  , 'firstname' => 'Dedalus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Diggory'  , 'firstname' => 'Amos'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Diggory'  , 'firstname' => 'Cédric'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dippet'  , 'firstname' => 'Armando'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Doge'  , 'firstname' => 'Elphias'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dolohov'  , 'firstname' => 'Antonin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dorcas'  , 'firstname' => 'Meadows'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dubois'  , 'firstname' => 'Olivier'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dumbledore'  , 'firstname' => 'Abelforth'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Dumbledore'  , 'firstname' => 'Albus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dumbledore'  , 'firstname' => 'Ariana'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dursley'  , 'firstname' => 'Dudley'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Dursley'  , 'firstname' => 'Marge'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dursley'  , 'firstname' => 'Pétunia'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dursley'  , 'firstname' => 'Vernon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Edgecombe'  , 'firstname' => 'Marietta'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Fenwick'  , 'firstname' => 'Benjy'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Figgs'  , 'firstname' => 'Arabella Dorine'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Finch-Fletchley'  , 'firstname' => 'Justin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Finnigan'  , 'firstname' => 'Seamus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Fletcher'  , 'firstname' => 'Mondingus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Flint'  , 'firstname' => 'Marcus'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Flitwick'  , 'firstname' => 'Filius'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Fudge'  , 'firstname' => 'Cornélius Oswald'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Gaunt'  , 'firstname' => 'Elvis Marvolo'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Gaunt'  , 'firstname' => 'Mérope'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Gaunt'  , 'firstname' => 'Morfin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Gobe-Planche'  , 'firstname' => 'Wilhelmina'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Goldstein'  , 'firstname' => 'Anthony'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Gourdenieze'  , 'firstname' => 'Gladys'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Goyle'  , 'firstname' => 'Gregory'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Greengrass'  , 'firstname' => 'Daphné'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Greyback'  , 'firstname' => 'Fenrir'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Gryffondor'  , 'firstname' => 'Godric'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Hagrid'  , 'firstname' => 'Rubeus'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Herbert'  , 'firstname' => 'Chorley'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Higgs'  , 'firstname' => 'Térence'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Jedusor'  , 'firstname' => 'Tom Elvis'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Johnson'  , 'firstname' => 'Angelina'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Jones'  , 'firstname' => 'Gwenog'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Jones'  , 'firstname' => 'Hestia'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Jordan'  , 'firstname' => 'Lee'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Jorkins'  , 'firstname' => 'Bertha'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Karkaroff'  , 'firstname' => 'Igor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Kirke'  , 'firstname' => 'Andrew'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Krum'  , 'firstname' => 'Victor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lestrange'  , 'firstname' => 'Bellatrix'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lestrange'  , 'firstname' => 'Rabastan'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Lestrange'  , 'firstname' => 'Rodolphus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lockhart'  , 'firstname' => 'Gilderoy'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Londubat'  , 'firstname' => 'Alice'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Londubat'  , 'firstname' => 'Augusta'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Londubat'  , 'firstname' => 'Frank'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Londubat'  , 'firstname' => 'Neville'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Lovegood'  , 'firstname' => 'Luna'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lupin'  , 'firstname' => 'Remus John'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'McGonagall'  , 'firstname' => 'Minerva'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'McKinnon'  , 'firstname' => 'Marlène'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'McLaggen'  , 'firstname' => 'Cormac'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'McMillan'  , 'firstname' => 'Ernie'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'McNair'  , 'firstname' => 'Walden'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Malefoy'  , 'firstname' => 'Drago'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Malefoy'  , 'firstname' => 'Lucius'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Malefoy'  , 'firstname' => 'Narcissa'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Maugrey'  , 'firstname' => 'Alastor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Maxime'  , 'firstname' => 'Olympe'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Moroz'  , 'firstname' => 'Broderick'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Nigellus'  , 'firstname' => 'Phineas'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Ogden'  , 'firstname' => 'Bob'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Ombrage'  , 'firstname' => 'Dolores Jane'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Parkinson'  , 'firstname' => 'Pansy'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Patil'  , 'firstname' => 'Padma'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Patil'  , 'firstname' => 'Parvati'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Peakes'  , 'firstname' => 'Jimmy'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Pettigrow'  , 'firstname' => 'Peter'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Podmore'  , 'firstname' => 'Sturgis'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Pomfresh'  , 'firstname' => 'Poppy'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Polkiss'  , 'firstname' => 'Piers'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Potter'  , 'firstname' => 'James'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Potter'  , 'firstname' => 'Lily'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Poufsouffle'  , 'firstname' => 'Helga'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Prewett'  , 'firstname' => 'Fabian'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Prewett'  , 'firstname' => 'Gideon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Pucey'  , 'firstname' => 'Adrian'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Quirell'  , 'firstname' => 'professeur'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Robards'  , 'firstname' => 'Gawain'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Robins'  , 'firstname' => 'Demelza'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rocade'  , 'firstname' => 'Stan'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rogue'  , 'firstname' => 'Severus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rogue'  , 'firstname' => 'Tobias'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rookwood'  , 'firstname' => 'Augustus'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rosier'  , 'firstname' => 'Evan'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rosmerta'  , 'firstname' => 'Madame'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rusard'  , 'firstname' => 'Argus'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Scrimgeour'  , 'firstname' => 'Rufus'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Serdaigle'  , 'firstname' => 'Rowena'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Serdaigle'  , 'firstname' => 'Héléna'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Serpentard'  , 'firstname' => 'Salazar'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Shacklebolt'  , 'firstname' => 'Kingsley'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Sinistra'  , 'firstname' => 'professeur'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Skeeter'  , 'firstname' => 'Rita'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Slughorn'  , 'firstname' => 'Horace'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Smith'  , 'firstname' => 'Hepzibah'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Smith'  , 'firstname' => 'Zacharias'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Spinnet'  , 'firstname' => 'Alicia'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Thomas'  , 'firstname' => 'Dean'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tonks'  , 'firstname' => 'Andromeda'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tonks'  , 'firstname' => 'Nymphadora'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tonks'  , 'firstname' => 'Ted'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Trelawney'  , 'firstname' => 'Sybille'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Vance'  , 'firstname' => 'Emmeline'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Vane'  , 'firstname' => 'Romilda'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Vector'  , 'firstname' => 'professeur'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Verpey'  , 'firstname' => 'Ludo'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Arthur'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Bill'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Charlie'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Fred'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Georges'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Ginny'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Molly'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Muriel'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Weasley'  , 'firstname' => 'Perceval'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Worpel'  , 'firstname' => 'Eldred'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Zabini'  , 'firstname' => 'Blaise'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Arryn'  , 'firstname' => 'Jon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Arryn'  , 'firstname' => 'Lysa'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Arryn'  , 'firstname' => 'Robin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'du Val'  , 'firstname' => 'Hugue'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Egen'  , 'firstname' => 'Vardis'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'de PortRéal'  , 'firstname' => 'Baratheon'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Robert'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Joffrey'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Tommen'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Myrcella'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'de Peyredragon'  , 'firstname' => 'Baratheon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Stannis'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Selyse'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Shireen'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Mervault'  , 'firstname' => 'Davos'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Mervault'  , 'firstname' => 'Matthos'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'd\'Ashaï'  , 'firstname' => 'Mélisandre'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Saan'  , 'firstname' => 'Salladhor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Cressen'  , 'firstname' => 'Mestre'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'd\'Accalmie'  , 'firstname' => 'Baratheon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baratheon'  , 'firstname' => 'Renly'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bolton'  , 'firstname' => 'Roose'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bolton'  , 'firstname' => 'Walda'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Bolton'  , 'firstname' => 'Ramsay'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Frey'  , 'firstname' => 'Walder'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Greyjoy'  , 'firstname' => 'Theon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Greyjoy'  , 'firstname' => 'Balon'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Greyjoy'  , 'firstname' => 'Yara'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Tyrion'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Jaime'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Cersei'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Tywin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Kevan'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Lancel'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Alton'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Martyn'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Lannister'  , 'firstname' => 'Willem'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Clegane'  , 'firstname' => 'Gregor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Payne'  , 'firstname' => 'Podrick'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Lorch'  , 'firstname' => 'Amory'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Titilleur'  , 'firstname' => 'Lee'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Martell'  , 'firstname' => 'Oberyn'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Sand'  , 'firstname' => 'Ellaria'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Eddard'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Catelyn'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Robb'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Snow'  , 'firstname' => 'Jon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Sansa'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Arya'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Bran'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Rickon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Maegyr'  , 'firstname' => 'Talisa'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Luwin'  , 'firstname' => 'Mestre'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Reed'  , 'firstname' => 'Jojen'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Reed'  , 'firstname' => 'Meera'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Cassel'  , 'firstname' => 'Rodrik'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Cassel'  , 'firstname' => 'Jory'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Nan'  , 'firstname' => 'Vieille'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Mordane'  , 'firstname' => 'Septa'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'de Torth'  , 'firstname' => 'Brienne'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Omble'  , 'firstname' => 'Jon'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Karstark'  , 'firstname' => 'Rickard'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Karstark'  , 'firstname' => 'Torhen'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Targaryen'  , 'firstname' => 'Daenerys'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Mormont'  , 'firstname' => 'Jorah'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Targaryen'  , 'firstname' => 'Viserys'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Selmy'  , 'firstname' => 'Barristan'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Naharis'  , 'firstname' => 'Daario'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tully'  , 'firstname' => 'Edmure'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tully'  , 'firstname' => 'Brynden'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Frey'  , 'firstname' => 'Roslin'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tyrell'  , 'firstname' => 'Margaery'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Tyrell'  , 'firstname' => 'Olenna'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tyrell'  , 'firstname' => 'Loras'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tyrell'  , 'firstname' => 'Mace'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Baelish'  , 'firstname' => 'Petyr'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Trant'  , 'firstname' => 'Meryn'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Hollard'  , 'firstname' => 'Dontos'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Payne'  , 'firstname' => 'Ilyn'  ,    'role' => 'ROLE_COACH' ),
                                 array(  'lastname' => 'Clegane'  , 'firstname' => 'Sandor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'MainVerte'  , 'firstname' => 'Lommy'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Forel'  , 'firstname' => 'Syrio'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Dondarrion'  , 'firstname' => 'Béric'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'de Myr'  , 'firstname' => 'Thoros'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Drogo'  , 'firstname' => 'Khal'  ,    'role' => 'ROLE_MANAGER' ),
                                 array(  'lastname' => 'Mormont'  , 'firstname' => 'Jeor'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Tarly'  , 'firstname' => 'Samwell'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Aemon'  , 'firstname' => 'Mestre'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Thorne'  , 'firstname' => 'Alliser'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Stark'  , 'firstname' => 'Benjen'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Slynt'  , 'firstname' => 'Janos'  ,    'role' => 'ROLE_REFEREE' ),
                                 array(  'lastname' => 'Tollett'  , 'firstname' => 'Eddison'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Marsh'  , 'firstname' => 'Bowen'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Rayder'  , 'firstname' => 'Mance'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Snow'  , 'firstname' => 'Ygrid'  ,    'role' => 'ROLE_PLAYER' ),
                                 array(  'lastname' => 'Giantsbane'  , 'firstname' => 'Tormund'  ,    'role' => 'ROLE_REFEREE' ),
                    );

         return $datas;
    }

}
