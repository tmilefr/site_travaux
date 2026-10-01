<?php

namespace App\Libraries\Compat;

/**
 * Equivalent de CI_Loader : $this->load->library() / model() / view() ...
 *
 * Les composants sont attaches au controleur courant, comme en CI3
 * ($CI->acl, $CI->Familys_model ...).
 */
class Loader
{
    /** Bibliotheques CI3 remplacees par un adaptateur */
    protected $core = [
        'session'         => Session::class,
        'email'           => Email::class,
        'pagination'      => Pagination::class,
        'form_validation' => Form_validation::class,
    ];

    /** @var array<string,string> nom en minuscules => classe complete */
    protected $libraries = null;

    /** Variables de vue cumulees (comportement CI3 : visibles dans les vues imbriquees) */
    protected $cachedVars = [];

    protected function ci()
    {
        return Ci3::get();
    }

    protected function libraryMap(): array
    {
        if ($this->libraries === null) {
            $this->libraries = [];
            foreach (glob(APPPATH . 'Libraries/*.php') as $file) {
                $name = basename($file, '.php');
                $this->libraries[strtolower($name)] = 'App\\Libraries\\' . $name;
            }
        }
        return $this->libraries;
    }

    /**
     * Charge une bibliotheque et l'attache au controleur ($CI->nom_en_minuscules).
     */
    public function library($library = '', $params = null, $object_name = null)
    {
        if (is_array($library)) {
            foreach ($library as $key => $value) {
                is_int($key) ? $this->library($value, $params) : $this->library($key, $params, $value);
            }
            return $this;
        }

        if ($library === '' || $library === null) {
            return $this;
        }

        $key  = strtolower($library);
        $name = $object_name ?: $key;
        $ci   = $this->ci();

        if (isset($ci->$name) && is_object($ci->$name)) {
            return $this;
        }

        if (isset($this->core[$key])) {
            $class = $this->core[$key];
        } else {
            $map = $this->libraryMap();
            if (!isset($map[$key])) {
                throw new \RuntimeException('Unable to locate the specified class: ' . $library);
            }
            $class = $map[$key];
        }

        $ci->$name = $params !== null ? new $class($params) : new $class();
        return $this;
    }

    /**
     * Charge un modele ($CI->Nom_du_modele ou alias).
     */
    public function model($model, $name = '', $db_conn = false)
    {
        if (is_array($model)) {
            foreach ($model as $key => $value) {
                is_int($key) ? $this->model($value, '') : $this->model($key, $value);
            }
            return $this;
        }

        if ($model === '' || $model === null) {
            return $this; // comportement CI3 : un nom vide est ignore
        }

        $model = str_replace('/', '\\', trim($model, '/'));
        $short = ($pos = strrpos($model, '\\')) !== false ? substr($model, $pos + 1) : $model;
        $name  = $name ?: $short;
        $ci    = $this->ci();

        if (isset($ci->$name) && is_object($ci->$name)) {
            return $this;
        }

        $class = 'App\\Models\\' . $model;
        if (!class_exists($class)) {
            throw new \RuntimeException('Unable to locate the model you have specified: ' . $model);
        }
        $ci->$name = new $class();
        return $this;
    }

    public function helper($helpers = [])
    {
        helper($helpers);
        return $this;
    }

    public function database($params = '', $return = false, $query_builder = null)
    {
        $ci = $this->ci();
        if (!isset($ci->db) || !is_object($ci->db)) {
            $ci->db = Db::shared();
        }
        return $return ? $ci->db : $this;
    }

    public function dbforge()
    {
        $this->ci()->dbforge = new Forge();
        return $this;
    }

    public function config($file = '', $use_sections = false, $fail_gracefully = false)
    {
        return $this->ci()->config->load($file, $use_sections, $fail_gracefully);
    }

    /**
     * Ajoute des variables disponibles dans toutes les vues suivantes.
     */
    public function vars($vars = [], $val = '')
    {
        if (is_array($vars)) {
            $this->cachedVars = array_merge($this->cachedVars, $vars);
        } elseif (is_string($vars)) {
            $this->cachedVars[$vars] = $val;
        }
        return $this;
    }

    /**
     * Rend une vue. Affiche le resultat (comportement CI3) sauf si $return est vrai.
     */
    public function view($view, $vars = [], $return = false)
    {
        $this->cachedVars = array_merge($this->cachedVars, (array) $vars);

        $renderer = \Config\Services::renderer(null, null, false);
        $output   = $renderer->setData($this->cachedVars)->render((string) $view, ['saveData' => false]);

        if ($return) {
            return $output;
        }
        echo $output;
        return $this;
    }
}
