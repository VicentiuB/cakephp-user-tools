<?php
namespace Burzum\UserTools\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UsersController extends Controller {

}

/**
 * UserToolComponent
 *
 * @author Florian Krämer
 * ]@copyright 2013 - 2017 Florian Krämer
 * @license MIT
 */
class UserToolComponentTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum\UserTools.User'
	];

	public $request;
	public $response;
	public $Controller;
	public $Users;
	public $Registry;

	/**
	 * setup
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->request = new ServerRequest();
		$this->response = new Response();
		$this->Users = TableRegistry::get('Users');
		$this->Controller = new UsersController($this->request, $this->response);
		$this->Registry = new ComponentRegistry($this->Controller);
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * testListing
	 *
	 * @return void
	 */
	public function testListing() {
		$this->Controller->loadComponent('Burzum/UserTools.UserTool');
		$this->Controller->UserTool->listing();
		$this->assertNotEmpty($this->Controller->viewVars['users']);
		$this->assertNotEmpty($this->Controller->viewVars['_serialize']);
	}

	/**
	 * testSetUserTable
	 *
	 * @return void
	 */
	public function testSetUserTable() {
		$this->Controller->loadComponent('Burzum/UserTools.UserTool');
		$this->Controller->UserTool->setUserTable();
		$this->assertEquals($this->Controller->viewVars['userTable'], 'Users');

		$this->Controller->UserTool->setUserTable();
		$this->assertEquals($this->Controller->viewVars['userTable'], 'Users');
	}

	/**
	 * testSetUserTable
	 *
	 * @return void
	 */
	public function testGetUser() {
		$this->Controller->request = $this->Controller->request->withParam('pass', [0 => 1]);
		$this->Controller->loadComponent('Burzum/UserTools.UserTool');
		$user = $this->Controller->UserTool->getUser(1);
		$this->assertEquals($user->id, 1);
		$this->assertEquals($user->username, 'adminuser');

		$this->Controller->request = $this->Controller->request->withParam('pass', [0 => 2]);
		$user = $this->Controller->UserTool->getUser();
		$this->assertEquals($user->id, 2);
		$this->assertEquals($user->username, 'newuser');
	}
}
