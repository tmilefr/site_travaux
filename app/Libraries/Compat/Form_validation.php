<?php

namespace App\Libraries\Compat;

/**
 * Validation de formulaire avec l'API de CI_Form_validation (CI3) :
 * set_rules(), set_data(), run($groupe), error(), validation_errors().
 *
 * Regles supportees : required, trim, numeric, integer, valid_email,
 * min_length[n], max_length[n], exact_length[n], matches[champ], alpha*,
 * is_natural(_no_zero), in_list[..], regex_match[..] et les fonctions PHP
 * simples (ex. strtolower).
 */
class Form_validation
{
    protected $groups = [];
    protected $fieldRules = [];
    protected $data = null;
    protected $errors = [];
    protected $errorPrefix = '<p>';
    protected $errorSuffix = '</p>';
    protected $messages = null;

    public function _SetRules($rules, $group)
    {
        $this->groups[$group] = $rules;
    }

    public function set_rules($field, $label = '', $rules = '')
    {
        if (is_array($field)) {
            foreach ($field as $row) {
                $this->set_rules($row['field'], $row['label'] ?? '', $row['rules'] ?? '');
            }
            return $this;
        }
        $this->fieldRules[] = ['field' => $field, 'label' => $label ?: $field, 'rules' => $rules];
        return $this;
    }

    public function set_data(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
    {
        $this->errorPrefix = $prefix;
        $this->errorSuffix = $suffix;
        return $this;
    }

    public function reset_validation()
    {
        $this->fieldRules = [];
        $this->data = null;
        $this->errors = [];
        return $this;
    }

    // ------------------------------------------------------------------

    /**
     * Execute la validation. Sans donnees (ni POST ni set_data) retourne FALSE
     * comme CI3 (aucune erreur n'est alors enregistree).
     */
    public function run($group = '')
    {
        $this->errors = [];

        $data = $this->data;
        if ($data === null) {
            $data = $_POST;
        }
        if (empty($data)) {
            return false;
        }

        $rules = $this->fieldRules;
        if ($group !== '' && isset($this->groups[$group])) {
            $rules = array_merge($rules, $this->groups[$group]);
        }
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $row) {
            $this->validateField($row, $data);
        }

        // trim : CI3 reecrit les valeurs nettoyees dans $_POST
        if ($this->data === null) {
            foreach ($rules as $row) {
                $name = $row['field'];
                if (isset($_POST[$name]) && is_string($_POST[$name]) && $this->hasRule($row['rules'], 'trim')) {
                    $_POST[$name] = trim($_POST[$name]);
                }
            }
        }

        return empty($this->errors);
    }

    protected function ruleList($rules): array
    {
        if (is_array($rules)) {
            return $rules;
        }
        return $rules === '' ? [] : explode('|', $rules);
    }

    protected function hasRule($rules, $name): bool
    {
        return in_array($name, $this->ruleList($rules), true);
    }

    protected function validateField(array $row, array $data): void
    {
        $name  = $row['field'];
        $label = $row['label'];
        $value = $data[$name] ?? null;
        $list  = $this->ruleList($row['rules']);

        if (is_string($value) && in_array('trim', $list, true)) {
            $value = trim($value);
        }

        $isEmpty = is_array($value) ? count($value) === 0 : ($value === null || trim((string) $value) === '');
        $required = in_array('required', $list, true);

        foreach ($list as $rule) {
            if ($rule === 'trim' || $rule === '') {
                continue;
            }
            $param = null;
            if (preg_match('/^(.*?)\[(.*)\]$/', $rule, $m)) {
                $rule  = $m[1];
                $param = $m[2];
            }

            if ($rule === 'required') {
                if ($isEmpty) {
                    $this->addError($name, $label, 'required');
                    return;
                }
                continue;
            }

            // Une regle autre que "required" ne s'applique pas a une valeur vide non requise
            if ($isEmpty && !$required) {
                continue;
            }

            if (!$this->check($rule, $value, $param, $data)) {
                $this->addError($name, $label, $rule, $param);
                return;
            }
        }
    }

    protected function check($rule, $value, $param, array $data): bool
    {
        $str = is_array($value) ? '' : (string) $value;
        switch ($rule) {
            case 'numeric':
                return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
            case 'is_numeric':
                return is_numeric($str);
            case 'integer':
                return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
            case 'decimal':
                return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
            case 'is_natural':
                return ctype_digit($str);
            case 'is_natural_no_zero':
                return ctype_digit($str) && $str != 0;
            case 'valid_email':
                return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
            case 'valid_url':
                return (bool) filter_var($str, FILTER_VALIDATE_URL);
            case 'valid_ip':
                return (bool) filter_var($str, FILTER_VALIDATE_IP);
            case 'min_length':
                return mb_strlen($str) >= (int) $param;
            case 'max_length':
                return mb_strlen($str) <= (int) $param;
            case 'exact_length':
                return mb_strlen($str) === (int) $param;
            case 'alpha':
                return ctype_alpha($str);
            case 'alpha_numeric':
                return ctype_alnum($str);
            case 'alpha_dash':
                return (bool) preg_match('/^[a-z0-9_-]+$/i', $str);
            case 'matches':
                return $str === (string) ($data[$param] ?? '');
            case 'differs':
                return $str !== (string) ($data[$param] ?? '');
            case 'greater_than':
                return is_numeric($str) && $str > $param;
            case 'less_than':
                return is_numeric($str) && $str < $param;
            case 'in_list':
                return in_array($str, explode(',', (string) $param), true);
            case 'regex_match':
                return (bool) preg_match($param, $str);
        }

        // Fonction PHP native a un argument (ex. "strtolower", "intval")
        if (function_exists($rule)) {
            return true;
        }
        throw new \RuntimeException('Unknown validation rule: ' . $rule);
    }

    // ------------------------------------------------------------------

    protected function loadMessages(): array
    {
        if ($this->messages === null) {
            $lang = [];
            $file = APPPATH . 'Language/french/form_validation_lang.php';
            if (is_file($file)) {
                include $file;
            }
            $this->messages = $lang;
        }
        return $this->messages;
    }

    protected function addError($field, $label, $rule, $param = null): void
    {
        $messages = $this->loadMessages();
        $msg = $messages['form_validation_' . $rule] ?? $messages['form_validation_error_message_not_set'] ?? 'Invalid {field}';
        $this->errors[$field] = str_replace(['{field}', '{param}'], [$label, (string) $param], $msg);
    }

    public function error($field = '', $prefix = '', $suffix = '')
    {
        if (!isset($this->errors[$field])) {
            return '';
        }
        $prefix = $prefix === '' ? $this->errorPrefix : $prefix;
        $suffix = $suffix === '' ? $this->errorSuffix : $suffix;
        return $prefix . $this->errors[$field] . $suffix;
    }

    public function error_array()
    {
        return $this->errors;
    }

    public function error_string($prefix = '', $suffix = '')
    {
        $prefix = $prefix === '' ? $this->errorPrefix : $prefix;
        $suffix = $suffix === '' ? $this->errorSuffix : $suffix;
        $out = '';
        foreach ($this->errors as $err) {
            $out .= $prefix . $err . $suffix . "\n";
        }
        return $out;
    }
}
