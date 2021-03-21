<?php
namespace controllers;

use models\Basket;
use models\Basketdetail;
use models\Order;
use models\Product;
use models\Section;
use services\ui\StoreUI;
use Ubiquity\attributes\items\di\Autowired;
use services\dao\StoreRepository;
use Ubiquity\attributes\items\router\Route;
use Ubiquity\controllers\auth\AuthController;
use Ubiquity\controllers\auth\WithAuthTrait;
use Ubiquity\orm\DAO;
use Ubiquity\utils\http\UResponse;
use Ubiquity\utils\http\USession;

/**
 * Controller MainController
 **/
class MainController extends ControllerBase {
    use WithAuthTrait;

    public function initialize() {
        parent::initialize();
        $this -> ui = new StoreUI($this);
    }

    #[Autowired]
    private StoreRepository $repo;

	#[Route(path: "home", name: "home")]
	public function index() {
        USession::set('recentlyViewedProducts', []);
        $products = USession::get('recentlyViewedProducts');
        print_r($products);

        $numOrders = count(DAO::getAll(Order::class, 'idUser=?', false, [USession::get("idUser")]));
        $numBaskets = count(DAO::getAll(Basket::class, 'idUser=?', false, [USession::get("idUser")]));
        $produitsPromo = DAO::getAll(Product::class, 'promotion<?', false, [0]);
        $this->loadDefaultView(['numOrders'=>$numOrders, 'numBaskets'=>$numBaskets, 'produitsPromo'=>$produitsPromo, 'recentlyViewedProducts'=>$products]);
	}

    public function getRepo(): StoreRepository {
        return $this->repo;
    }

    public function setRepo(StoreRepository $repo): void {
        $this->repo = $repo;
    }

    #[Route(path:"store/order", name:"order")]
    public function order() {
        $order = DAO::getAll(Order::class, 'idUser=?', false, [USession::get("idUser")]);
        $this->loadDefaultView(['order'=>$order]);
    }

    #[Route(path:"store/browse", name:"store")]
    public function store() {
        $products = USession::get("recentlyViewedProducts");
        print_r($products);

        $section = DAO::getAll(Section::class, false, ['products']);
        $produitsPromo = DAO::getAll(Product::class, 'promotion<?', false, [0]);
        $this->loadDefaultView(['section'=>$section, 'produitsPromo'=>$produitsPromo, 'recentlyViewedProducts'=>$products]);
    }

    #[Route(path:"basket/new", name:"basket.new")]
    public function newBasket() {
        $newBasket = DAO::getAll(Order::class, 'idUser=?', false, [USession::get("idUser")]);
        $this->loadDefaultView(['newBasket'=>$newBasket]);
    }

    #[Route(path:"basket", name:"basket")]
    public function basket() {
        $basket = DAO::getAll(Basket::class, 'idUser=?', false, [USession::get("idUser")]);
        $this->loadDefaultView(['baskets'=>$basket]);
    }

    #[Route(path:"store/section/{id}", name:"section")]
    public function section($id) {
        $sections = DAO::getAll(Section::class, false, ['products']);
        $section = DAO::getById(Section::class, $id, ['products']);
        $this->loadDefaultView(['section'=>$section, 'sections'=>$sections]);
    }

    #[Route(path:"store/product/{idSection}/{idProduct}", name:"product")]
    public function product($idSection, $idProduct){
        $sections = DAO::getAll(Section::class, false, ['products']);
        $section = DAO::getById(Product::class, $idSection);
        $product = DAO::getById(Product::class, $idProduct);

        $products = USession::get("recentlyViewedProducts");
        print_r($products);
        USession::set("recentlyViewedProducts", $products);
        print_r($products);

        $this->loadDefaultView(['sections'=>$sections, 'product'=>$product, 'section'=>$section]);
    }

    #[Route(path:"basket/add/{idProduct}", name:"addProduct")]
    public function addProduct($idProduct){
        $basket = DAO::getOne(Basket::class, 'idUser=?', false, [USession::get("idUser")]);

        $details = new Basketdetail();
        $details->setBasket($basket);

        $details->setIdProduct($idProduct); $details->setQuantity(1);

        DAO::save($details);
        UResponse::header('location', '/home');
    }

    #[Route(path:"basket/add/{idBasket}/{idProduct}", name:"addProductTo")]
    public function addProductTo($idBasket, $idProduct){
        $this->loadDefaultView();
    }

	protected function getAuthController(): AuthController {
        return new MyAuth($this);
    }
}
