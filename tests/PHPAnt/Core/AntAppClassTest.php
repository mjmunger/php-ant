<?php
use PHPUnit\Framework\TestCase;

class AntAppClassTest extends TestCase
{

	function getMyAE($options) {
		
		$vars = getMockVars(true);
		$pdo = gimmiePDO();
		$W = new PHPAnt\Core\ConfigWeb($pdo, $vars);
		
		//Setup the Server object
		$Server = new \PHPAnt\Core\ServerEnvironment();
		$HTTP   = new \PHPAnt\Core\HTTPEnvironment();
		$SSL    = new \PHPAnt\Core\SSLEnvironment();
		$WR     = new \PHPAnt\Core\WebRequest();
		$Ex     = new \PHPAnt\Core\ScriptExecution();

		$Server->HTTP      = $HTTP;
		$Server->SSL       = $SSL;
		$Server->Request   = $WR;
		$Server->Execution = $Ex;

		//Setup the request
		$Server->Request->uri = '/uploader/';

		//Add to the ConfigWeb instance.
		$W->Server = $Server;

		//Get an AppEngine with those configs.
		return new PHPAnt\Core\AppEngine($W,$options);		
	}

	function testInit() {
		$appInitPath = __DIR__ . '/resources/app.json';
		$this->assertFileExists($appInitPath);


		$app = new \PHPAnt\Core\AntApp();

		$this->assertCount(0, $app->getFilters);
		$this->assertCount(0, $app->postFilters);

		$appOptions = json_decode(file_get_contents($appInitPath));
		$this->assertInstanceOf('stdClass', $appOptions);
		$app->init($appOptions);

		$this->assertCount(1, $app->getFilters);
		$this->assertCount(1, $app->postFilters);
	}

	/**
	 * @depends testInit
	 **/
	
	function testFilterOnRequest() {
		//Setup the app.
		$appInitPath = __DIR__ . '/resources/app.json';
		$appOptions = json_decode(file_get_contents($appInitPath));
		$app = new \PHPAnt\Core\AntApp();

		//Instantiate an AppEngine instance with WebConfigs and this app enabled.
		$vars    = getMockVars();
		$configs = getWebConfigs();
		$options = getDefaultOptions();
		
		//Add the disable all apps option.
		$options['disableApps'] = true;

		$AE = $this->getMyAE($options);

		//With no POST or GET vars set in the app, the app should run:
		$this->assertTrue($app->FilterOnRequest($AE));

		//With POST vars set, the app should run
		$AE->Configs->Server->Request->post_vars['processorType'] = 'testa';
		$AE->Configs->Server->Request->mergeRequest();
		$this->assertTrue($app->FilterOnRequest($AE));

		//With GET vars set, the app should run.
		$AE->Configs->Server->Request->get_vars['processorType'] = 'testa';
		$AE->Configs->Server->Request->mergeRequest();
		$this->assertTrue($app->FilterOnRequest($AE));

		//Load the app options so the request filters are loaded:
		$app->init($appOptions);

		//Reset the get / set vars.
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		//Nothing is set. Fail.
		$this->assertFalse($app->FilterOnRequest($AE));
		
		//Reset the get / set vars.
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		//The required post vars are not present, but get vars are. This should return true.
		$AE->Configs->Server->Request->get_vars['processorType'] = 'gepaid';
		$AE->Configs->Server->Request->mergeRequest();

		$this->assertCount(0, $AE->Configs->Server->Request->post_vars);
		$this->assertArrayHasKey('processorType', $AE->Configs->Server->Request->get_vars);
		$this->assertArrayHasKey('processorType', $app->getFilters);

		$this->assertSame($app->getFilters['processorType'], 'gepaid');
		$this->assertSame($AE->Configs->Server->Request->get_vars['processorType'], 'gepaid');

		$this->assertTrue($app->FilterOnRequest($AE));

		//reset
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		//The required get vars are not present, but post vars aren't.. This should return true.
		$AE->Configs->Server->Request->post_vars['processorType'] = 'gepaid';
		$AE->Configs->Server->Request->mergeRequest();
		$this->assertTrue($app->FilterOnRequest($AE));

		//reset
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();
		$this->assertFalse($app->FilterOnRequest($AE));

		//Post vars set, but wrong values (right keys)
		$AE->Configs->Server->Request->post_vars['processorType'] = 'gepaidx';
		$AE->Configs->Server->Request->mergeRequest();
		$this->assertFalse($app->FilterOnRequest($AE));

		//reset
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		$this->assertFalse($app->FilterOnRequest($AE));

		//GET vars set, but wrong values (right keys)
		$AE->Configs->Server->Request->get_vars['processorType'] = 'gepaidx';
		$this->assertFalse($app->FilterOnRequest($AE));

		//reset
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		//Just the post var is correct.
		$AE->Configs->Server->Request->post_vars['processorType'] = 'gepaid';
		$AE->Configs->Server->Request->get_vars['processorType'] = 'gepaidx';
		$this->assertTrue($app->FilterOnRequest($AE));

		//reset
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		//Just the get var is correct.
		$AE->Configs->Server->Request->post_vars['processorType'] = 'gepaidx';
		$AE->Configs->Server->Request->get_vars['processorType'] = 'gepaid';
		$this->assertTrue($app->FilterOnRequest($AE));

		//reset
		$AE->Configs->Server->Request->get_vars = [];
		$AE->Configs->Server->Request->post_vars = [];
		$AE->Configs->Server->Request->mergeRequest();

		//Just both are correct.
		$AE->Configs->Server->Request->post_vars['processorType'] = 'gepaid';
		$AE->Configs->Server->Request->get_vars['processorType'] = 'gepaid';
		$this->assertTrue($app->FilterOnRequest($AE));
	}

	/**
	 * @covers AntApp::fireOnURI
	 * @dataProvider providerFireOnURI
	 */
	
	public function testFireOnURI($regex,$uri,$expected)
	{
		$app = new \PHPAnt\Core\AntApp();

		if($regex) {
			$this->assertTrue($app->registerURI([$regex]));
		} else {
			$app->uriRegistry = [];
		}

		if(!$regex) $this->assertCount(0, $app->uriRegistry);
		if($regex)  $this->assertCount(1, $app->uriRegistry);
		$this->assertSame($expected, $app->fireOnURI($uri));
	}

	function providerFireOnURI() {

		$data = [['#^\/uploader\/.*#' , '/'           , false]
				,['#^\/uploader\/.*#' , '/uploader/'  , true ]
				,['#^\/uploader\/.*#' , '/uploader/1' , true ]
				,['#^\/uploader\/.*#' , '/uploader/2' , true ]
				,['#^\/uploader\/.*#' , '/uploader/3' , true ]
				,['#^\/uploader\/.*#' , '/uploader/4' , true ]
				,['#^\/uploader\/.*#' , '/upload/4'   , false]
				,['#^\/uploader\/.*#' , '/account'    , false]
				,[''                  , '/account'    , true ]
				];

		return $data;
	}
}