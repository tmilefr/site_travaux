<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * CrudController
 *
 * Contrôleur de base : CRUD générique (list / add / edit / view / delete / bulk / export CSV)
 * piloté par les schémas JSON des modèles (app/Models/json) et par Render_object.
 *
 * Cycle de vie CodeIgniter 4 :
 *   initController() -> boot() (réglages du contrôleur enfant) -> action
 *
 * Un contrôleur enfant surcharge boot() pour déclarer $_controller_name, $_model_name,
 * $_autorize, etc. puis appelle $this->init() quand ces réglages sont posés.
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
#[\AllowDynamicProperties]
abstract class CrudController extends BaseController
{
	/* VARS*/
	protected $_autorised_get_key 	= array(
				'order','direction','filter','page','repertoire','search','id','per_page',
				'order_push','order_clear','column_toggle'
			);

	protected $_redirect			= true; //redirect page after POST
	protected $_model_name			= FALSE;
	protected $_debug_array  		= array();
	protected $_debug 				= FALSE;
	/* used for right */
	protected $_controller_name 	= null;
	protected $_action			 	= null;
	protected $_rules				= null;
	protected $_autorize			= array();
	protected $_search  			= false;
	protected $_dba_data				= null;

	protected $_edit_view = '';
	protected $_list_view = '';
	protected $_bg_color = '';
	protected $view_inprogress 		= null;
	protected $data_view 			= array();
	protected $title 				= '';
	protected $json = null;
	protected $json_path = APPPATH.'Models/json/';
	protected $per_page	= 15;//pagination
	protected $next_view = 'list';
	protected $render_view = true; //render view @ end of process or not ? used for decoration.
	protected $_api  = FALSE; //trois mode possible, HTML par defaut, CLI dépendant de PHP_SAPI et API dépendant de cette variable.

	/** Le CRUD générique (list, add, edit...) est-il joignable par URL pour ce contrôleur ? */
	protected $_expose_crud = true;

	/** Règles de validation par modèle : [modèle => [champ => ['label' => ..., 'rules' => ...]]] */
	protected $validation_rules = [];

	/* Services partagés (voir app/Config/Services.php) */
	/** @var \CodeIgniter\Session\Session */
	public $session = null;
	/** @var \CodeIgniter\Database\BaseConnection */
	public $db = null;
	/** @var \App\Libraries\Render_object */
	public $render_object = null;
	/** @var \App\Libraries\Bootstrap_tools */
	public $bootstrap_tools = null;
	/** @var \App\Libraries\Acl */
	public $acl = null;

	/**
	 * Liste blanche des colonnes que l'utilisateur peut masquer dans la liste.
	 * Vide par défaut : toutes les colonnes du JSON marquées "list:true" sont
	 * masquables. Un contrôleur peut restreindre via :
	 *   $this->_hideable_columns = ['email', 'phone'];
	 */
	protected $_hideable_columns = array();

	/**
	 * Actions groupées disponibles depuis la liste. Format :
	 *   ['action_name' => ['label_key' => '...', 'class' => 'btn-warning', 'confirm' => true]]
	 * 'delete' est inclus par défaut si $_autorize['delete'] = true.
	 */
	protected $_bulk_actions = array();

	/**
	 * Initialisation CI4 : prépare les services puis appelle boot() du contrôleur enfant.
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		$this->session         = service('session');
		$this->db              = db_connect();
		$this->acl             = service('acl');
		$this->render_object   = service('renderObject');
		$this->bootstrap_tools = service('bootstrapTools');

		$this->render_object->_set('controller', $this);

		// Langue choisie par l'utilisateur (Translations_controller::switch_lang)
		$locale = $this->session->get('user_language');
		if ($locale && preg_match('/^[a-z][a-z0-9_\-]*$/i', (string) $locale)) {
			$this->request->setLocale($locale);
		}

		$this->boot();
	}

	/** Méthodes publiques de CrudController joignables par URL (CRUD générique). */
	private const CRUD_ACTIONS = ['list', 'add', 'edit', 'delete', 'view', 'index', 'clear_filters', 'export_csv', 'bulk', 'Jsondata'];

	/**
	 * Point d'entrée des routes : /Controleur/action/p1/p2... => action(p1, p2...).
	 *
	 * @param string $action    action demandée dans l'URL (insensible à la casse)
	 * @param string ...$params segments restants de l'URL, transmis à l'action
	 *
	 * @return mixed réponse de l'action
	 */
	public function dispatch(string $action = 'index', string ...$params)
	{
		$method = $this->resolveAction($action);

		return $this->{$method}(...$params);
	}

	/**
	 * Nom réel de l'action si elle est joignable par URL, sinon 404.
	 *
	 * Joignables : les méthodes publiques déclarées par le contrôleur lui-même
	 * (hors préfixe _ et hors outils internes) et, si $_expose_crud, le CRUD générique.
	 */
	protected function resolveAction(string $action): string
	{
		$wanted = strtolower($action);

		foreach (get_class_methods($this) as $method) {
			if (strtolower($method) !== $wanted || $method[0] === '_') {
				continue;
			}
			$reflection = new \ReflectionMethod($this, $method);
			if (! $reflection->isPublic() || $reflection->isStatic()) {
				continue;
			}
			$declaring = $reflection->getDeclaringClass()->getName();

			if ($declaring === self::class) {
				// CRUD générique : seulement pour les contrôleurs qui déclarent $_autorize,
				// et list/add/edit/delete/view uniquement s'ils y figurent
				if ($this->_expose_crud && ! empty($this->_autorize) && in_array($method, self::CRUD_ACTIONS, true)
					&& (! in_array($method, ['list', 'add', 'edit', 'delete', 'view'], true) || array_key_exists($method, $this->_autorize))) {
					return $method;
				}
				break;
			}
			if ($declaring !== BaseController::class && ! str_starts_with($declaring, 'CodeIgniter\\') && ! in_array($method, ['boot', 'initController', 'init', 'LoadModel', 'render_view', 'set_ref_field', 'set_civil_years', 'SaveToJson', 'LoadJsonData'], true)) {
				return $method;
			}
			break;
		}

		throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($action);
	}

	/** Action courante lue dans l'URL (Controleur/action/...), « index » par défaut. */
	protected function currentAction(): string
	{
		$segments = array_values(array_filter(explode('/', trim($this->request->getPath(), '/')), 'strlen'));

		return strtolower($segments[1] ?? 'index');
	}

	/**
	 * Réglages propres au contrôleur (à surcharger).
	 */
	protected function boot(): void
	{
	}

	/**
	 * Redirection qui interrompt l'action en cours (exception gérée par CI4).
	 */
	protected function goTo(string $uri, int $code = 302): void
	{
		throw new RedirectException(redirect()->to($uri, $code));
	}

	/** Interrompt la requête avec une erreur HTTP (404 => page introuvable). */
	protected function abort(int $code, string $message): void
	{
		if ($code === 404) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($message);
		}

		throw new \RuntimeException($message, $code);
	}

	/** Nom court (sans namespace) du contrôleur routé. */
	protected function routedClass(): string
	{
		$name = service('router')->controllerName();

		return is_string($name) ? substr(strrchr('\\' . $name, '\\'), 1) : '';
	}

	public function SaveToJson($name, $data){
		$txt = '{"'.str_replace(['_data','.json'] ,['',''] ,$name).'":'.json_encode($data).'}';
		file_put_contents($this->json_path.$name, $txt);
	}

	public function Jsondata($field,$model_from_url = null){
		$json = '[';
		$model = $this->_model_name;
		//autre model utilisé pour l'objet
		if ($pos = strpos($field,'_')){
			$model2 = substr($field,$pos+1);
			if (isset($this->$model2)) {
				$model = substr($field,$pos+1);
				$field = substr($field,0,$pos);
			}
		}
		if ($model_from_url){
			$model = $model_from_url;
		}

		$def = $this->{$model}->_get('defs')[$field];
		if ( method_exists( $def ,'JsonData') &&  $def->_get('query') ){//new methode for set datas
			$json .= $def->Jsondata();
		} else {
			$tmp = '';
			if (count($def->_get('values')))
			foreach($def->_get('values') AS $key => $value){
				$tmp .= '{ "id":"'.$key.'", "label":"'.$value.'"},';
			}
			$json .=  substr($tmp,0,-1);
		}

		return $this->response->setContentType('application/json')->setBody($json.']');
	}

	/**
	 * @brief Load Json
	 */
	public function LoadJsonData($json,$model,$path){
		$this->$model = model($model);
		$json = file_get_contents($this->json_path.$json);
		$json = json_decode($json);
		foreach($json->{$path} AS $element){
			echo '<pre>'.print_r($element, true).'</pre>';
		}
	}

	/**
	 * @brief Controller initialisation
	 */
	function init(){
		$this->_process_url();

		$cfg = config('Travaux');
		$this->data_view['app_name'] 	= $cfg->appName;
		$this->data_view['slogan'] 		= $cfg->slogan;
		$this->data_view['title'] 		= $this->title;

		$this->data_view['raw_url']		= $this->_controller_name.'/'.$this->_action;

		$this->data_view['footer_line'] = '';
		if ($cfg->debugApp === 'debug') {
			$this->_set('_debug', TRUE);
		}

		$option = [
			'filter'=>$this->session->get($this->set_ref_field('filter')),
			'direction'=>$this->session->get($this->set_ref_field('direction')),
		];

		$this->render_object->SetOption($option);

		if ($this->_model_name){
			$this->LoadModel($this->_model_name);
			$this->render_object->_set('datamodel', $this->_model_name);
			$this->bootstrap_tools->_set('_controller_name', $this->_controller_name);
			/* TODO */
			$this->data_view['_model_name'] = $this->_model_name;// Need ?
		}

		/* FIX FILED NOT IN TABLE IN LIST => todo : filter by page */
		$filter = $this->session->get($this->set_ref_field('filter'));
		$autorized_fields = $this->{$this->_model_name}->_get('autorized_fields');
		$autorized_fields[] = 'civil_year'; //cas particulier
		if (is_array($filter) && count($filter)){
			foreach($filter AS $key=>$value){
				if (!in_array($key,$autorized_fields))
					unset($filter[$key]);
			}
			$this->session->set( $this->set_ref_field('filter') , $filter);
		}

		//Create CRUD URL
		foreach($this->_autorize AS $key=>$value){
			$this->_set_ui_rules($key , $value);
		}
		//to permit use it in view.
		$this->render_object->_set('_ui_rules' , $this->_rules);
		$this->_debug($this->_rules, __FUNCTION__, '_ui_rules', __FILE__,181);

		// Services exposés aux vues
		$this->data_view['render_object']   = $this->render_object;
		$this->data_view['bootstrap_tools'] = $this->bootstrap_tools;
		$this->data_view['acl']             = $this->acl;

		$search_object 					= new StdClass();
		$search_object->url 			= $this->routedClass().'/'.$this->currentAction();
		$search_object->global_search 	= $this->session->get($this->set_ref_field('global_search'));
		$search_object->autorize 		= FALSE;
		$this->data_view['search_object'] = $search_object;
	}

	/**
	 * Charge un modèle, ses définitions de champs et ses règles de validation.
	 */
	public function LoadModel($model){
		$this->$model = model($model);
		$this->{$model}->_set('_debug', $this->_debug);
		$this->{$model}->_init_def(); //here for option event

		$this->_debug($model, __FUNCTION__, 'init', __FILE__,203);

		$config = $this->render_object->Set_Rules_elements($model, $this->{$model}); //loading Infos_model ELements
		$this->validation_rules[$model] = $config;
	}

	/**
	 * Valide les données postées (ou $data) avec les règles du modèle.
	 *
	 * Comme avec CodeIgniter 3 : sans données le résultat est FALSE (aucune erreur
	 * enregistrée), et la règle "trim" nettoie les valeurs postées.
	 *
	 * @param string     $model  nom du modèle dont on prend les règles
	 * @param array|null $data   données à valider (défaut : POST)
	 */
	protected function runValidation(string $model, ?array $data = null): bool
	{
		$fromPost = ($data === null);
		$data   ??= $this->request->getPost() ?? [];
		$config   = $this->validation_rules[$model] ?? [];
		if (empty($data) || empty($config)) {
			return false;
		}

		$rules = [];
		foreach ($config as $row) {
			$list = array_filter(explode('|', (string) $row['rules']), static fn ($r) => $r !== '');
			// CI4 n'a pas de règle qui modifie la valeur : "trim" nettoie les données avant validation
			if (in_array('trim', $list, true)) {
				if (isset($data[$row['field']]) && is_string($data[$row['field']])) {
					$data[$row['field']] = trim($data[$row['field']]);
				}
				$list = array_diff($list, ['trim']);
			}
			// Un champ vide non requis n'est pas soumis aux autres règles
			if (! in_array('required', $list, true)) {
				array_unshift($list, 'permit_empty');
			}
			$rules[$row['field']] = ['label' => $row['label'], 'rules' => implode('|', $list)];
		}

		if ($fromPost) {
			$this->request->setGlobal('post', $data);
		}

		return $this->validateData($data, $rules);
	}

	/**
	 * @brief 		Render View in Template
	 */
	function render_view(){
		// CI4 capture ce qui est affiché par l'action et en fait le corps de la réponse
		if ($this->request->isAJAX()){
			echo view($this->view_inprogress, $this->data_view);
		} else {
			echo view('template/head', $this->data_view);

			// Nettoie le préfixe 'unique/' s'il est déjà présent
			$view_clean = str_replace('unique/', '', $this->view_inprogress);

			$view = rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR
				  . 'Views' . DIRECTORY_SEPARATOR
				  . 'unique' . DIRECTORY_SEPARATOR
				  . $this->_controller_name . DIRECTORY_SEPARATOR
				  . $view_clean . ((strpos($this->view_inprogress,'.php')) ? '':'.php');
			if (is_file($view)){
				$this->view_inprogress = 'unique/' . $this->_controller_name . '/' . $view_clean;
			}

			echo view($this->view_inprogress, $this->data_view);
			echo view('template/footer', $this->data_view);
		}

		if ($this->_debug) {
			d($this->_debug_array);
		}
	}

	/**
	 * @brief Attach variable to controller name
	 */
	public function set_ref_field($name){
		return $name.'_'.$this->_controller_name;
	}

	/**
	 * @brief Generic list view
	 */
	public function list()
	{
		if ($this->_search)
			$this->data_view['search_object']->autorize = true;

		$session_pp = (int) $this->session->get($this->set_ref_field('per_page'));
		$effective_pp = $session_pp > 0 ? $session_pp : $this->per_page;

		// Pile de tris (vague 2) : si elle existe, elle prime sur order/direction simples.
		$order_stack = $this->session->get($this->set_ref_field('order_stack')) ?: array();
		$dir_stack   = $this->session->get($this->set_ref_field('direction_stack')) ?: array();

		if (!empty($order_stack)) {
			$this->{$this->_model_name}->_set('order',     $order_stack);
			$this->{$this->_model_name}->_set('direction', $dir_stack);
		} else {
			$this->{$this->_model_name}->_set('order',     $this->session->get($this->set_ref_field('order')));
			$this->{$this->_model_name}->_set('direction', $this->session->get($this->set_ref_field('direction')));
		}

		$this->{$this->_model_name}->_set('global_search', $this->session->get($this->set_ref_field('global_search')));
		$this->{$this->_model_name}->_set('filter',        $this->session->get($this->set_ref_field('filter')));
		$this->{$this->_model_name}->_set('per_page',      $effective_pp);
		$this->{$this->_model_name}->_set('page',          $this->session->get($this->set_ref_field('page')));

		// Model::paginate() : le pager partagé mémorise page courante et total ; l'URL est Controleur/list/page/N
		$model = $this->{$this->_model_name};
		$this->data_view['fields'] = $model->_get('autorized_fields');
		$this->data_view['datas']  = $model->get();
		$total_rows = $model->get_pagination();
		$cur_page   = service('pager')->getCurrentPage();

		$model->pager->setPath($this->_controller_name.'/list/page');
		$this->data_view['pagination_links'] = $model->pager->links('default', 'app_bootstrap');

		// Vague 1
		$this->data_view['total_rows']       = (int) $total_rows;
		$this->data_view['per_page']         = $effective_pp;
		$this->data_view['per_page_options'] = array(15, 30, 50, 100);
		$this->data_view['cur_page']         = (int) $cur_page;
		$this->data_view['active_filters']   = $this->session->get($this->set_ref_field('filter')) ?: array();
		$this->data_view['global_search']    = $this->session->get($this->set_ref_field('global_search'));

		// Vague 2 : tri secondaire + colonnes masquables + bulk actions
		$this->data_view['order_stack']      = $order_stack;
		$this->data_view['direction_stack']  = $dir_stack;
		$this->data_view['hidden_columns']   = $this->session->get($this->set_ref_field('hidden_columns')) ?: array();
		$this->data_view['hideable_columns'] = $this->_hideable_columns;
		$this->data_view['bulk_actions']     = $this->_get_bulk_actions();

		// Pousse aussi les piles dans le Render_object pour render_link
		$this->render_object->_set('_options', array_merge(
			$this->render_object->_get('_options'),
			array(
				'order_stack'     => $order_stack,
				'direction_stack' => $dir_stack,
				'hidden_columns'  => $this->data_view['hidden_columns'],
			)
		));

		$this->_set('view_inprogress','unique/list_view');
		if ($this->render_view)
			$this->render_view();
	}

	/**
	 * Construit la liste finale des actions de masse en intégrant 'delete'
	 * si autorisé. Les contrôleurs métier peuvent enrichir $_bulk_actions
	 * dans boot(), par exemple :
	 *   $this->_bulk_actions['archive'] = ['label_key'=>'BULK_ARCHIVE','class'=>'btn-warning','confirm'=>true];
	 */
	protected function _get_bulk_actions(){
		$actions = $this->_bulk_actions;
		if (!empty($this->_autorize['delete']) && $this->acl->hasAccess($this->_controller_name.'/delete')) {
			$actions = array_merge(
				array('delete' => array(
					'label_key' => 'BULK_DELETE',
					'class'     => 'btn-danger',
					'confirm'   => true,
				)),
				$actions
			);
		}
		return $actions;
	}

	/**
	 * @brief Réinitialise les filtres de colonnes, la recherche globale
	 *        et la page courante pour la liste du contrôleur appelant.
	 */
	public function clear_filters()
	{
		$this->session->set( $this->set_ref_field('filter')        , array() );
		$this->session->set( $this->set_ref_field('global_search') , ''      );
		$this->session->set( $this->set_ref_field('page')          , 1       );
		$this->goTo($this->_controller_name . '/list');
	}

	/**
	 * @brief Genric View Method
	 */
	public function view($id){
		if ($id){
			$this->render_object->_set('id',		$id);
			$this->{$this->_model_name}->_set('key_value',$id);
			$this->_dba_data = $this->{$this->_model_name}->get_one();
			$this->render_object->_set('dba_data',$this->_dba_data);
		}
		$this->_set('view_inprogress',$this->_list_view);
		if ($this->render_view)
			$this->render_view();

	}

	/**
	 * @brief DELETE Method
	 */
	public function delete($id = 0){
		if ($id){

			$fields = $this->{$this->_model_name}->_get('defs');
			foreach($fields AS $field){
				if (!in_array($field->_get('name'),['id','created','updated'])){
					$child_model = $field->_get('model');
					if($child_model != ''){
						//effacement des elements lié (TODO : utilisation de clé de contrainte dans la base de donnée)
						if (method_exists($this->{$child_model},'DeleteLink'))
							$this->{$child_model}->DeleteLink($field->_get('foreignkey'), $id);
					}
				}
			}
			$this->{$this->_model_name}->delete($id);
		}
		$this->goTo($this->_get('_rules')[$this->next_view]->url);
	}

	/**
	 * @brief ADD Method
	 */
	public function add(){
		$this->render_object->_set('form_mod', 'add');
		$this->edit();
	}

	/**
	 * @brief Edition Method
	 */
	public function edit($id = 0)
	{
		$this->data_view['id'] = '';
		if (!$id){
			if ($this->request->getPost('id') ){
				$id = $this->request->getPost('id');
			}
		}
		if ($id){
			$this->render_object->_set('id',		$id);
			$this->{$this->_model_name}->_set('key_value',$id);
			$dba_data = $this->{$this->_model_name}->get_one();
			$this->render_object->_set('dba_data',$dba_data);
			$this->render_object->_set('form_mod', 'edit');
			$this->data_view['id'] = $id;
		}

		if ($this->request->getPost('form_mod')){
			if ($this->runValidation($this->_model_name) === FALSE){
				$this->_debug(implode(' ', service('validation')->getErrors()),'edit','form_validation',__FILE__,__LINE__);
			} else {
				$datas = $this->_ProcessPost($this->_model_name);
				if ($this->_redirect){
					$this->goTo($this->_get('_rules')[$this->next_view]->url);
				}
			}
		}

		$this->data_view['required_field'] = $this->{$this->_model_name}->_get('required');

		$this->_set('view_inprogress',$this->_edit_view);
		$this->render_view();
	}

	/**
	 * @brief Router Default
	 */
	public function index(){
		$this->goTo($this->_get('_rules')['list']->url);
	}

	/**
	 * Method _debug : Set Debug Array
	 */
	function _debug($message , $from = null , $type = null, $file = null, $line = null){
		$msg = new Stdclass();
		$msg->message = $message;
		$msg->from = $from;
		$msg->type = $type;
		$msg->file = $file;
		$msg->line = $line;

		$this->_debug_array[] = $msg;
	}

	/**
	 * Segments de l'URL à partir du n-ième, lus deux par deux : /cle/valeur/cle/valeur.
	 */
	protected function uriToAssoc(int $from = 3): array
	{
		$segments = array_values(array_filter(explode('/', trim($this->request->getPath(), '/')), 'strlen'));
		$segments = array_slice($segments, $from - 1);
		$assoc    = [];
		for ($i = 0, $c = count($segments); $i < $c; $i += 2) {
			$assoc[$segments[$i]] = $segments[$i + 1] ?? null;
		}

		return $assoc;
	}

	/**
	 * Method _process_url : Processing variable on url
	 */
	private function _process_url(){

		if ($this->request->getPost('global_search')){
			$this->session->set( $this->set_ref_field('global_search') ,$this->request->getPost('global_search'));
		}
		$segments = array_values(array_filter(explode('/', trim($this->request->getPath(), '/')), 'strlen'));
		$this->_action = $segments[1] ?? 0;

		/* FIX FILED NOT IN TABLE IN LIST */
		$filter = $this->session->get($this->set_ref_field('filter'));
		if (isset($filter['filter']))
			unset($filter['filter']);
		$this->session->set( $this->set_ref_field('filter') , $filter);

		$array = $this->uriToAssoc(3);

		foreach($array AS $field=>$value){
			if (in_array($field,$this->_autorised_get_key)){
				switch($field){
					case 'search':
						$this->session->set( $this->set_ref_field('global_search') ,'');
					break;
					case 'filter':
						$filtered = $this->session->get( $this->set_ref_field('filter') );
						if ($array['filter_value'] == 'all'){
							unset($filtered[$value]);
						} else {
							$filtered[$value] = $array['filter_value'];
						}
						$this->session->set( $this->set_ref_field('filter') , $filtered);
					break;
					case 'per_page':
						// Liste blanche pour éviter qu'un utilisateur stocke n'importe quoi en session
						$allowed_pp = array(15, 30, 50, 100);
						$pp = (int) $value;
						if (in_array($pp, $allowed_pp, true)) {
							$this->session->set(
								$this->set_ref_field('per_page'),
								$pp
							);
							// Si on change le per_page, repartir page 1 pour ne pas tomber hors plage
							$this->session->set(
								$this->set_ref_field('page'),
								1
							);
						}
					break;
					case 'order_push':
						// Empile un nouveau critère de tri en respectant la pile existante.
						// Si le champ est déjà dans la pile, on inverse sa direction ;
						// sinon on l'ajoute en fin de pile.
						$stack = $this->session->get($this->set_ref_field('order_stack')) ?: array();
						$dirs  = $this->session->get($this->set_ref_field('direction_stack')) ?: array();

						$idx = array_search($value, $stack, true);
						if ($idx !== false) {
							// Toggle direction
							$dirs[$idx] = (isset($dirs[$idx]) && $dirs[$idx] === 'asc') ? 'desc' : 'asc';
						} else {
							$stack[] = $value;
							$dirs[]  = 'asc';
							// Limite raisonnable : pile de 3 tris max
							if (count($stack) > 3) {
								array_shift($stack);
								array_shift($dirs);
							}
						}
						$this->session->set($this->set_ref_field('order_stack'),     $stack);
						$this->session->set($this->set_ref_field('direction_stack'), $dirs);
					break;

					case 'order_clear':
						$this->session->set($this->set_ref_field('order_stack'),     array());
						$this->session->set($this->set_ref_field('direction_stack'), array());
					break;

					case 'column_toggle':
						$hidden = $this->session->get($this->set_ref_field('hidden_columns')) ?: array();
						if (in_array($value, $hidden, true)) {
							$hidden = array_values(array_diff($hidden, array($value)));
						} else {
							$hidden[] = $value;
						}
						$this->session->set($this->set_ref_field('hidden_columns'), $hidden);
					break;
					default:
						$this->session->set( $this->set_ref_field($field) , $value );
					break;
				}
			}
		}
	}

	/**
	 * Method _ProcessPost : écrit en base les champs postés du modèle.
	 */
	function _ProcessPost($model_name, $override_fields = null){
		$datas = array();
		if ($override_fields){
			$fields =  $override_fields;
		} else{
			$fields = $this->{$model_name}->_get('autorized_fields');
		}
		$this->render_object->_set('post_data', $this->request->getPost());
		foreach($fields AS $field){
			// created / updated : horodatage natif du modèle, jamais fourni par le formulaire
			if (in_array($this->{$model_name}->_get('defs')[$field]->_get('type'), ['created','updated'], true)){
				continue;
			}
			if (method_exists($this->{$model_name}->_get('defs')[$field],'PrepareForDBA')){
				$datas[$field] 	= $this->{$model_name}->_get('defs')[$field]->PrepareForDBA($this->request->getPost($field));
			} else {
				$datas[$field] 	= $this->request->getPost($field);
			}
		}

		if ($this->request->getPost('form_mod') == 'edit'){
			if (isset($datas['id']) AND $id = $datas['id']){
				$this->{$model_name}->_set('key_value', $id);
				$this->{$model_name}->_set('datas', $datas);
				$this->{$model_name}->put();
			}
		} else if ($this->request->getPost('form_mod') == 'add'){
			$this->data_view['id'] = $this->{$model_name}->post($datas);
			$datas['id'] = $this->data_view['id'];
		}

		foreach($this->{$model_name}->_get('autorized_fields') AS $field){
			if (method_exists($this->{$model_name}->_get('defs')[$field],'AfterExec')){
				$this->{$model_name}->_get('defs')[$field]->AfterExec($datas);
			}
		}
		return $datas;
	}

	//must push out this stuff
	function set_civil_years($from = null){
		$filtered = $this->session->get( $this->set_ref_field('filter') );

		if (isset($filtered['civil_year'])){
			return $filtered['civil_year'];
		} else {
			$civil_year = config('Travaux')->civilYear;
			$this->session->set( $this->set_ref_field('filter') , ['civil_year'=>$civil_year]);
			return $civil_year;
		}
	}

	/**
	 * @brief Set Rules for CRUD URL
	 */
	function _set_ui_rules($key,$value){
		$rules = new StdClass();
		$rules->url 	=  base_url($this->_controller_name.'/'.$key);
		$rules->term 	= $key;
		$rules->name 	= tr(strtoupper($key).'_'.$this->_controller_name);
		if (!$this->acl->hasAccess(strtolower($this->_controller_name.'/'.$key))){
			$value = FALSE;
		}
		$rules->autorize= $value;
		$rules->icon 	= tr($key.'_icon');
		$rules->class  = tr($key.'_class');
		$this->_rules[$key] = $rules;
	}

	/**
	 * Réponse JSON avec code HTTP.
	 *
	 * @param mixed $data
	 */
	public function _renderJson($code, $data){
		return $this->response->setStatusCode((int) $code)->setJSON($data);
	}

	/**
	 * @brief Generic SETTER
	 */
	public function _set($field,$value){
		$this->$field = $value;
	}

	/**
	 * @brief Generic GETTER
	 */
	public function _get($field){
		return $this->$field;
	}

	/**
	 * @brief Exporte la liste courante au format CSV en respectant les
	 *        filtres, la recherche globale, le tri et les colonnes
	 *        masquées. Pas de pagination : tout est exporté.
	 */
	public function export_csv()
	{
		// Reproduire les mêmes _set() que list()
		$order_stack = $this->session->get($this->set_ref_field('order_stack')) ?: array();
		$dir_stack   = $this->session->get($this->set_ref_field('direction_stack')) ?: array();

		if (!empty($order_stack)) {
			$this->{$this->_model_name}->_set('order',     $order_stack);
			$this->{$this->_model_name}->_set('direction', $dir_stack);
		} else {
			$this->{$this->_model_name}->_set('order',     $this->session->get($this->set_ref_field('order')));
			$this->{$this->_model_name}->_set('direction', $this->session->get($this->set_ref_field('direction')));
		}
		$this->{$this->_model_name}->_set('global_search', $this->session->get($this->set_ref_field('global_search')));
		$this->{$this->_model_name}->_set('filter',        $this->session->get($this->set_ref_field('filter')));
		$this->{$this->_model_name}->_set('per_page',      0); // pas de limite
		$this->{$this->_model_name}->_set('page',          1);

		$datas = $this->{$this->_model_name}->get_all_filtered();
		$defs  = $this->{$this->_model_name}->_get('defs');
		$hidden = $this->session->get($this->set_ref_field('hidden_columns')) ?: array();

		// Liste finale des champs à exporter (respect du flag list:true et des colonnes masquées)
		$columns = array();
		foreach ($defs as $field => $def) {
			if ($def->_get('list') === true && !in_array($field, $hidden, true)) {
				$columns[] = $field;
			}
		}

		$filename = $this->_controller_name . '_' . date('Y-m-d_His') . '.csv';
		$out = fopen('php://temp', 'w+');

		// BOM UTF-8 pour qu'Excel ouvre les accents correctement
		fwrite($out, "\xEF\xBB\xBF");

		// Ligne d'en-tête : libellés i18n
		$header_row = array();
		foreach ($columns as $field) {
			$label = tr($field);
			$header_row[] = $label ?: $field;
		}
		fputcsv($out, $header_row, ';', '"');

		// Lignes de données : on remplace les ID par leur libellé via les "values"
		foreach ($datas as $row) {
			$line = array();
			foreach ($columns as $field) {
				$raw = isset($row->{$field}) ? $row->{$field} : '';
				$values = $defs[$field]->_get('values');
				if (is_array($values) && isset($values[$raw])) {
					$line[] = $values[$raw];
				} else {
					// Strip HTML éventuel + retours chariot pour rester dans une cellule
					$clean = strip_tags((string) $raw);
					$clean = preg_replace('/\s+/', ' ', $clean);
					$line[] = trim($clean);
				}
			}
			fputcsv($out, $line, ';', '"');
		}

		rewind($out);
		$csv = stream_get_contents($out);
		fclose($out);

		return $this->response
			->download($filename, $csv)
			->setContentType('text/csv', 'UTF-8')
			->setHeader('Cache-Control', 'no-store, no-cache');
	}

	/**
	 * @brief Exécute une action sur un lot d'éléments sélectionnés dans la liste.
	 */
	public function bulk()
	{
		$action = $this->request->getPost('bulk_action');
		$ids    = $this->request->getPost('bulk_ids');

		if (!is_array($ids) || empty($ids) || !$action) {
			$this->session->setFlashdata('bulk_error', tr('BULK_NOTHING_SELECTED'));
			$this->goTo($this->_controller_name . '/list');
		}

		// Whitelist : l'action doit figurer dans _get_bulk_actions()
		$allowed = $this->_get_bulk_actions();
		if (!isset($allowed[$action])) {
			$this->session->setFlashdata('bulk_error', tr('BULK_FORBIDDEN'));
			$this->goTo($this->_controller_name . '/list');
		}

		if ($action === 'delete') {
			$nb = $this->{$this->_model_name}->delete_bulk($ids);
			$this->session->setFlashdata(
				'bulk_success',
				sprintf(tr('BULK_DELETED_X'), $nb)
			);
		} else {
			// Action custom : la méthode bulk_<action>() doit exister dans le contrôleur
			$method = 'bulk_' . $action;
			if (method_exists($this, $method)) {
				$this->{$method}($ids);
			} else {
				$this->session->setFlashdata('bulk_error', tr('BULK_NOT_IMPLEMENTED'));
			}
		}

		$this->goTo($this->_controller_name . '/list');
	}
}
