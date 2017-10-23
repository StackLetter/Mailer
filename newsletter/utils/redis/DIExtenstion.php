<?php

namespace Newsletter\Utils\Redis;

use Nette\DI\CompilerExtension;
use Predis\Client;

class DIExtension extends CompilerExtension{

    public function loadConfiguration(){
        $config = $this->getConfig();
        $container = $this->getContainerBuilder();

        $container->addDefinition($this->prefix($client = 'client'))
            ->setType(Client::class)
            ->setFactory(Client::class, [$config['uri']]);

        $container->addDefinition($this->prefix('queue'))
            ->setType(RedisQueue::class)
            ->setFactory(RedisQueue::class, ['@' . $this->prefix($client), $config['job_queue']]);

    }
}
