<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use AppBundle\Entity\Player;
use AppBundle\Entity\Sport;
use AppBundle\Form\TeamType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;



class TeamController extends Controller
{

    private $currentUser;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->currentUser = $tokenStorage->getToken()->getUser();
    }



    /**
    * @Route("/liste-des-equipes", name="listTeam")
     */
    public function listAction(Request $request)
    {

        $manager = $this->getDoctrine()->getManager();

        $owners = $manager->getRepository(Team::class)->findByCreatedBy($this->currentUser);
        $associates = $manager->getRepository(Team::class)->findAssociated($this->currentUser->getId());

        return $this->render('AppBundle:team:list.html.twig', ['owners' => $owners, 'associates' => $associates]);
    }

    /**
     * @Route("/voir-equipe/{teamId}", name="showTeam")
     */
    public function showTeam(Request $request, $teamId) {
        $team = $this->getDoctrine()->getManager()->getRepository(Team::class)->find($teamId);
        return $this->render('AppBundle:team:show.html.twig', ['team' => $team]);
    }

    /**
     * @Route("/creer-une-equipe", name="createTeam")
     * @Route("/modifier-equipe/{id}", name="editTeam")
     */
    public function editTeam(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $originalPlayers = new ArrayCollection();

        if($id)
        {
          $team = $em->getRepository(Team::class)->find($id);
          foreach($team->getPlayers() as $player)
          {
              $originalPlayers->add($player);
          }
          $mode = "Modification";
        } else {
          $team = new Team();

          $mode = "Création";
        }

          $form = $this->createForm(TeamType::class, $team);
          $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted())
        {

            if($mode == "Création") $team->setCreatedBy($this->currentUser);
            if($mode == "Modification") $team->setUpdatedBy($this->currentUser);

            $em->persist($team);
            $em->flush();

            return $this->redirectToRoute('showTeam', ['teamId' => $team->getId()]);
        }

        return $this->render('AppBundle:team:edit.html.twig', ['form' => $form->createView(), 'mode' => $mode]);

    }

    /**
     * @Route("/ajout-joueur-team/{team_id}/{user_id}", name="addPlayerTeam")
     */
    public function addPlayerTeam($team_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->addUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:usersList.html.twig', ['users' => $users]);

    }

    /**
     * @Route("/ajout-coach-team/{team_id}/{user_id}", name="addCoachTeam")
     */
    public function addCoachTeam($team_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->addUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:usersList.html.twig', ['users' => $users]);

    }


    /**
     * @Route("/playerListTeam/{team_id}", name="playerListTeam")
     */
    public function playerListTeam($team_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        return $this->render('AppBundle:team:playersList.html.twig', ['users' => $team->getPlayers()]);

    }


    /**
     * @Route("/supprimer-coach-team/{team_id}/{user_id}", name="deleteUserCoachAjax")
     */
    public function deleteUserCoachAjax($team_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->removeUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:usersList.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/get-teams-ajax/{search}", name="teamsListAjax", defaults={"search" = null})
     */
    public function getTeamsListAjax($search)
    {
        $teams = $this->getDoctrine()->getManager()->getRepository(Team::class)->findLike($search);
        return $this->render('AppBundle:team:teamsListAjax.html.twig', ['teams' => $teams]);
    }

    /**
     * @Route("/supprimer-player-team/{team_id}/{user_id}", name="deletePlayerTeam")
     */
    public function deleteUserTeam($team_id = null, $user_id = null) {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->removeUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:playersList.html.twig', ['users' => $users]);
    }


    /**
     * @Route("/supprimer-une-equipe/{id}", name="delTeam")
     */
    public function deleteTeam(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($id);

        $em->remove($team);
        $em->flush();

        return $this->redirectToRoute('listTeam');
    }



    /**
     * @Route("/create-teams", name="create-teams")
     */
    public function createTeamFixture(EntityManagerInterface $em)
    {
        $sport = $em->getRepository(Sport::class)->find(1);

        $coachList = [7, 9, 10, 11, 13, 15, 16, 19, 21, 22, 23, 25, 26, 32, 33, 34, 35, 36, 38, 40, 41];
        $i = 0;
        foreach($this->fixtures() as $data) {

         if(!isset($coachList[$i])) $i = 0;
          $user = $em->getRepository(User::class)->find($coachList[$i]);
          $i++;

          $team = new Team();
          $team->setName($data['name']);
          $team->setDescription($data['description']);
          $team->setCity($data['city']);
          $team->setPostalCode(rand(10000, 30000));
          $team->setSport($sport);
          $team->setSportClass('U13');
          $team->setCreatedBy($user);

          $em->persist($team);
          $em->flush();
        }
    }

    public function fixtures()
    {
      return [
             array('name' => 'Poufsouffle' , 'description'  => 'Poufsouffle (Hufflepuff) était une sorcière loyale, honnête et travailleuse, originaire des vallées galloises6,Note 2. Elle appréciait ces mêmes qualités chez les élèves6 et s\'intéressait particulièrement à ceux et celles qui avaient le goût du travail acharné'    ,  'city' => 'Gringotts' )   ,
             array('name' => 'Serdaigle' , 'description'  => 'Originaire des montagnes d\'Écosse12,Note 5, Serdaigle (Ravenclaw) était connue pour sa créativité et sa grande intelligence7, mais également pour être la sorcière la plus brillante de son temps14. Serdaigle aurait inventé notamment les pièces changeantes de Poudlard7 ainsi que les escaliers mouvants'    ,  'city' => 'Ravenclaw' )   ,
             array('name' => 'Serpentard' , 'description'  => 'Serpentard (Slytherin) venait des marécages de l\'est de l\'Angleterre, en Est-Anglie, dans le Norfolk9. Ancêtre de Voldemort23, c\'était un puissant sorcier, malin et roublard, qui ne faisait bénéficier de son enseignement que les descendants des plus nobles lignées'    ,  'city' => 'Slytherin' )   ,
             array('name' => 'Gryffondor 2' , 'description'  => 'Gryffondor (Gryffindor en anglais) vivait dans le village de Godric\'s Hollow, dans les plaines12, et était le plus grand duelliste de son temps5. L\'emblème des Gryffondor est le lion, considéré comme la plus courageuse de toutes les créatures13.'    ,  'city' => 'Godric\'s Hollow' )   ,
             array('name' => 'Coruscant' , 'description'  => 'Coruscant est la capitale de la République Galactique, puis de l\'Empire Galactique avant d\'être celle de la Nouvelle République.'    ,  'city' => 'Coruscant' )   ,
             array('name' => 'Crait' , 'description'  => 'Un avant poste de l\'Alliance Rebelle s\'y trouvait avant la bataille de Scarif. Cette planète est composé d\'un minerait rouge recouvert d\'une couche de sel.'    ,  'city' => 'Crait' )   ,
             array('name' => 'Endor' , 'description'  => 'Aussi appelé Lune Sanctuaire, cette planète à des paysages variés. Elle es habité par les Ewoks, les Yuzzums et les Duloks'    ,  'city' => 'Endor' )   ,
             array('name' => 'Hoth' , 'description'  => 'Hoth est une planète recouverte de neige et de glace. Elle est constamment frappé par des météorites et ne possède que peu d\'espèces animals parmis lesquelles les tauntaun et les wampa.'    ,  'city' => 'Hoth' )   ,
             array('name' => 'Ach To' , 'description'  => 'Planète que choisie Luke Skywalker pour s\'exiler après que Ben Solo fut devenu Kylo Ren. Elle est majoritairement composé d\'eau'    ,  'city' => 'Ach To' )   ,
             array('name' => 'Dagobah' , 'description'  => 'Dagobah est une planète couvert de jungles et de marais. On peut trouver sur cette planète une grotte empli du coté obscure.'    ,  'city' => 'Dagobah' )   ,
             array('name' => 'Kamino' , 'description'  => 'Kamino est une planète aquatique. C\'est sur cette planète que les kaminoens créent l\'armée de clone de la république à partir de Jango Fett.'    ,  'city' => 'Kamino' )   ,
             array('name' => 'Mustafar' , 'description'  => 'Mustafar est une planète volcanique inhospitalière. Elle apparait dans Star Wars III La Revanche Des Sith où se passe le duel opposant Obiwan Kenobi à son apprenti, Anakin Skywalker.'    ,  'city' => 'Mustafar' )   ,
             array('name' => 'Kashyyyk' , 'description'  => 'Kashyyyk est une planète quasiment recouverte de forêts. Les wookies y vivent en échangeant leur bois précieux contre de la technologie'    ,  'city' => 'Kashyyyk' )   ,
             array('name' => 'Tatooine' , 'description'  => 'Tatooine est une planète désertique connu pour ses 2 soleils, pour les courses de modules et pour abriter les pires hors-la-loi de la galaxie.'    ,  'city' => 'Tatooine' )   ,
             array('name' => 'Naboo' , 'description'  => 'Les humains et les gungan cohabitent sur cette planète. Elle est la planète de naissance de Padmé Amidala, Jar Jar Binks ainsi que de Sheev Palpatine.'    ,  'city' => 'Naboo' )   ,
             array('name' => 'Starkiller' , 'description'  => 'La base Starkiller était une planète glacée et mobile avant que le Premier Ordre ne la terraforme environ 30 ans après la bataille d\'Endor. Cette planète abritait une super-arme capable d\'anéantir un système solaire'    ,  'city' => 'Starkiller' )   ,
             array('name' => 'Castle Black' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Castle Black' )   ,
             array('name' => 'Winterfell' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Winterfell' )   ,
             array('name' => 'White Harbor' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'White Harbor' )   ,
             array('name' => 'Moat Cailin' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Moat Cailin' )   ,
             array('name' => 'The Twins' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'The Twins' )   ,
             array('name' => 'The Eyrie' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'The Eyrie' )   ,
             array('name' => 'Gulltown' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Gulltown' )   ,
             array('name' => 'Kneeling Man' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Kneeling Man' )   ,
             array('name' => 'The Trident' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'The Trident' )   ,
             array('name' => 'Riverrun' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Riverrun' )   ,
             array('name' => 'Harroway\'s Twn' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Harroway\'s Twn' )   ,
             array('name' => 'Harrenhal' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Harrenhal' )   ,
             array('name' => 'Stoney Sept' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Stoney Sept' )   ,
             array('name' => 'Lannisport' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Lannisport' )   ,
             array('name' => 'King\'s Landing' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'King\'s Landing' )   ,
             array('name' => 'Bitterbridge' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Bitterbridge' )   ,
             array('name' => 'Storm\'s End' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Storm\'s End' )   ,
             array('name' => 'Summerhall' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Summerhall' )   ,
             array('name' => 'Ashford' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Ashford' )   ,
             array('name' => 'Highgarden' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Highgarden' )   ,
             array('name' => 'Tower of Joy' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Tower of Joy' )   ,
             array('name' => 'Oldtown' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Oldtown' )   ,
             array('name' => 'Nightsong' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Nightsong' )   ,
             array('name' => 'Sunspear' , 'description'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam lacinia tristique libero, sit amet lacinia diam interdum vel. Pellentesque ornare vulputate nibh et pretium. Nunc dapibus facilisis condimentum. Nulla non felis metus. Proin ac tristique dui. Vestibulum non tincidunt lorem. Morbi id posuere augue. In aliquet posuere lorem, vitae gravida sem vulputate nec. Etiam porttitor ultrices nibh, vel feugiat risus finibus et. Nam convallis neque ut maximus dignissim. In condimentum aliquam ipsum, in imperdiet libero gravida vel. Sed egestas rhoncus convallis.'    ,  'city' => 'Sunspear' )   ,
             array('name' => 'Minas Tirith ' , 'description'  => 'Minas Tirith est le nom qui fut donné à Minas Anor après la chute de Minas Ithil en III 2002. La cité-forteresse était construite sur sept niveaux fortifiés sur la Colline de Garde, de telle manière que chaque porte permettant d\'accéder à un niveau était orientée d\'une façon différente à celle du niveau inférieur (orientées Nord ou Sud selon), la Grande Porte étant elle tournée vers l\'Est'    ,  'city' => 'Minas Anor' )   ,
             array('name' => 'Rohan' , 'description'  => 'Royaume des Rohirrim, le Rohan était une ancienne province du Gondor, le Calenardhon qui fut offerte aux Éothéod par l\'Intendant Cirion en III 2510 en échange de l\'aide qu\'ils apportèrent au Gondor dans la guerre contre les Balchoth, en particulier lors de la Bataille du Champ du Celebrant, et du Serment d\'Eorl. Les premiers Rois battirent leur capitale, Edoras, sur une colline en contrebas de Dunharrow,'    ,  'city' => 'Terre du Milieu' )   ,
             array('name' => 'Minas Morgul' , 'description'  => 'Quand les Nazgûl capturèrent Minas Ithil en III 2002, la cité fut renommée Minas Morgul. Elle devint alors leur quartier général et c\'est de là que furent lancées maintes campagnes de harcèlement contre le Gondor et en particulier l\'Ithilien qui fut rapidement déserté. D\'aspect, la ville ressemblait toujours à Minas Ithil, mais une version pervertie de Minas Ithil o'    ,  'city' => 'Minas Ithil' )   ,
             array('name' => 'Fondcombe' , 'description'  => 'n l\'an II 1697, juste après la Guerre entre les Elfes et Sauron, Maître Elrond Peredhil fuit l\'Eregion avec les survivants des Gwaith-i-Mírdain. Alors que le royaume des Elfes-Forgerons d\'Eriador fut détruit. les Hauts-Elfes survivants construire le refuge de Fondcombe dans la vallée étroite, enfoncée et cachée d\'Imladris dans la partie la plus orientale d\'Eriador, au pied des Monts Brumeux, non loin de la Bruinen. Considérée comme la \'Dernière Maison Familière à l\'Est de l\'Océan'    ,  'city' => ' Monts Brumeux' )   ,
             array('name' => 'Isengard' , 'description'  => 'Forteresse fondée par le Gondor au début du Troisième Age, au temps du faîte de sa puissance, dans la partie occidentale du Nan Curunír, près de la source de l\'Isen. Elle était constituée d\'un grand mur circulaire en pierre entourant une plaine de près de deux kilomères de diamètre au centre de laquelle se trouvait la tour d\'Orthanc'    ,  'city' => 'Gondor' )   ,

          ];
    }

}
