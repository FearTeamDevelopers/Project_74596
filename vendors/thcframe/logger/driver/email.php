<?php

namespace THCFrame\Logger\Driver;

use THCFrame\Logger;
use THCFrame\Registry\Registry;

/**
 * Email logger class
 */
class Email extends Logger\Driver
{

    public function __construct($options = null)
    {
        $options = array(
            'path' => 'application'.DIRECTORY_SEPARATOR.'logs',
        );

        parent::__construct($options);

        $this->path = APP_PATH . DIRECTORY_SEPARATOR . trim($this->path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }
    
    /**
     * 
     * @param type $message
     */
    public function log($message)
    {
        require_once APP_PATH . '/vendors/swiftmailer/swift_required.php';
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);

        $config = Registry::get('configuration');
        $sendTo = $config->system->adminemail;
        $appName = $config->system->appname;
        
        $email = \Swift_Message::newInstance(null)
                ->setSubject($appName . ' error' )
                ->setFrom('info@'.strtolower($appName).'.cz')
                ->setTo($sendTo)
                ->setBody($message);

        file_put_contents($this->path.date('Y-m-d').'_mail.log', $email->toString());
        
        $result = $mailer->send($email);
    }

    public function alert($message, array $context = array())
    {
        
    }

    public function critical($message, array $context = array())
    {
        
    }

    public function debug($message, array $context = array())
    {
        
    }

    public function emergency($message, array $context = array())
    {
        
    }

    public function error($message, array $context = array())
    {
        
    }

    public function info($message, array $context = array())
    {
        
    }

    public function notice($message, array $context = array())
    {
        
    }

    public function warning($message, array $context = array())
    {
        
    }

}
