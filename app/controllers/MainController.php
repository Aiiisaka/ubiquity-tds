<?php
namespace controllers;
use models\Group;
use models\Organization;
use models\User;
use services\dao\OrgaRepository;
use services\ui\UIGroups;
use Ubiquity\attributes\items\di\Autowired;
use Ubiquity\attributes\items\router\Route;
use Ubiquity\controllers\auth\AuthController;
use Ubiquity\controllers\auth\WithAuthTrait;
use Ubiquity\orm\DAO;
use Ubiquity\utils\http\USession;

/**
 * Controller MainController
 */
class MainController extends ControllerBase{
    use WithAuthTrait;

    #[Autowired]
    private OrgaRepository $repo;
    private UIGroups $ui;

    #[Route(path:'index', name:'home')]
    public function index() {
        $this->jquery->renderView('MainController/index.html');
    }

    #[Route(path:"user/utilisateur", name:"main.utilisateur")]
    public function utilisateur() {
        $user = DAO::getById(User::class, [1], false);
        $this->loadView('MainController/utilisateurs.html', ['user'=>$user]);
    }

    #[Route('user/details/{idUser}', name: 'user.details')]
    public function userDetails($idUser) {
        $user=DAO::getById(User::class, [$idUser], true);
        echo "Organization : ".$user->getOrganization();
    }

    #[Route('groups/list', name: 'groups.list')]
    public function listGroups() {
        $idOrga=USession::get('idOrga');
        $groups=DAO::getAll(Group::class, 'idOrganization= ?', false, [$idOrga]);
        $this->ui->listGroups($groups);
        $this->jquery->renderView('MainController/listGroups.html');
    }

    #[Get('new/user', name: 'new.user')]
    public function newUser() {
        $this->ui->newUser('frm-user');
        $this->jquery->renderView('main/vForm.html');
    }

    #[Post('new/user', name: 'new.userPost')]
    public function newUserPost() {
        $idOrga=USession::get('idOrga');
        $orga=DAO::getById(Organization::class,$idOrga,false);
        $user=new User();
        URequest::setValuesToObject($user);
        $user->setEmail(\strtolower($user->getFirstname().'.'.$user->getLastname().'@'.$orga->getDomain()));
        $user->setOrganization($orga);
        if(DAO::insert($user)){
            $count=DAO::count(User::class,'idOrganization= ?',[$idOrga]);
            $this->jquery->execAtLast('$("#users-count").html("'.$count.'")');
            $this->showMessage("Ajout d'utilisateur","L'utilisateur $user a été ajouté à l'organisation.",'success','check square outline');
        }else{
            $this->showMessage("Ajout d'utilisateur","Aucun utilisateur n'a été ajouté",'error','warning circle');
        }
    }

    #[Get('newOrga',name: 'newOrga')]
    public function orgaForm() {
        $this->uiService->orgaForm(new Organization());
        $this->jquery->renderDefaultView();
    }

    #[Post('addOrga', name:'addOrga')]
    public function addOrga() {
        var_dump($_POST);
    }

    public function setRepo(OrgaRepository $repo): void {
        $this->repo = $repo;
    }

    public function initialize() {
        $this->ui=new UIGroups($this);
        parent::initialize();
    }

    protected function getAuthController(): AuthController {
        return new MyAuth($this);
    }
}