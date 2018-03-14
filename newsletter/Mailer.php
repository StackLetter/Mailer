<?php

namespace Newsletter;

use Kdyby\Monolog\Logger as KdybyLogger;
use Nette\FileNotFoundException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SmtpException;
use Nette\SmartObject;
use Newsletter\Utils\Redis\RedisQueue;
use Psr\Log\LoggerInterface;

class Mailer{
    use SmartObject;

    protected $config;
    protected $templateDir;

    /** @var LoggerInterface */
    protected $logger;

    /** @var RedisQueue */
    protected $queue;

    /** @var IMailer */
    protected $mailer;

    private $jobProcessors;


    public function __construct(array $config, string $outputDir){
        $this->config = $config;
        $this->templateDir = $outputDir;
        $this->jobProcessors = [
            'stackletter.mail.newsletter' => [$this, 'sendNewsletter']
        ];
    }

    public function setLogger(LoggerInterface $logger){
        $this->logger = $logger instanceof KdybyLogger ? $logger->channel('mailer') : $logger;
    }

    public function setRedisQueue(RedisQueue $queue){
        $this->queue = $queue;
    }

    public function setMailer(IMailer $mailer){
        $this->mailer = $mailer;
    }


    public function sendNewsletter($params, $repeat = true){
        if(!isset($this->config['sender'])){
            throw new \RuntimeException("No sender email specified. Specify sender email in config/local.neon");
        }
        if(!isset($params['email'])){
            $this->logger->error("No email specified: #" . ($params['newsletter_id'] ?? 'no-ID'));
            return false;
        }
        $file = $params['file'];
        if(!file_exists($file)){
            $this->logger->error("Rendered newsletter not found in ($file) #" . ($params['newsletter_id'] ?? 'no-ID'));
            return false;
        }
        $html = file_get_contents($file);

        $mail = new Message();
        $mail->setFrom($this->config['sender'])
            ->addTo($params['email']);

        try{
            $mail->setHtmlBody($html, $this->templateDir);
        } catch(FileNotFoundException $e){
            $mail->setHtmlBody($html);
        }

        if(isset($this->config['xmailer'])){
            $mail->setHeader('X-Mailer', $this->config['xmailer']);
        }
        if(isset($params['newsletter_id'])){
            $mail->setHeader('X-StackLetter-ID', $params['newsletter_id']);
        }
        if(isset($params['unsubscribe'])){
            $mail->setHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            $mail->setHeader('List-Unsubscribe', "<$params[unsubscribe]>");
        }

        try{
            $this->mailer->send($mail);
        } catch(SmtpException $e){
            // If failed, try again in a few seconds
            if($repeat){
                $this->logger->error('Error sending mail, trying again in a few seconds...');
                sleep(10);
                return $this->sendNewsletter($params, false);
            } else{
                $this->logger->error('Could not send mail; newsletter_id=' . $params['newsletter_id'] ?? 'unknown');
                return false;
            }
        }

        // Remove rendered file
        //@unlink($file);

        // Remove empty dir
        //if(!(new \FilesystemIterator(dirname($file)))->valid()){
        //    @rmdir(dirname($file));
        //}
        return true;
    }


    public function run(){
        while(true){
            $job = $this->queue->waitFor();
            if(!isset($job['job'])){
                continue;
            }
            $this->processJob($job['job'], $job['params'] ?? []);
        }
    }


    public function processJob($job, $params){
        if(isset($this->jobProcessors[$job])){
            $this->logger->debug("Processing job $job... ", [
                'newsletter_id' => $params['newsletter_id'] ?? 'no-ID',
                'email' => $params['email'] ?? '<unknown>',
            ]);
            $res = call_user_func($this->jobProcessors[$job], $params);
            $this->logger->debug("Done (" . ($res ? 'OK' : 'FAIL') . ")");
            return $res;
        } else{
            return false;
        }
    }
}
