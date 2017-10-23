<?php

namespace Newsletter\Utils\Api;

use Nette\DI\CompilerExtension;

class DIExtension extends CompilerExtension{

    public function loadConfiguration(){
        $config = $this->getConfig();
        $container = $this->getContainerBuilder();

        $container->addDefinition($this->prefix($client = 'helper'))
                  ->setType(ApiHelper::class)
                  ->setFactory(ApiHelper::class, [$config]);
    }
}
