<?php

namespace Directus\Mail;

use Directus\Collection\Collection;
use function Directus\get_api_project_from_request;

class Mailer
{
    /**
     * @var TransportManager
     */
    protected $transports;

    /**
     * @var \Swift_Mailer[]
     */
    protected $mailers = [];

    public function __construct(TransportManager $transportManager)
    {
        $this->transports = $transportManager;
    }

    /**
     * Creates a new message instance
     *
     * @return Message
     */
    public function createMessage()
    {
        return new Message();
    }

    public function sendWithTemplate($view, array $data, \Closure $callback = null, $params = [])
    {
        $content = \Directus\parse_twig($view, array_merge(
            $data,
            ['api' => ['project' => get_api_project_from_request()]]
        ));

        $this->sendWithContent($content, 'text/html', $callback, $params);
    }

    public function sendWithContent($content, $contentType = 'text/html', \Closure $callback = null, $params = [])
    {
        $transport = $this->transports->getDefault();
        $message = $this->createMessage();

        /*
	        Get global information.
	        Allow admins to override!
        */
        
        $config = new Collection($this->transports->getDefaultConfig());
        
        if (isset($params['from']) && is_array($params['from'])) {
	        $message->setFrom($params['from']);
        }
        elseif ($config->has('from')) {
            $message->setFrom($config->get('from'));
        }

        if (isset($params['bcc']) && is_array($params['bcc'])) {
	        $message->setFrom($params['bcc']);
        }
        elseif ($config->has('bcc')) {
            $message->setBcc($config->get('bcc'));
        }

        if (isset($params['cc']) && is_array($params['cc'])) {
	        $message->setFrom($params['cc']);
        }
        elseif ($config->has('cc')) {
            $message->setCc($config->get('cc'));
        }
        
        /*
	        Allow users to send attachments.
	        Full URL Only via Directus Files or External.
        */
        
        if (isset($params['attachment']) && is_array($params['attachment'])) {
	        foreach ($params['attachment'] as $attachment) {
		        if (filter_var($attachment, FILTER_VALIDATE_URL)) {
			        $message->attach(\Swift_Attachment::fromPath($attachment));
		        }
	        }
        }

        $message->setBody($content, $contentType);

        if ($callback) {
            call_user_func($callback, $message);
        }

        $transportId = get_class($transport);
        
        if (!array_key_exists($transportId, $this->mailers)) {
            $this->mailers[$transportId] = new \Swift_Mailer($transport->getSwiftTransport());
        }

        $swiftMailer = $this->mailers[$transportId];
        $swiftMailer->send($message);
    }
}
