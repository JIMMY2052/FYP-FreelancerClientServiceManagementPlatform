<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '68069d1af60d8c395881265bce5aa31b71d0f969',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '68069d1af60d8c395881265bce5aa31b71d0f969',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'stripe/stripe-php' => array(
            'pretty_version' => 'v19.0.0',
            'version' => '19.0.0.0',
            'reference' => '8f868a7825d2680e917f89839e4b67851dad96e2',
        ),
        'tecnickcom/tcpdf' => array(
            'pretty_version' => '6.10.1',
            'version' => '6.10.1.0',
            'reference' => '7a2701251e5d52fc3d508fd71704683eb54f5939',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
