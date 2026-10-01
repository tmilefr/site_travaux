<?php

namespace App\Libraries\Compat;

/**
 * API CI3 (print_debugger, reply_to ...) sur le service Email de CI4.
 */
class Email
{
    protected $email;

    /** Cles de configuration CI3 => proprietes de Config\Email (CI4) */
    protected $map = [
        'protocol'     => 'protocol',
        'useragent'    => 'userAgent',
        'mailpath'     => 'mailPath',
        'smtp_host'    => 'SMTPHost',
        'smtp_user'    => 'SMTPUser',
        'smtp_pass'    => 'SMTPPass',
        'smtp_port'    => 'SMTPPort',
        'smtp_timeout' => 'SMTPTimeout',
        'smtp_keepalive' => 'SMTPKeepAlive',
        'smtp_crypto'  => 'SMTPCrypto',
        'wordwrap'     => 'wordWrap',
        'wrapchars'    => 'wrapChars',
        'mailtype'     => 'mailType',
        'charset'      => 'charset',
        'validate'     => 'validate',
        'priority'     => 'priority',
        'crlf'         => 'CRLF',
        'newline'      => 'newline',
        'bcc_batch_mode' => 'BCCBatchMode',
        'bcc_batch_size' => 'BCCBatchSize',
        'dsn'          => 'DSN',
    ];

    public function __construct($config = [])
    {
        $this->email = \Config\Services::email(null, false);
        if (!empty($config)) {
            $this->initialize($config);
        }
    }

    public function initialize($config = [])
    {
        $translated = [];
        foreach ((array) $config as $key => $value) {
            if ($value === null || $value === '') {
                continue; // garde la valeur par defaut de Config\Email
            }
            $lk = strtolower($key);
            $translated[$this->map[$lk] ?? $key] = $value;
        }
        if (isset($translated['SMTPPort'])) {
            $translated['SMTPPort'] = (int) $translated['SMTPPort'];
        }
        if (isset($translated['wordWrap'])) {
            $translated['wordWrap'] = (bool) $translated['wordWrap'];
        }
        $this->email->initialize($translated);
        return $this;
    }

    /** Methodes CI3 dont le nom change en CI4 */
    protected $methods = [
        'from'            => 'setFrom',
        'to'              => 'setTo',
        'cc'              => 'setCC',
        'bcc'             => 'setBCC',
        'reply_to'        => 'setReplyTo',
        'subject'         => 'setSubject',
        'message'         => 'setMessage',
        'set_alt_message' => 'setAltMessage',
        'set_header'      => 'setHeader',
        'set_mailtype'    => 'setMailType',
        'set_priority'    => 'setPriority',
        'set_newline'     => 'setNewline',
        'set_crlf'        => 'setCRLF',
        'print_debugger'  => 'printDebugger',
    ];

    public function __call($name, $args)
    {
        $camel = $this->methods[$name] ?? lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        $result = $this->email->{$camel}(...$args);
        return $result === $this->email ? $this : $result;
    }
}
