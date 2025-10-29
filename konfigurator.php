<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class Konfigurator extends Module
{
    public function __construct()
    {
        $this->name = 'konfigurator';
        $this->tab = 'other';
        $this->version = '1.0.0';
        $this->author = 'Jelly';
        $this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.7.7.1', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('konfigurator');
        $this->description = $this->l('Wyświetla konfigurator');

    }

    public function install()
    {
        return parent::install() && $this->registerHook('moduleRoutes') && $this->registerHook('header') && $this->registerHook('displayAdminProductsMainStepLeftColumnMiddle') && $this->registerHook('actionProductFormBuilderModifier')&& $this->registerHook('actionImportFields')
        && $this->registerHook('actionProductImport')
        && $this->registerHook('actionProductUpdate')
        && Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'product ADD konfigurator_active TINYINT(1) NOT NULL DEFAULT 0,  ADD `konfigurator_image1` VARCHAR(255) NULL,  ADD `konfigurator_image2` VARCHAR(255) NULL,  ADD `konfigurator_image3` VARCHAR(255) NULL, ADD `konfigurator_schema` VARCHAR(255) NULL, ADD `konfigurator_text` TEXT NULL;'
        ) 
		&& Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'product_shop ADD konfigurator_active TINYINT(1) NOT NULL DEFAULT 0,  ADD `konfigurator_image1` VARCHAR(255) NULL,  ADD `konfigurator_image2` VARCHAR(255) NULL,  ADD `konfigurator_image3` VARCHAR(255) NULL, ADD `konfigurator_schema` VARCHAR(255) NULL, ADD `konfigurator_text` TEXT NULL;'
        )&& Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'konfigurator_sets` (
				`konfigurationid` VARCHAR(255)  NOT NULL,
				`data` TEXT NOT NULL,
				PRIMARY KEY (konfigurationid)
			) DEFAULT CHARSET=utf8;'
        );
		
    }
	

    public function uninstall()
    {
        return parent::uninstall() && Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'product DROP COLUMN konfigurator_active, DROP COLUMN konfigurator_image1, DROP COLUMN konfigurator_image2, DROP COLUMN konfigurator_image3, DROP COLUMN konfigurator_schema, DROP COLUMN konfigurator_text;'
        ) && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'konfigurator_sets`')
		&& Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'product_shop DROP COLUMN konfigurator_active, DROP COLUMN konfigurator_image1, DROP COLUMN konfigurator_image2, DROP COLUMN konfigurator_image3, DROP COLUMN konfigurator_schema, DROP COLUMN konfigurator_text;');
    }
	public function hookHeader()
{
	    if (Tools::getValue('module') == 'konfigurator') {

			$this->context->controller->addCSS($this->_path . 'views/css/konfigurator.css');
			$this->context->controller->addJS($this->_path . 'views/js/konfigurator.js');
			$this->context->controller->addJS($this->_path . 'views/js/qrcode.min.js');
		}
}
public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params)
    {
        $id_product = (int)$params['id_product'];

		 $productData = Db::getInstance()->getRow('SELECT konfigurator_active, konfigurator_image1, konfigurator_image2, konfigurator_image3, konfigurator_schema, konfigurator_text FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . $id_product);
		
		$this->context->smarty->assign([
			'konfigurator_active' => $productData['konfigurator_active'],
			'input_name' => 'konfigurator_active',
			'konfigurator_image1' => $productData['konfigurator_image1'],
			'konfigurator_image2' => $productData['konfigurator_image2'],
			'konfigurator_image3' => $productData['konfigurator_image3'],
			'konfigurator_schema' => $productData['konfigurator_schema'],
			'image_url' => $productData['konfigurator_image1'] ? $productData['konfigurator_image1'] : '',
			'image_url2' => $productData['konfigurator_image2'] ? $productData['konfigurator_image2'] : '',
			'image_url3' => $productData['konfigurator_image3'] ? $productData['konfigurator_image3'] : '',
			'image_url4' => $productData['konfigurator_schema'] ? $productData['konfigurator_schema'] : '',
			'konfigurator_text' => $productData['konfigurator_text'] ? $productData['konfigurator_text'] : '',
			'id_product' => $id_product
		]);
        return $this->display(__FILE__, 'views/templates/admin/konfigurator_active.tpl');
    }


public function hookActionProductUpdate(array $params)
{
    $productId = (int)$params['id_product'];
    $isChecked = (bool)Tools::getValue('konfigurator_active');
	//var_dump($isChecked);die;
	$konfigurator_image1 = Tools::getValue('konfigurator_image1');
	$konfigurator_image2 = Tools::getValue('konfigurator_image2');
	$konfigurator_image3 = Tools::getValue('konfigurator_image3');
	$konfigurator_schema = Tools::getValue('konfigurator_schema');
	$konfigurator_text = Tools::getValue('konfigurator_text');
	
    Db::getInstance()->execute(
        'UPDATE ' . _DB_PREFIX_ . 'product SET konfigurator_active = ' . (int)$isChecked . ', konfigurator_image1 = "' . pSQL($konfigurator_image1) . '", konfigurator_image2 = "' . pSQL($konfigurator_image2) . '", konfigurator_image3 = "' . pSQL($konfigurator_image3) . '", konfigurator_schema = "' . pSQL($konfigurator_schema) . '", konfigurator_text = "' . pSQL($konfigurator_text, true) . '"  WHERE id_product = ' . $productId
    );
	
}
    public function hookModuleRoutes($params)
    {
        return [
            'module-konfigurator-display' => [
                'controller' => 'display',
                'rule' => 'konfigurator',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                    'controller' => 'display',
                ],
            ],
			'module-yourmodule-konfiguracja' => [
				'controller' => 'konfiguracja',
				'rule' => 'konfiguracja',
				'keywords' => [
					'hash' => ['regexp' => '[a-zA-Z0-9]+', 'param' => 'hash']
				],
				'params' => [
					'fc' => 'module',
					'module' => $this->name
				]
			],
        ];
    }
	public function generateHash()
	{
		if (Tools::getValue('ajax') && Tools::getValue('action') == 'generateHash') {
			// Generate a random hash
			$hash = Tools::strtoupper(Tools::passwdGen(32)); // 32-character hash
			$cartData = Tools::getValue('cartData');
			// Save to configuration
			//Configuration::updateValue('MY_MODULE_HASH', $hash);
			
			// Return the hash as JSON
			die(json_encode([
				'success' => true,
				'hash' => $hash,
				'cartData' => $cartData
			]));
		}
	}
	public function hookActionImportFields(&$params)
	{
		if ($params['entity'] !== 'Products') {
			return;
		}

		
	}
	public function hookActionProductImport($params)
	{
		
	}
	
}
