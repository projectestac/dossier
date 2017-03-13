<?php

function test_mail($app, $mail = false, $env='INT') {

    if (!empty($_REQUEST['mail'])) {
        $mail = $_REQUEST['mail'];
    } else if (!empty($_REQUEST['user'])) {
        $mail = $_REQUEST['user'].'@xtec.cat';
    } else if (!$mail) {
        show_warning('User not set, using agora');
        $mail = 'agora@xtec.cat';
    }

    show_header('Test MAIL '.$mail);

    try {
        $testmailer = testapp_mail::instance($app, $env);

        if (!$testmailer->send_mail('text/html', $mail)) {
            return false;
        }
        if (!$testmailer->send_mail('text/plain', $mail)) {
            return false;
        }

        show_success('Enviat!');
    } catch (Exception $e) {
        show_error('ERROR: Something terrible happened during the mailing and might be repaired');
        show_error($e->getMessage());
        return false;
    }
}

final class testapp_mail {

    private $sender;
    private $log;
    private $logfile;
    private $app;

    public static function instance($app, $env='INT') {
        static $inst = null;
        if ($inst === null) {
            $inst = new testapp_mail($app, $env);
        }
        return $inst;
    }

    private function __construct($app, $env) {
        $this->app = $app;
        $this->env = $env;

        $dir = sp_get_mailsender_dir();
        if (!$dir) {
            return;
        }
        require_once($dir.'mailsender.class.php');
        require_once($dir.'message.class.php');

        if (!empty($_REQUEST['logmail'])) {
            $this->logfile = __DIR__.'/log/'.$this->app.'.log';
            show_success('Logfile '.$this->logfile);
            $this->log = 1;
        } else {
            $this->log = 0;
            $this->logfile = "";
        }
        // Load the mailsender
        try {
            $this->sender = new mailsender($this->app, 'noreply@agora.xtec.cat', 'educacio', $this->env, $this->log, $this->log, $this->logfile);
        } catch (Exception $e) {
            show_error('ERROR: Cannot initialize mailsender, no mail will be send. <br> '.$e->getMessage());
            $this->sender = false;
        }
    }

    public function send_mail($format, $mail) {
        if (!$this->sender) {
            return;
        }

        if ($format === 'text/plain') {
            $newline = "\n";
        } else {
            $newline = '<br/>';
        }
        // Load the message
        $message = new message($format, $this->log, $this->log, $this->logfile);
        $message->set_to(array($mail));
        $message->set_subject('['.$this->app.'] Prova des de superworks format '.$format);
        $message->set_bodyContent("S'està enviant un correu des de FMO servei $this->app entorn $this->env $newline L'hora actual és ".time()."$newline Caràcters especials: àíòúèçÇñÑ");
        // Add message to mailsender
        if (!$this->sender->add($message)) {
            show_error('ERROR: Impossible to add message to mailsender');
            return false;
        }
        // Send messages
        if (!$this->sender->send_mail()) {
            show_error('ERROR: Impossible to send messages');
            return false;
        }
        return true;
    }

}
